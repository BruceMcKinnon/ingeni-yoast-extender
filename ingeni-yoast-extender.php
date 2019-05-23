<?php
/*
Plugin Name: Ingeni Yoast Extender
Version: 2019.01
Plugin URI: http://ingeni.net
Author: Bruce McKinnon - ingeni.net
Author URI: http://ingeni.net
Description: Provides automatic SEO markup where none are specified for Wordpress
*/

/*
Copyright (c) 2019 Ingeni Web Solutions
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt

Disclaimer: 
	Use at your own risk. No warranty expressed or implied is provided.
	This program is free software; you can redistribute it and/or modify 
	it under the terms of the GNU General Public License as published by 
	the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 	See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


Requires : Wordpress 3.x or newer ,PHP 5 +

v2019.01 - Initial version


*/


// Set default SEO meta description if none found
function yoast_extender_add_desc( $str ) {
	if (strlen($str) == 0) {
		global $post;

		if ( strlen($post->post_excerpt) > 0 ) {
			$str = $post->post_excerpt;
		} else {
			$str = get_first_sentence( $post->post_content );
		}
	}
  return $str;
}
add_filter( 'wpseo_metadesc', 'yoast_extender_add_desc', 10, 1 );

/*
<a class="button" href="https://naisda.com.au/wp-content/uploads/2019/04/NAISDA_Application_Form_2019-10.pdf" target="_blank" rel="noopener" 
onclick="ga('send', 'event', 'Download', 'PDF', 'uploads/2019/04/NAISDA_Application_Form_2019-10.pdf');">DOWNLOAD NAISDA APPLICATION PACK</a>
*/

add_shortcode( 'ga-track-event','do_ingeni_ga_track_event' );
function do_ingeni_ga_track_event( $args ) {

	$params = shortcode_atts( array(
		'file_url' => '',
		'new_tab' => 1,
		'category' => 'event',
		'action' => 'Download',
		'opt_label' => '',
		'opt_value' => '',
		'opt_noninteraction' => 0,
		'text' => 'Download Now',
		'class' => '',
	), $args );

	$path_parts = pathinfo( $params['file_url'] );

	if ( strlen( $params['opt_label'] ) == 0 ) {
		
		$params['opt_label'] = $path_parts['extension'];
	}

	if ( strlen( $params['opt_label'] ) == '') {
		$params['opt_label'] = 'file';
	}

	if ( strlen( $params['label'] ) == '') {
		$params['label'] = 'file';
	}

	if ( strlen( $params['opt_value'] ) == '') {
		$params['opt_value'] = $path_parts['filename'];
	}

	$target = 'target="_blank" ';
	if ( $params["new_tab"] == 0 ) {
		$target = '';
	}

	$retHtml = '<a class="'.$params['class'].' href="'.$params['file_url'].'" '.$target.' rel="noopener" onclick="ga(\'send\', \'' . $params['category']. '\', \'' . $params['action']. '\', \'' . $params['opt_label']. '\', \'' . $params['opt_value']. '\', \'' . $params['opt_noninteraction']. '\');">' . $params['text'] . '</a>';

	return $retHtml;
}


function ingeni_load_yoast_extender() {
	// Init auto-update from GitHub repo
	require 'plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/BruceMcKinnon/ingeni-yoast-extender',
		__FILE__,
		'ingeni-yoast-extender'
	);
}
add_action( 'wp_enqueue_scripts', 'ingeni_load_yoast_extender' );


// Plugin activation/deactivation hooks
function ingeni_yoast_extender_activation() {
	flush_rewrite_rules( false );
}
register_activation_hook(__FILE__, 'ingeni_yoast_extender_activation');

function ingeni_yoast_extender_deactivation() {
  flush_rewrite_rules( false );
}
register_deactivation_hook( __FILE__, 'ingeni_yoast_extender_deactivation' );

?>