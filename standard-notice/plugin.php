<?php
/*
Plugin Name: Standard Notice
Plugin URI: http://github.com/eightbit/plugins/standard-notice
Description: Easily add short notices and announcements above posts. Displays on both the blog and in RSS feeds.
Version: 1.0.1
Author: Tom McFarlin
Author URI: http://tommcfarlin.com
Author Email: tom@8bit.io
License:

  Copyright 2012 Tom McFarlin (tom@8bit.io)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

class Standard_Notice {

	// We write these out inline so that it is also styled in RSS readers
	private $styles = 'padding: 1em; background: #DDD; margin: 0 0 1em 0; border: 1px solid #CCC;';
	 
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, admin styles, and content filters.
	 */
	function __construct() {
	
		load_plugin_textdomain( 'standard-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	
		// Include admin styles
		add_action( 'admin_print_styles', array( &$this, 'add_admin_styles' ) );
		
		// Add the post meta box to the post editor
		add_action( 'add_meta_boxes', array( &$this, 'add_notice_metabox' ) );
		add_action( 'save_post', array( &$this, 'save_notice' ) );
		
		// Append the notice before the content in both the blog and in the feed.
	    add_filter( 'the_content', array( &$this, 'prepend_standard_notice' ) );

	} // end constructor
	
	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	 
	/**
 	 * Introduces the admin styles
 	 */
 	 function add_admin_styles() {
	 	 
	 	 wp_register_style( 'standard-notice', plugins_url() . '/standard-notice/css/admin.css' );
	 	 wp_enqueue_style( 'standard-notice' );
	 	 
 	 } // end add_admin_styles
	
	/**
	 * Adds the meta box below the post content editor on the post edit dashboard.
	 */
	 function add_notice_metabox() {
	
		add_meta_box(
			'standard_notice',
			__( 'Post Notice', 'standard-notice' ),
			array( &$this, 'standard_notice_display' ),
			'post',
			'normal',
			'high'
		);

	} // end add_notice_metabox
	
	/**
	 * Renders the nonce and the textarea for the notice.
	 */
	function standard_notice_display( $post ) {
		
		wp_nonce_field( plugin_basename( __FILE__ ), 'standard_notice_nonce' );

		echo '<textarea id="standard_notice" name="standard_notice">' . get_post_meta( $post->ID, 'standard_notice', true ) . '</textarea>';
		
	} // end standard_notice_display
	
	/**
	 * Saves the notice for the given post.
	 *
	 * @params	$post_id	The ID of the post that we're serializing
	 */
	function save_notice( $post_id ) {
		
		if( isset( $_POST['standard_notice_nonce'] ) && isset( $_POST['post_type'] ) ) {
		
			// Don't save if the user hasn't submitted the changes
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			} // end if
			
			// Verify that the input is coming from the proper form
			if( ! wp_verify_nonce( $_POST['standard_notice_nonce'], plugin_basename( __FILE__ ) ) ) {
				return;
			} // end if
			
			// Make sure the user has permissions to post
			if( 'post' == $_POST['post_type']) {
				if( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				} // end if
			} // end if/else
		
			// Read the Link's URL
			$link_url = '';
			if( isset( $_POST['standard_notice'] ) ) {
				$link_url = $_POST['standard_notice'];
			} // end if
			
			// If the value exists, delete it first. I don't want to write extra rows into the table.
			if ( 0 == count( get_post_meta( $post_id, 'standard_notice' ) ) ) {
				delete_post_meta( $post_id, 'standard_notice' );
			} // end if
	
			// Update it for this post.
			update_post_meta( $post_id, 'standard_notice', $link_url );
	
		} // end if
	
	} // end save_notice
	
	/**
 	 * Prepends the content with the notice, if specified.
 	 *
 	 * @params	$content	The post content.
 	 * @returns				The post content with the prepended notice (if specified).
	 */
	function prepend_standard_notice( $content ) {
    	
    	// If there is a notice, prepend it to the content
    	if( '' != get_post_meta( get_the_ID(), 'standard_notice', true ) ) {
    	
	    	$post_notice = '<div style="' . $this->styles . '" class="standard-notice">';
	    		$post_notice .= get_post_meta( get_the_ID(), 'standard_notice', true );
	    	$post_notice .= '</div><!-- /.standard-notice -->';
	    	
	    	$content = $post_notice . $content;
	    	
    	} // end if 
    	
    	return $content;
    	
	} // end prepend_standard_notice
  
} // end class

new Standard_Notice();
?>