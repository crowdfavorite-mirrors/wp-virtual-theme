<?php
/*
Copyright 2011  Mobile Sentience LLC  (email : oss@mobilesentience.com)
Copyright 2008  Stephen Carroll  (email : scarroll@virtuosoft.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
                                                                 
Plugin Name: Virtual Theme
Plugin URI: http://wordpress.org/extend/plugins/virtual-theme/
Description: Virtual Theme allows you to switch themes based on virtual paths, making an entire site accessible through multiple themes based on their URL prefix.  If you like Virtual Path consider supporting it. | <a target="_blank" href="http://twitter.com/share?url=http://www.mobilesentience.com/software/oss/virtual-theme/&text=Checkout%20Virtual%20Theme%20a%20virtual%20path%20%23theme%20%23switcher%20for%20%40Wordpress.%20%23wordpress%20%23wp%20%23plugin&via=MobileSentience">Tweet about Virtual Theme.</a> | <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F6PVX3CMH8MK4">Donate</a> | <a target="_blank" href="http://www.mobilesentience.com/software/oss/virtual-theme/">Virtual Theme Support</a> | <a target="_blank" href="http://twitter.com/#!/MobileSentience">Follow Mobile Sentience</a>
Version: 1.0.14
License: GPL2
Author: Max Jonathan Spaulding - Mobile Sentience LLC
Author URI: http://www.mobilesentience.com
Stable Tag: 1.0.14
*/

$plugin_name = "virtualtheme";
$plugin_version = "1.0.14";
$adserv = "http://www.mobilesentience.com/ads/WordpressPlugins&plugin=" . $plugin_name . "&pluginversion=" . $plugin_version;
$deactivating = false;
$rewriteHooked = false;

function_exists('register_activation_hook')?register_activation_hook(__FILE__, 'ActivatePlugin'):add_action('activate_'.__FILE__, 'ActivatePlugin');
function_exists('register_deactivation_hook')?register_deactivation_hook(__FILE__, 'DeactivatePlugin'):add_action('deactivate_'.__FILE__, 'DeactivatePlugin');


$VirtualTheme = new VirtualTheme($_SERVER["REQUEST_URI"]);

function virtual_theme_get_path(){
	return $_GET["VirtualThemeActiveUrl"];
}

function virtual_theme_get_variable($variable){
	return $_GET["VirtualThemeVariables"][$variable];
}

function virtual_theme_get_variables(){
	return $_GET["VirtualThemeVariables"];
}

function VirtualThemeActive(){
	$options = maybe_unserialize(get_option("VirtualTheme_options"));
	return $options['active'];
}

function ActivatePlugin(){
	// Add the virtual path rules back to .htaccess
	$options = maybe_unserialize(get_option("VirtualTheme_options"));
	$options['active'] = true;
	update_option("VirtualTheme_options", $options);
	FlushRewrites();
}

function DeactivatePlugin() {
	global $deactivating;
	$options = maybe_unserialize(get_option("VirtualTheme_options"));
	$options['active'] = false;
	update_option("VirtualTheme_options", $options);
	$deactivating = true;
	FlushRewrites();
}

function FlushRewrites(){
	global $wp_rewrite, $rewriteHooked;
	if(!isset($rewriteHooked) || !$rewriteHooked){
		add_action('generate_rewrite_rules', 'RewriteExternalPaths');
		$rewriteHooked = true;
	}
	$wp_rewrite->flush_rules();
}

function RewriteExternalPaths() {
	global $wp_rewrite, $deactivating;
	//RewriteRule ^(.*/)?(vpath)(/.*)?$ /$1$3?VirtualThemePath=$2
	if(!$deactivating){
		$paths = VirtualTheme::GetVirtualPaths();
		foreach($paths as $path){
			if(strpos($path, '/') === 0)
				$path = substr($path, 1);
			$newRules['(.*/)?(' . $path . ')(/.*)?$'] = '$1$3?VirtualThemePath=$2';
		}
		//$wp_rewrite->non_wp_rules = array_merge($newRules, $wp_rewrite->non_wp_rules);
		if(isset($newRules) && is_array($newRules))
			$wp_rewrite->non_wp_rules = $newRules + $wp_rewrite->non_wp_rules;
	}
}

