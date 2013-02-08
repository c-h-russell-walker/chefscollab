<?php

// WP Unformatted
// version 1.1, 2007-01-24
//
// Copyright (c) 2004-2007 Alex King
// http://alexking.org/projects/wordpress
//
// This is an add-on for WordPress
// http://wordpress.org/
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// *****************************************************************

/*
Plugin Name: WP Unformatted
Plugin URI: http://alexking.org/projects/wordpress
Description: With this enabled, you can add a custom field of 'sponge' set to '1' to a post to disable the auto formatting and a custom field of 'sandpaper' set to '1' to a post to disable the auto smart-quote conversion.
Version: 1.1
Author: Alex King
Author URI: http://alexking.org
*/ 

// conditional auto-p function
function wp_sponge($pee) {
	global $post;
	if (get_post_meta($post->ID, 'sponge', true) == '1') {
		return $pee;
	}
	else {
		return wpautop($pee);
	}
}

// conditional texturize function
function wp_sandpaper($text) {
	global $post;
	if (get_post_meta($post->ID, 'sandpaper', true) == '1') {
		return $text;
	}
	else {
		return wptexturize($text);
	}
}

// disable auto-p
remove_filter('the_content', 'wpautop');

// add conditional auto-p
add_filter('the_content', 'wp_sponge');

// disable texturize
remove_filter('the_content', 'wptexturize');

// add conditional texturize
add_filter('the_content', 'wp_sandpaper');

?>