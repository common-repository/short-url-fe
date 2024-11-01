<?php
/*
Plugin Name: Short URL FE
Description: This plugin automatically adds a short URL for all blog posts (in FrontEnd) after post content. It uses https://v.gd or https://tinyurl.com/ service to get short URLs.
Version: 1.0.0
Author: José María Ferri Azorín
Text Domain: short-url-fe
*/

/*  This file is part of Short URL FE plugin. Copyright 2022 José María Ferri Azorín

	This plugin is based on Prasanna SP Tiny URL (https://wordpress.org/plugins/tiny-url/) plugin and uses some of its code.

    TinyURL is a trademark of TinyURL, LLC
	v.gd is a trademark of V.gd

    Short URL FE plugin is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Short URL plugin is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You can read the GNU General Public License at https://www.gnu.org/licenses/
*/

/**
 * **********************************************************************
 * This plugin is based on Prasanna SP Tiny URL plugin (Discontinued?)
 * *********************************************************************
 */

 /**
  * Get the short URL for current post/page from selected provider
  *
  * @param string $url
  * @return void
  */
function shorturlfe_short_url_fe(string $url) {
	$shorturlfe = __("(not valid for localhost)", "short-url-fe");
	$pattern = "/^(https?:\/\/)?(localhost|127\.0\.0\.1)/i";
	// if not in localhost
	if(!preg_match($pattern, $url)){
		$providers = array(
			"0"=>array("url"=>"https://v.gd/create.php?format=simple&url=%s", "context"=>array("http" => array("ignore_errors" => true))),
			"1"=>array("url"=>"https://tinyurl.com/api-create.php?url=%s", "context"=>null)
		);
		$options = get_option('shorturlfe_options');
		// get selected provider
		$provider = $options['shorturlfe_provider'];
		
		// load information
		$provider_url = $providers[$provider]["url"];
		$context = $providers[$provider]["context"];
		if($context){
			$context = \stream_context_create($context);
		}
		// make request
		$shorturlfe = @file_get_contents(sprintf($provider_url, $url), false, $context);
	}
    return $shorturlfe;
}
/**
 * If enabled "Show Copy URL button" option, the button HTML code is generated
 *
 * @param bool $action - Whether to add the click action or not. Not added when button show in backend, because it is shown just for preview, it is not functional
 * @return string - HTML button code, inside a span block
 */
function shorturlfe_show_copy_button(bool $action=true):string {
	$ret = "";
	$options = get_option('shorturlfe_options');
	$buttontext = trim($options['shorturlfe_button_text']);
	$buttonclass = trim($options['shorturlfe_button_class']);
	
	//$url = shorturlfe_short_url_fe(get_permalink($post->ID));
	if ( isset($options['copy_url_button'])){
		$onclick="";
		if($action){	// if action needed (when shown in front end)
			$onclick = "onclick='shorturlfe.copyToClipboard()'";
		}
		$ret = "<button type='button' id='short-url-fe-button' class='short-url-fe-button ".esc_html($buttonclass)."' $onclick>".esc_html($buttontext)."</button>";
	}
	return $ret;
}

/**
 * Generate the HTML code for a input box with a label to hold short url text
 *
 * @category Filter
 * @param string $showshorturlfe
 * @return string
 */
function shorturlfe_show_short_url_fe(string $showshorturlfe):string {
	$options = get_option('shorturlfe_options');
	$showFor = $options['shorturlfe_show_for'];
	// If show short URL for:
	// 0 = All users
	// 1 = Logged users and user is logged
	// 2 = Logged users that can edit current post and user is logged and user can edit current post
	if($showFor == 0 || ($showFor == 1 && is_user_logged_in()) || ($showFor == 2 && is_user_logged_in() && current_user_can( 'edit_post' ))){ 
		$shorturlfe_posttitle = $options['shorturlfe_title'];
		$shorturlfe_pagetitle = $options['shorturlfe_page_title'];
		$textboxclass = trim($options['shorturlfe_textbox_class']);
		$url = shorturlfe_short_url_fe(get_permalink($post->ID));

		if ( is_single() && !isset($options['shorturlfe_hide_on_posts']) ){
			$label = $shorturlfe_posttitle;
		}elseif ( is_page() && isset($options['shorturlfe_show_on_pages']) ){
			$label = $shorturlfe_pagetitle;
		}

		$showshorturlfe .= "<p class='short-url-fe' id='short-url-fe'><label for='shorturlfe'>$label</label><input class='$textboxclass' type='text' id='short-url-fe-textbox' value='$url' readonly='readonly' onclick='shorturlfe.selectText(this)'/>".shorturlfe_show_copy_button()."</p>";
	}	
	return $showshorturlfe;
}
/**
 * @category Action
 */
add_filter('the_content', 'shorturlfe_show_short_url_fe');

function shorturlfe_load_resources() {
	$options = get_option('shorturlfe_options');
	if ( isset($options['copy_url_button']) ) {
		wp_enqueue_script('suf-scripts', plugins_url('/js/scripts.js', __FILE__));
	}
	wp_enqueue_style('suf-styles', plugins_url('/css/styles.css', __FILE__));
}