function compare($a, $b){
	if (strlen($a['url']) == strlen($b['url'])) {
		return 0;
	}
	return (strlen($a['url']) > strlen($b['url'])) ? -1 : 1;
}

// Create the function use in the action hook
function virtual_theme_add_dashboard_widgets() {
	// Add Adsense widget
	wp_add_dashboard_widget("VirtualThemeAdWidget", "Virtual Theme", "displayWidget");
} 


function displayWidget(){
	global $plugin_name, $plugin_version, $adserv;
	echo '<iframe src="' . $adserv . '" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:735px; height:97px;" allowTransparency="true"></iframe>';
}

function is_associative($input) { 
	$result = false; 
     
	foreach($input as $key => $value) { 
		if(is_string($key)) { 
			$result = true; 
			break; 
		} 
	} 

	return $result; 
}  

class VirtualTheme {   
	var $blogdescription;
	var $currenturl;
	var $stylesheet;
	var $template;
	var $blogname;
	var $siteurl;
	var $options;
	var $home;
	var $uri;
            
	// Based on the current domain name, load the associated theme data
	function VirtualTheme($requestURI){
		// Get default settings
		$this->blogdescription = get_option("blogdescription");
		$this->stylesheet = get_option("stylesheet");
		$this->template = get_option("template");
		$this->blogname = get_option("blogname");
		$this->siteurl = get_option("siteurl"); // WordPress url
		$this->home = get_option("home"); // Blog address url
		$this->currenturl = $requestURI;
        
		// Load domain option settings
		global $wp_version;
		$this->options = maybe_unserialize(get_option("VirtualTheme_options"));

        if(!is_array($this->options)){
        	$this->options = array();
		}
		
		if(!isset($this->options['virtual-paths']) || !is_array($this->options['virtual-paths'])){
        	$this->options['virtual-paths'] = array();
		}

		if(!isset($this->options['custom-variables']) || !is_array($this->options['custom-variables'])){
        	$this->options['custom-variables'] = array();
		}

		// Locate the matching index for the current domain
		if(isset($_GET['VirtualThemePath'])){
			$vpath = $_GET['VirtualThemePath'];
			$vt = $this->options['virtual-paths'][$vpath];

			// Update the settings for the matching domain
			$this->blogdescription = $vt['blogdescription'];
			$this->blogname = $vt['blogname'];
			$this->stylesheet = $vt['theme'];
			$this->template = $vt['theme'];

			$this->siteurl = $this->siteurl . '/' . $vpath;
			$this->home = $this->siteurl;

			//Add the variables
			$_GET["VirtualThemeVariables"] = $vt["variables"];
			$_GET["VirtualThemeActiveUrl"] = $vt["url"];
		}   
        
		// Apply filters and actions
		add_filter('pre_option_blogdescription', array(&$this, 'getBlogdescription'));
		add_filter('pre_option_stylesheet', array(&$this, 'getStylesheet'));
		add_filter('pre_option_template', array(&$this, 'getTemplate'));
		add_filter('pre_option_blogname', array(&$this, 'getBlogname'));
		add_filter('pre_option_siteurl', array(&$this, 'getSiteurl'));
		add_filter('pre_option_home', array(&$this, 'getHome'));
		add_action('admin_menu', array(&$this, 'displayAdminMenu'));
		add_action('admin_init', array(&$this, 'AdminInit'));

		// Specify uri for admin panels
		$this->uri = '?page=' . $this->getRightMost(__FILE__, 'plugins/');

		// Hook into the 'wp_dashboard_setup' action to register our other functions
		add_action('wp_dashboard_setup', 'virtual_theme_add_dashboard_widgets' );
	}

