<?php
/*
Plugin Name: is_frontpage 
Plugin URI: http://www.bos89.nl
Description: A plugin that adds the is_frontpage() function which tell us if we are at the static frontpage. For Wordpress v2.1 (and higher?)
Author: M. Stegink
Version: 1.0
Author URI: http://www.bos89.nl/
*/
?>
<?php

function is_frontpage()
{
	global $post; 
	$id = $post->ID;
	$show_on_front = get_option('show_on_front');
	$page_on_front = get_option('page_on_front');

	if ($show_on_front == 'page' && $page_on_front == $id ) {
		return true;
	} else {
		return false;
	}
	
}

?>