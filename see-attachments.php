<?php
/*
 Plugin Name: See attachments
 Plugin URI: https://www.mijnpress.nl
 Description: Shows all attachments for a post or page
 Version: 1.5.4
 Author: Ramon Fincken
 Author URI: https://www.mijnpress.nl
 Images by: http://24charlie.deviantart.com/art/Black-Pearl-Files-78798192
 */
if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

if(!class_exists('mijnpress_plugin_framework'))
{
	include('mijnpress_plugin_framework.php');
}

class see_attachments extends mijnpress_plugin_framework
{
	function __construct()
	{
		$this->showcredits = true;
		$this->showcredits_fordevelopers = true;
		$this->plugin_title = 'See attachments';
		$this->plugin_class = 'see_attachments';
		$this->plugin_filename = 'see-attachments/see-attachments.php';
		$this->plugin_config_url = NULL;
	}

	function see_attachments()
	{
		$args= func_get_args();
		call_user_func_array
		(
		    array(&$this, '__construct'),
		    $args
		);
	}

	/**
	 * Additional links on the plugin page
	 */
	static function addPluginContent_($links, $file, $strictstandardvoid1 = '', $strictstandardvoid2 = '') {
		$plugin = new see_attachments();
		$links = parent::addPluginContent($plugin->plugin_filename,$links,$file,$plugin->plugin_config_url);
		return $links;
	}
}

// Admin only
if(mijnpress_plugin_framework::is_admin())
{
	add_filter('plugin_row_meta',array('see_attachments', 'addPluginContent_'), 10, 2);
}

/* Prints the box content */
function plugin_see_attachments_inner_custom_box($post) {
	$args = array(
		'post_type' => 'attachment',
		'numberposts' => -1,
		'post_status' => null,
		'post_parent' => $post->ID
	);
	$attachments = get_posts($args);
	if ($attachments) {
		$image_dir = mijnpress_plugin_framework::get_plugin_url(NULL,__FILE__).'/images/';
		$i = 0;
		foreach ($attachments as $attachment) {
			$i++;
			echo "\n";
			echo '<div style="float: left; border: 1px solid #dfdfdf; margin-bottom: 5px; margin-right: 5px;"><div style="border: 1px solid #fff; padding: 0 5px 0 5px; min-width: 120px; height: 170px; ">';
			echo '<p style="margin-bottom: 0;"><small>Bijlage '.$i.'</small></p>';

			$icon = wp_mime_type_icon($attachment->post_mime_type);
			$temp = end(explode('/',$icon));
			if($temp == 'default.png')
			{
				$end = end(explode('.',$attachment->guid));
				$file = WP_PLUGIN_DIR.'/see-attachments/images/'.$end.'.png';
				// Wait! We have a better one!
				if(file_exists($file))
				{
					$icon = $image_dir.$end.'.png';
				}
			}

			// Show the real file? Even better!
			if(in_array($end, array('png','jpeg','jpg','gif','bmp')))
			{
				$icon = $attachment->guid;
			}

			$title = apply_filters('the_title', $attachment->post_title);
			$icon_html = '<a href="'.$attachment->guid.'" target="_blank"><img src="'.$icon.'" style="max-width: 80px; max-height: 80px;"></a>';
			$href_human = end(explode('uploads/',$attachment->guid));
			
			echo '<p style="margin: 0;"><strong>'.$title.'</strong></p>';
			echo '<p style="line-height: 12px;"><small>Link: <br/><a href="'.$attachment->guid.'" target="_blank">'.$href_human.'</a></small></p>';
			echo '<p style="text-align: center; padding-top: 5px;">'.$icon_html.'</p>';
			
			echo '</div></div><!-- end div for attachment -->';
		}
		echo '<div style="clear: both;"></div>';
	}
	else
	{
		echo '<div style="clear: both;">'.__('Geen bijlages gevonden.').'</div>';
	}
}

/* Adds a box to the main column on all post_type edit screens */
function plugin_see_attachments_add_custom_box() {
	$post_types=get_post_types('','names'); 
	foreach ($post_types as $post_type ) {
		add_meta_box( 'plugin_see_attachments_sectionid', __( 'Bijlages', 'plugin_see_attachments' ), 'plugin_see_attachments_inner_custom_box', $post_type );
	}  
}

/* Define the custom boxes */
add_action('add_meta_boxes', 'plugin_see_attachments_add_custom_box');
?>