	public function AdminInit(){
		/** Remove this once everyone is using 1.0.11 */
		if(!isset($this->options['migrated-to-associative-options']) || is_array($this->options['migrated-to-associative-options'])){
			if(!is_associative($this->options)){
				$vpaths = array();
				foreach($this->options as $index => $path){
					$vpaths[$path['url']] = $path;
					unset($this->options[$index]);
				}
				$this->options = array();
				$this->options['virtual-paths'] = $vpaths;

				$variables = maybe_unserialize(get_option("VirtualTheme_custom_variables"));
			
				if(is_array($variables)){
					$this->options['custom-variables'] = $variables;
				}else{
					$this->options['custom-variables'] = array();
				}
				$this->options['migrated-to-associative-options'] = true;

				delete_option("VirtualTheme_custom_variables");
				update_option("VirtualTheme_options", $this->options);
			}
		}

		/** Remove this once everyone is using 1.0.12 */
		if(!isset($this->options['normalized-vpaths'])){
			foreach($this->options['virtual-paths'] as $key => $value){
				$path = trim($key, "/ \t\n\r\0\x0B");
				if($path != $key){
					$value['url'] = $path;
					$this->options['virtual-paths'][$path] = $value;
					unset($this->options['virtual-paths'][$key]);
				}
			}
			
			$this->options['normalized-vpaths'] = true;
			update_option("VirtualTheme_options", $this->options);
			FlushRewrites();
		}
	}

	static public function GetVirtualPaths(){
		global $wp_version;
		$options = maybe_unserialize(get_option("VirtualTheme_options"));
        
		if(gettype($options)!="array"){
			$options = array();
		}

		return array_keys($options['virtual-paths']);
	}

	static public function GetCustomVariables(){
		global $wp_version;
		$variables = maybe_unserialize(get_option("VirtualTheme_custom_variables"));
        
		if(gettype($variables)!="array"){
			$variables = array();
		}

		return array_keys($variables);
	}

	static public function GetVirtualVariable($url, $variable){
		global $wp_version;
		$options = maybe_unserialize(get_option("VirtualTheme_options"));
        
		if(gettype($options)!="array"){
			$options = array();
		}
		foreach($options as $vt){
			if($vt['url'] == $url){
				if(isset($vt['variables'][$variable]))
					return $vt['variables'][$variable];
			}
		}

		return null;
	}

	function urlMatches($url, $prefix){
		return !(strpos($url, $prefix) === false);
	}

	function fixUrl(&$url, $prefix){
		$pos = strpos($url, $prefix);
		if($pos && strlen($url) == $pos + strlen($prefix)){
			$url .= "/";
		}
	}

	// Common string functions
	function getRightMost($sSrc, $sSrch){        
		for($i = strlen($sSrc); $i >= 0; $i = $i - 1){
			$f = strpos($sSrc, $sSrch, $i);
			if($f !== FALSE){
				return substr($sSrc,$f + strlen($sSrch), strlen($sSrc));
			}
		}
		return $sSrc;
	}

	function delLeftMost($sSource, $sSearch){
		for($i = 0; $i < strlen($sSource); $i = $i + 1){
			$f = strpos($sSource, $sSearch, $i);
			if($f !== FALSE){
				return substr($sSource,$f + strlen($sSearch), strlen($sSource));
			}
		}
		return $sSource;
	}

	function getLeftMost($sSource, $sSearch){
		for($i = 0; $i < strlen($sSource); $i = $i + 1){
			$f = strpos($sSource, $sSearch, $i);
			if($f !== FALSE){
				return substr($sSource,0, $f);
			}
		}
		return $sSource;
	}
	
	function getThemeTitleByTemplate($template){
		// Return descriptive name for a given template name
		$themes = get_themes();
		foreach($themes as $theme){
			if ($template==$theme["Template"]){
				break;
			}
		}
		return $theme["Title"];
	}