/**
 * @category Action
 */
add_action('wp_enqueue_scripts', 'shorturlfe_load_resources');

function shorturlfe_load_resources_admin() {
	$options = get_option('shorturlfe_options');
	if ( isset($options['copy_url_button']) ) {
		wp_enqueue_script('suf-scripts', plugins_url('/js/scripts.js', __FILE__));
	}
	wp_enqueue_style('suf-styles', plugins_url('/css/admin.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'shorturlfe_load_resources_admin');

// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'shorturlfe_add_defaults');
register_uninstall_hook(__FILE__, 'shorturlfe_delete_plugin_options');
add_action('admin_init', 'shorturlfe_init' );
add_action('admin_menu', 'shorturlfe_add_options_page');
add_filter('plugin_action_links', 'shorturlfe_plugin_action_links', 10, 2 );

/**
 * Delete options table entries ONLY when plugin deactivated AND deleted
 *
 * @category hook
 * @return void
 */
function shorturlfe_delete_plugin_options() {
	delete_option('shorturlfe_options');
}

/**
 * Create default options
 *
 * @category hook
 * @return void
 */
function shorturlfe_add_defaults() {
	$tmp = get_option('shorturlfe_options');
	if(($tmp['shorturlfe_default_options_db'] == '1') || (!\is_array($tmp))) {
		$arr = array(
			"shorturlfe_title" => __("Short URL for this post:", 'short-url-fe'),
			"shorturlfe_page_title" => __("Short URL for this page:", 'short-url-fe'),
			"shorturlfe_default_options_db" => "",
			"shorturlfe_button_text" => __("Copy"),
			"shorturlfe_button_class" => "",
			"shorturlfe_textbox_class" => "",
			"shorturlfe_provider" => 0,		// default provider: v.gd
			"shorturlfe_show_for" => 1		// default show only for registered users
		);
		update_option('shorturlfe_options', $arr);
	}
}
/**
 *
 * @category Action
 * @return void
 */
function shorturlfe_init(){
	register_setting( 'shorturlfe_plugin_options', 'shorturlfe_options', 'shorturlfe_validate_options' );
}

/**
 *
 * @category Action
 * @return void
 */
function shorturlfe_add_options_page() {
	add_options_page('Short URL FrontEnd Options Page', 'Short URL FE', 'manage_options', __FILE__, 'shorturlfe_options_page_form');
}

/**
 * Creates the option page form
 *
 * @return void
 */
function shorturlfe_options_page_form() {
	?>
	<div class="wrap">
		<h2><?php _e('Short URL FE Options', 'short-url-fe')?></h2>
		<h4><?php 
			// TRANSLATORS: %s and %s are replaced in code by providers url, please, respect when translate
			echo sprintf(__('This plugin automatically adds a short URL for all blog posts (in FrontEnd) after post content. Uses %s or %s service to get short URLs.', 'short-url-fe'), "<a href='https://v.gd' target='_blank'>https://v.gd</a>", "<a href='https://tinyurl.com/' target='_blank'>https://tinyurl.com/</a>");
		?></h4>
		<h3><?php _e('Set your options for Short URL FE plugin', 'short-url-fe')?>.</h3>

		<form method="post" action="options.php">
			<?php settings_fields('shorturlfe_plugin_options'); ?>
			<?php 
				$options = get_option('shorturlfe_options');
			    $shorturlfe_adminpagetitle = $options['shorturlfe_title'];
			?>

			<table class="form-table">
			
				<h4><?php _e('Title Settings', 'short-url-fe')?></h4>
				<tr>
					<th scope="row"><?php _e('Short URL FE title for posts:', 'short-url-fe')?></th>
					<td>
						<input type="text" size="50" name="shorturlfe_options[shorturlfe_title]" value="<?php echo esc_html($options['shorturlfe_title']); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Short URL FE title for pages:', 'short-url-fe')?></th>
					<td>
						<input type="text" size="50" name="shorturlfe_options[shorturlfe_page_title]" value="<?php echo esc_html($options['shorturlfe_page_title']); ?>" />
					</td>
				</tr>
			</table>
			
			<table class="form-table">
				<h4><?php _e('Short URL FE Appearance', 'short-url-fe')?></h4>
				<tr>
					<th scope="row"><?php _e('Short URL provider', 'short-url-fe')?></th>
					<td>
						<select name="shorturlfe_options[shorturlfe_provider]">
							<option value='0' <?php selected('0', $options['shorturlfe_provider']);?>>v.gd</option>
							<option value='1' <?php selected('1', $options['shorturlfe_provider']);?>>Tiny URL</option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php _e('Show Short URL FE on pages', 'short-url-fe')?></th>
					<td>
						<input name="shorturlfe_options[shorturlfe_show_on_pages]" type="checkbox" value="1" <?php if (isset($options['shorturlfe_show_on_pages'])) { checked('1', $options['shorturlfe_show_on_pages']); } ?> />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Hide Short URL FE on blog posts', 'short-url-fe')?></th>
					<td>
						<input name="shorturlfe_options[shorturlfe_hide_on_posts]" type="checkbox" value="1" <?php if (isset($options['shorturlfe_hide_on_posts'])) { checked('1', $options['shorturlfe_hide_on_posts']); } ?> />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Show short link for:', 'short-url-fe')?></th>
					<td>
						<select name="shorturlfe_options[shorturlfe_show_for]">
							<option value='0' <?php selected('0', $options['shorturlfe_show_for']);?>><?php _e('All users', 'short-url-fe');?></option>
							<option value='1' <?php selected('1', $options['shorturlfe_show_for']);?>><?php _e('Registered users', 'short-url-fe');?></option>
							<option value='2' <?php selected('2', $options['shorturlfe_show_for']);?>><?php _e('Registered users that can edit', 'short-url-fe');?></option>
						</select>
					</td>
				</tr>
			</table>
			<table class="form-table">
				<h4><?php _e('Copy URL to Clipboard Option', 'short-url-fe')?></h4>
				<tr>
					<th scope="row"><?php _e('Show Copy URL button', 'short-url-fe');?></th>
					<td>
						<input name="shorturlfe_options[copy_url_button]" type="checkbox" value="1" <?php if (isset($options['copy_url_button'])) { checked('1', $options['copy_url_button']); } ?> />
						<span class="description"><?php _e('Selecting this will add a button next to Short URL textbox to copy the URL to clipboard. (See live example below)', 'short-url-fe')?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Copy URL button text:', 'short-url-fe')?></th>
					<td>
						<input type="text" size="25" name="shorturlfe_options[shorturlfe_button_text]" value="<?php echo esc_html($options['shorturlfe_button_text']); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Custom Class for Texbox:', 'short-url-fe')?></th>
					<td>
						<input type="text" size="25" name="shorturlfe_options[shorturlfe_textbox_class]" value="<?php echo esc_html($options['shorturlfe_textbox_class']); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Custom Class for Button:', 'short-url-fe')?></th>
					<td>
						<input type="text" size="25" name="shorturlfe_options[shorturlfe_button_class]" value="<?php echo esc_html($options['shorturlfe_button_class']); ?>" />
					</td>
				</tr>
				<tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row"><?php _e('Database Options:', 'short-url-fe');?></th>
					<td>
						<input name="shorturlfe_options[shorturlfe_default_options_db]" type="checkbox" value="1" <?php if (isset($options['shorturlfe_default_options_db'])) { checked('1', $options['shorturlfe_default_options_db']); } ?> /><label><?php _e('Restore defaults upon plugin deactivation/reactivation', 'short-url-fe');?></label>
						<div style="color:#666666;margin-left:2px;"><?php _e('Only check this if you want to reset plugin settings upon Plugin reactivation', 'short-url-fe')?></div>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			<div id='short-url-fe-example-box'>
				<table class="form-table">
					<tr>
						<th scope="row"><h3><?php _e('Live example', 'short-url-fe')?></h3></th>
						<td>
							<p class="short-url-fe" id="short-url-fe"><label for="shorturlfe"><?php echo esc_html($shorturlfe_adminpagetitle); ?></label><input type="text" id="shorturlfe" readonly='readonly' value="http://tinyurl.com/tinyurlplugin" /><?php echo shorturlfe_show_copy_button(false);?></p>
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
	<?php	
}

/**
 *  Sanitize and validate input
 *
 * @param array $input
 * @return array
 */
function shorturlfe_validate_options(array $input):array {
	 // strip html from textboxes
	$input['shorturlfe_title'] =  wp_filter_nohtml_kses($input['shorturlfe_title']); // strip html tags, and escape characters
	$input['shorturlfe_page_title'] =  wp_filter_nohtml_kses($input['shorturlfe_page_title']);
	$input['shorturlfe_button_text'] =  wp_filter_nohtml_kses($input['shorturlfe_button_text']);
	$input['shorturlfe_textbox_class'] =  sanitize_html_class($input['shorturlfe_textbox_class']);
	$input['shorturlfe_button_class'] =  sanitize_html_class($input['shorturlfe_button_class']);
	return $input;
}

/**
 * Display a Settings link on the main Plugins page
 *
 * @param array $links
 * @param string $file
 * @return array
 */ 
function shorturlfe_plugin_action_links(array $links, string $file ):array {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$shorturlfe_links1 = '<a href="'.get_admin_url().'options-general.php?page=short-url-fe/short-url-fe.php">'.__('Settings').'</a>';
		
		// make the 'Settings' link appear first
		\array_unshift( $links, $shorturlfe_links1);
	}
	return $links;
}

function shorturlfe_initLanguages(){
	$pluginDir = dirname(plugin_basename(__FILE__));
	load_plugin_textdomain('short-url-fe', false, $pluginDir . '/languages/');
}
shorturlfe_initLanguages();
?>
