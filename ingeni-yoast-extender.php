<?php
/*
Plugin Name: Ingeni Yoast Extender
Version: 2021.01
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
v2019.02 - Misc bug fixes
v2021.01 - Add support for EntryTitle over-riding
				 - Add support for multiple keywords and insertion into the <head>

*/


//
// Add metaboxes
//
function iye_add_meta_box( $post_type, $post ) {
	add_meta_box( 
			'ingeni-yoast-extender-meta-box',
			__( 'Ingeni Yoast Extender' ),
			'iye_render_meta_box',
			array('post', 'page'), // Add metabox to both posts and pages
			'normal',
			'default'
	);
}

// Hooks
add_action( 'add_meta_boxes', 'iye_add_meta_box', 10, 2 );
add_action( 'save_post', 'iye_save_meta_box', 10, 2 );


// Save the meta box content
function iye_save_meta_box( $post_id, $post ) {

  // Verify the nonce before proceeding.
  if ( !isset( $_POST['iye_nonce'] ) || !wp_verify_nonce( $_POST['iye_nonce'], basename( __FILE__ ) ) )
    return $post_id;

  // Get the post type object.
  $post_type = get_post_type_object( $post->post_type );

  // Check if the current user has permission to edit the post.
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;


	// Save the entry title override
  $new_value = ( isset( $_POST['iye_entry_title_override'] ) ? sanitize_text_field( trim($_POST['iye_entry_title_override']) ) : '' );
  update_post_meta( $post_id, 'iye_entry_title_override', $new_value );

	// Save the keywords
  $new_value = ( isset( $_POST['iye_meta_keywords'] ) ? sanitize_text_field( trim($_POST['iye_meta_keywords']) ) : '' );
  update_post_meta( $post_id, 'iye_meta_keywords', $new_value );
}


// Display the metabox
function iye_render_meta_box( $post ) {
  wp_nonce_field( basename( __FILE__ ), 'iye_nonce' ); ?>

  <p>
    <label for="iye_entry_title_override"><?php _e( "Override the default page Entry Title." ); ?></label>
    <br />
    <input class="widefat" type="text" name="iye_entry_title_override" id="iye_entry_title_override" value="<?php echo esc_attr( get_post_meta( $post->ID, 'iye_entry_title_override', true ) ); ?>" />
  </p>

	<p>
    <label for="iye_meta_keywords"><?php _e( "Add SEO meta keywords or phrases, comma separated." ); ?></label>
    <br />
		<textarea id="iye_meta_keywords" name="iye_meta_keywords" rows="4" cols="50"><?php echo trim(esc_attr( get_post_meta( $post->ID, 'iye_meta_keywords', true ) ) ); ?></textarea>
  </p>
	<?php
}


// Hook the page titles
add_filter( 'the_title', 'iye_title_overrider', 10, 2 );
function iye_title_overrider( $title, $id = null ) {
	if ( $id ) {
	$override_title = get_post_meta( $id, 'iye_entry_title_override', true );
		if ($override_title) {
			$title = $override_title;
		}
	}
	return $title;
}


// Hook the meta keywords
add_action('wp_head', 'iye_add_keywords' );
function iye_add_keywords() {
	if ( is_page() || is_single() ) {
    $id = get_queried_object_id();
		if ($id) {
    	$meta_keywords = trim( get_post_meta( $id, 'iye_meta_keywords', true ) );
			if ($meta_keywords != '') {
				echo ( '<meta name="keywords" content="'.$meta_keywords.'" />' );
			}
		} 
	} 
}


// Set default SEO meta title if none found
add_filter( 'wpseo_title', 'yoast_extender_add_title', 10, 1 );
add_filter( 'wpseo_metakey', 'yoast_extender_add_title', 10, 1 );
function yoast_extender_add_title( $str ) {
	if (strlen($str) == 0) {
		global $post;

		if ( strlen($post->post_title) > 0 ) {
			$str = $post->post_title;
		}
	}
  return $str;
}


// Set default SEO meta description if none found
add_filter( 'wpseo_metadesc', 'yoast_extender_add_desc', 10, 1 );
function yoast_extender_add_desc( $str ) {
	if (strlen($str) == 0) {
		global $post;

		if ( strlen($post->post_excerpt) > 0 ) {
			$str = $post->post_excerpt;
		} else {
			$str = get_opening_sentence( $post->post_content );
		}
	}
  return $str;
}


/*
<a class="button" href="https://naisda.com.au/wp-content/uploads/2019/04/NAISDA_Application_Form_2019-10.pdf" target="_blank" rel="noopener" 
onclick="ga('send', 'event', 'Download', 'PDF', 'uploads/2019/04/NAISDA_Application_Form_2019-10.pdf');">DOWNLOAD NAISDA APPLICATION PACK</a>
*/

add_shortcode( 'ga-track-event','do_ingeni_ga_track_event' );
function do_ingeni_ga_track_event( $args ) {

	$params = shortcode_atts( array(
		'file_url' => "",
		'new_tab' => 1,
		'category' => "event",
		'action' => "Download",
		'opt_label' => "",
		'opt_value' => "",
		'opt_noninteraction' => 1,
		'text' => "Download Now",
		'class' => "",
	), $args );

	//fb_log($params['file_url']);
	$domain = get_bloginfo('url');
	//fb_log($domain);
	if (  !startsWith( $params['file_url'], 'http' ) ) {
		$params['file_url'] = str_ireplace('[url]', $domain, $params['file_url']);
		if ( !startsWith( $params['file_url'], $domain ) ) {
			
			$params['file_url'] = str_ireplace( "//", "/", $params['file_url'] );
			$params['file_url'] = $domain . $params['file_url'];
		}
	}

	$path_parts = pathinfo( $params['file_url'] );
	//fb_log(print_r($path_parts,true));
	if ( strlen( $params['opt_label'] ) == 0 ) {
		
		$params['opt_label'] = $path_parts['extension'];
	}

	if ( strlen( $params['opt_label'] ) == '') {
		$params['opt_label'] = 'file';
	}

	if ( strlen( $params['opt_value'] ) == '') {
		$params['opt_value'] = $path_parts['filename'];
	}

	$target = 'target="_blank" ';
	if ( $params["new_tab"] == 0 ) {
		$target = '';
	}

	$retHtml = '<a class="'.$params['class'].'" href="'.$params['file_url'].'" '.$target.' rel="noopener" onclick="ga(\'send\', \'' . $params['category']. '\', \'' . $params['action']. '\', \'' . $params['opt_label']. '\', \'' . $params['opt_value']. '\', ' . $params['opt_noninteraction']. ');">' . $params['text'] . '</a>';
	//fb_log($retHtml);
	return $retHtml;
}



if (!function_exists("get_opening_sentence")) {
	function get_opening_sentence($content) {
		$retVal = $content;

		// Remove H4s
		$clean = preg_replace('#<h4>(.*?)</h4>#', '', $content);
		$clean = wp_strip_all_tags($clean);

		$period = strpos($clean, ".");
		if ($period === false)
			$period = strlen($clean)-1;
		$exclaim = strpos($clean, "!");
		if ($exclaim === false)
			$exclaim = strlen($clean)-1;
		$question = strpos($clean, "?");
		if ($question === false)
			$question = strlen($clean)-1;

		$loc = min( array($period,$exclaim,$question));

		$retVal = substr($clean,0, ($loc+1) );

		return $retVal;
	}
}




if (!function_exists("startsWith")) {
  function startsWith($haystack, $needle) {
      // search backwards starting from haystack length characters from the end
      return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
  }
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