	function displayAdminMenu(){
		add_options_page('Virtual Theme Options', 'Virtual Theme', 8, __FILE__, array(&$this, 'createAdminPanel'));
	}

	// Return modified data based on the current url
	function getBlogdescription(){
		return $this->blogdescription;
	}

	function getStylesheet(){
		return $this->stylesheet;
	}

	function getTemplate(){
		return $this->template;
	}

	function getBlogname(){
		return $this->blogname;
	}

	function getSiteurl(){
		return $this->siteurl;
	}

	function getHome(){
		return $this->home;
	}
    
	// Create the administration panel
	function createAdminPanel(){
		// Check if we need to add a Virtual Path
		$needsRulesFlushed = false;
		if($_GET['action'] == "addVirtualPath"){
			$url['url'] = trim(strtolower($_POST['url']), "/ \t\n\r\0\x0B");
			$url['theme'] = $_POST['theme'];
			$url['blogname'] = stripslashes($_POST['blogname']);
			$url['blogdescription'] = stripslashes($_POST['blogdescription']);
			$url['variables'] = $this->options['custom-variables'];
			$this->options['virtual-paths'][$url['url']] = $url;

			update_option("VirtualTheme_options", $this->options);
			$needsRulesFlushed = true;
		}

		// Check if we need to copy a Virtual Path
		if ($_GET['action']=="copypath"){
			$sourcepath = $_POST['sourcepath'];
			$newpath = trim(strtolower($_POST['newpath']), "/ \t\n\r\0\x0B");
			if(isset($this->options['virtual-paths'][$sourcepath])){
				$this->options['virtual-paths'][$newpath] = $this->options['virtual-paths'][$sourcepath];
				$this->options['virtual-paths'][$newpath]['url'] = $newpath;

				update_option("VirtualTheme_options", $this->options);
				$needsRulesFlushed = true;
			}
		}
        
		// Check if we need to edit a Virtual Path
		if ($_GET['action']=="editVirtualPath"){
			$id = $_GET['id'];
			if($_POST['submit']){
				$this->options['virtual-paths'][$id]['url'] = trim(strtolower($_POST['url']), "/ \t\n\r\0\x0B");
				$this->options['virtual-paths'][$id]['theme'] = $_POST['theme'];
				$this->options['virtual-paths'][$id]['blogname'] = stripslashes($_POST['blogname']);
				$this->options['virtual-paths'][$id]['blogdescription'] = stripslashes($_POST['blogdescription']);
			}
			foreach($this->options['virtual-paths'][$id]['variables'] as $key => $val){
				$uname = 'update-'.$key;
				if($_POST[$uname]){
					$vname = 'value-'.$key;
					$this->options['virtual-paths'][$id]['variables'][$key] = $_POST[$vname];
					$_GET['action']="urlProps";
				}
			}
			update_option("VirtualTheme_options", $this->options);
			$needsRulesFlushed = true;
		}

		// Do we need handle the custom variable forms
		if($_GET['action'] == customVars){
			if($_POST['add']){ // Do we need to add a new variable
				// Add the variable to the defaults
				$name = $_POST['nname'];
				$this->options['custom-variables'][$name] = $_POST['ndefault'];

				// Add the new variable to each of the existing virtual paths with its default value
				foreach($this->options['virtual-paths']as $key => $value){
					$this->options['virtual-paths'][$key]['variables'][$name] = $_POST['ndefault'];
				}
				update_option("VirtualTheme_options", $this->options);
				$needsRulesFlushed = true;
			}
			foreach($this->options['custom-variables'] as $key => $val){
				$uname = 'update-'.$key;
				if($_POST[$uname]){
					$vname = 'value-'.$key;
					$this->options['custom-variables'][$key] = $_POST[$vname];
					update_option("VirtualTheme_options", $this->options);
				}
			}

			// Check if we need to delete one or more Custom Variables
			if ($_POST['deleteit'] && $_POST['chkDelete']){
				foreach(array_reverse($_POST['chkDelete']) as $id){
					unset($this->options['custom-variables'][$id]);
					foreach($this->options['virtual-paths'] as $path => $theme){
						unset($theme['variables'][$id]);
					}
				}
				update_option("VirtualTheme_options", $this->options);
				$needsRulesFlushed = true;
			}
		}
        
		// Check if we need to delete one or more Virtual Paths
		if ($_GET['action']=="del" && $_POST['chkDelete']){
			foreach(array_reverse($_POST['chkDelete']) as $id){
				unset($this->options['virtual-paths'][$id]);
			}
			update_option("VirtualTheme_options", $this->options);
			$needsRulesFlushed = true;
		}
		if($needsRulesFlushed)
			FlushRewrites();
   
		// Display AdSense Leader Board
		//displayWidget();
		global $plugin_name, $plugin_version, $adserv;
		echo '<iframe src="' . $adserv . '&banner=large" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:735px; height:97px;" allowTransparency="true"></iframe>';

		// Check if we should display the edit panel
		if ($_GET['action']=="urlProps"){
			$id = $_GET['id'];
			echo '<div class="wrap">
                    <form name="editVirtualPath" id="editVirtualPath" action="' . $this->uri . '&action=editVirtualPath&id=' . $id . '" method="post">
                    <h2>' . __('Edit Virtual Theme') . '(<a href="' . $this->uri . '">Back</a>)</h2>
                    <br class="clear" />
                    <div class="tablenav">
                        <br class="clear" />
                    </div>
                    <br class="clear" />
                    <table class="form-table">
                        <tr class="form-field">
                            <th scope="row" valign="top"><label for="url">Virtual Path</label></th>
                            <td><input name="url" id="url" type="text" value="'. $id . '" size="40" /><br />
                            The Virtual Path that is used to access the site (i.e. www.example.com/VirtualPath).</td>
                        </tr>
                        <tr class="form-field">
                            <th scope="row" valign="top"><label for="theme">Theme</label></th>
                            <td>
                                <select name="theme" id="theme" class="postform" >';
			$themes = get_themes();
			foreach($themes as $theme){
				if($theme["Template"] == $this->options['virtual-paths'][$id]['theme']){
					echo '<option value="' . $theme["Template"] . '" selected>' . $theme["Name"] . '</option>';
				}else{
					echo '<option value="' . $theme["Template"] . '">' . $theme["Name"] . '</option>';
				}
			}
			echo '              </select>
                                <br />
                                Specify the theme to use when the site is accessed by the given Virtual Path.
                            </td>
                        </tr>
                        <tr class="form-field">
                            <th scope="row" valign="top"><label for="blogname">Blog Title</label></th>
                            <td><input name="blogname" id="blogname" type="text" value="' . htmlspecialchars($this->options['virtual-paths'][$id]['blogname']) . '" size="40" /><br />
                            The blog title that will be used when the site is accessed by the given Virtual Path.</td>
                        </tr>
                        <tr class="form-field">
                            <th scope="row" valign="top"><label for="blogname">Tagline</label></th>
                            <td><input name="blogdescription" id="blogdescription" type="text" value="' . htmlspecialchars($this->options['virtual-paths'][$id]['blogdescription']) . '" size="45" /><br />
                            In a few words, the blogs description when accessed by the given Virtual Path.</td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" class="button" name="submit" value="Edit Virtual Path" /></p>
                    </form>';
			// Create the  Custom variable list
			echo '<div class="wrap">
                <form name="editVirtualPath" id="editVirtualPath" action="' . $this->uri . '&action=editVirtualPath&id=' . $id . '" method="post">
                <h2>' . __('Custom Variables') . '</h2>
                <br class="clear" />
                <table class="widefat">
                <thead>
                    <tr>
                        <th scope="col">Variable</th>
                        <th scope="col">Default Value</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="the-list" class="list:url">';
			$i=0;
			foreach($this->options['virtual-paths'][$id]['variables'] as $key => $val){
				echo'   <tr id="var-'.$key.'" ';
				if (!fmod($i,2)){
					echo 'class="alternate">'; 
				}else{
					echo '>';
				}
				echo'       <th scope="row">' . $key . '</th>
                        <td><input name="value-' . $key . '" type="text" value="' . $val . '" size="40"/></td>
						<td><p class="submit"><input type="submit" class="button" name="update-' . $key . '" value="Update Default Value" /></p></td></form>
                    </tr>
                ';            
            	$i++;
			}

			echo '</tbody>
              </table>
			  </div>';
			return;
		} else if ($_GET['action']=="customVars"){

			// Inject the javascript for delete check all option
			echo '<script language="Javascript">
                (function($){
                    $(function(){
                        $("#chkAll").click(function(){
                            c=this.checked;
                            $(".chkDelete").each(function(i){
                                this.checked=c;
                            })
                        });
                    })
                })(jQuery);
              </script>';
        
			// Create the  Custom variable list
			echo '<div class="wrap">
                <form name="customVars" id="customVars" action="' . $this->uri . '&action=customVars' . '" method="post">
                <h2>' . __('Edit Custom Variables') . '(<a href="' . $this->uri . '">Back</a>)</h2>
                <br class="clear" />
                <div class="tablenav">
                    <div class="alignleft">
                        <input type="submit" value="Delete" name="deleteit" class="button-secondary delete" />
                    </div>
                    <br class="clear" />
                </div>
                <br class="clear" />
                <table class="widefat">
                <thead>
                    <tr>
                        <th scope="col" class="check-column"><input type="checkbox" id="chkAll" /></th>
                        <th scope="col">Variable</th>
                        <th scope="col">Default Value</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="the-list" class="list:url">';
			$i=0;
			foreach($this->options['custom-variables'] as $key => $val){
				echo'   <tr id="var-'.$key.'" ';
				if (!fmod($i,2)){
					echo 'class="alternate">'; 
				}else{
					echo '>';
				}
				echo'       <th scope="row" class="check-column"><input type="checkbox" name="chkDelete[]" class="chkDelete" value="'.$key.'" /></th>
                        <td>'.$key.'</td>
                        <td><input name="value-'.$key.'" type="text" value="'.$val.'" size="40"/></td>
						<td><p class="submit"><input type="submit" class="button" name="update-'.$key.'" value="Update Default Value" /></p></td></form>
                    </tr>
                ';            
				$i++;
			}
        
        // Create the add form
		echo '   <tr ';
		if (!fmod($i,2)){
			echo 'class="alternate">'; 
		}else{
			echo '>';
		}
		echo '       <th scope="row" class="check-column"></th>
						<td><input name="nname" id="nname" type="text" value="" size="40" /></td>
						<td><input name="ndefault" id="ndefault" type="text" value="" size="40" /></td>
						<td><p class="submit"><input type="submit" class="button" name="add" value="Add Custom Variable" /></p></td>
                    </tr>
                </tbody>
              </table>
         </div>';
            return;
		}
        // Inject the javascript for delete check all option
        echo '<script language="Javascript">
                (function($){
                    $(function(){
                        $("#chkAll").click(function(){
                            c=this.checked;
                            $(".chkDelete").each(function(i){
                                this.checked=c;
                            })
                        });
                    })
                })(jQuery);
              </script>';
        
        // Create the list
        echo '<div class="wrap">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input name="cmd" type="hidden" value="_s-xclick" />
				<input name="hosted_button_id" type="hidden" value="F6PVX3CMH8MK4" />
                <h2>' . __('Virtual Theme') . ' (<a href="#addVirtualPath">add new</a>)<input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/btn/btn_donate_SM.gif" type="image" /><img src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" /></form><iframe src="http://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FMobile-Sentience%2F184752614894685%3Fsk%3Dapp_136488953086266&amp;layout=button_count&amp;show_faces=false&amp;width=200&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe> <a target="_blank" href="http://twitter.com/#!/MobileSentience">Follow Mobile Sentience</a></h2>
                <form name="urlList" id="urlList" action="'.$this->uri.'&action=del'.'" method="post">
                <br class="clear" />
                <div class="tablenav">
                    <div class="alignleft">
                        <input type="submit" value="Delete" name="deleteit" class="button-secondary delete" />
                    </div>
                    <br class="clear" />
                </div>
                <br class="clear" />
                <table class="widefat">
                <thead>
                    <tr>
                        <th scope="col" class="check-column"><input type="checkbox" id="chkAll" /></th>
                        <th scope="col">Virtual Path</th>
                        <th scope="col">Theme</th>
                        <th scope="col">Blog Title</th>
                        <th scope="col">Tagline</th>
                    </tr>
                </thead>
                <tbody id="the-list" class="list:url">';
        $i=0;
        foreach($this->options['virtual-paths'] as $key => $url){
            echo'   <tr id="url-'.$i.'" ';
            if (!fmod($i,2)){
                echo 'class="alternate">'; 
            }else{
                echo '>';
            }
            echo'       <th scope="row" class="check-column"><input type="checkbox" name="chkDelete[]" class="chkDelete" value="'.$key.'" /></th>
                        <td><a href="'.$this->uri.'&action=urlProps&id='.$key.'"/>'.$url['url'].'</a></td>
                        <td>'.$this->getThemeTitleByTemplate($url['theme']).'</td>
                        <td>'.$url['blogname'].'</td>
                        <td>'.$url['blogdescription'].'</td>
                    </tr>
                ';            
            $i++;
        }
        
        // Create the add form
        echo ' </form>
               <form name="addVirtualPath" id="addVirtualPath" action="'.$this->uri.'&action=addVirtualPath" method="post">';
		echo '   <tr ';
		if (!fmod($i,2)){
			echo 'class="alternate">'; 
		}else{
			echo '>';
		}
		echo '       <th scope="row" class="check-column"></th>
						<td><input name="url" id="url" type="text" value="" size="40" /></td>
						<td>
							<select name="theme" id="theme" class="postform" >';
		$themes = get_themes();
		foreach($themes as $theme){
			echo '       <option value="'.$theme["Template"].'">'.$theme["Name"].'</option>';
		}
		echo '              </select>
                            <br />
							</td>
                            <td><input name="blogname" id="blogname" type="text" value="" size="40" /></td>
                            <td><input name="blogdescription" id="blogdescription" type="text" value="" size="45" /></td>
                    </tr>
					<tr>
						<th scope="row" class="check-column"></th>
						<td align="center"><p class="submit"><input type="submit" class="button" name="submit" value="Add Virtual Path" /></p></td></form>
						<form name="customVars" id="customVars" action="'.$this->uri.'&action=customVars'.'" method="post"><td><p class="submit"><input type="submit" value="Edit Custom Variables" name="editvars" class="button" /></p></td></form>
						<form name="copypath" id="copypath" action="'.$this->uri.'&action=copypath'.'" method="post">
							<td align="center"><p><select name="sourcepath" id="id" class="postform" >';
		foreach($this->options['virtual-paths'] as $key => $url){
			echo '<option value="' . $key . '">'.$url["url"].'</option>';
		}
			echo '              </select>
                            <input name="newpath" id="newpath" type="text" value="New Path" size="40" /></p></td>
							<td align="left"><p class="submit"><input type="submit" value="Copy Virtual Path" name="copypath" class="button" /></p></td></form>
					</tr>
                </tbody>
              </table>
         </div>';
    }
}       
?>
