<?php

// Remove the WordPress Generator Meta Tag
function remove_generator_filter() { return ''; }

if (function_exists('add_filter')) {
    $types = array('html', 'xhtml', 'atom', 'rss2', /*'rdf',*/ 'comment', 'export');

    foreach ($types as $type)
    add_filter('get_the_generator_'.$type, 'remove_generator_filter');
}

// Widget Settings

if ( function_exists('register_sidebar') ) {
	register_sidebar(array(
		'name' => 'Front Left Navigation',
		'before_widget' => '<li>', // Removes <li>
		'after_widget' => '</li>', // Removes </li>
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	/*register_sidebar(array(
		'name' => 'Front Right Sidebar',
		'before_widget' => '<li>', // Removes <li>
		'after_widget' => '</li>', // Removes </li>
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));*/
}

// Search
function widget_sumanasa_search() {
	include (TEMPLATEPATH . '/searchform.php');
}

function widget_upcoming_calendar() {
	include (TEMPLATEPATH . '/upcoming.php');
}

function widget_newsletter_subscribe() {
	include (TEMPLATEPATH . '/newsletter.php');
}

function widget_join_now() {
	include (TEMPLATEPATH . '/joinbutton.php');
}

function widget_recent_communiques() {
	include (TEMPLATEPATH . '/recent-communiques.php');
}


// This is the built-in widget_list_pages, but without the title section.
function widget_list_pages( $args ) {
	extract( $args );
	$options = get_option( 'widget_pages' );
	
	$title = empty( $options['title'] ) ? __( 'Pages' ) : $options['title'];
	$sortby = empty( $options['sortby'] ) ? 'menu_order' : $options['sortby'];
	$exclude = empty( $options['exclude'] ) ? '' : '&exclude=' . $options['exclude'];
	
	if ( $sortby == 'menu_order' ) {
		$sortby = 'menu_order, post_title';
	}
	
	$out = function_exists('wswwpx_fold_page_list') ? wswwpx_fold_page_list('title_li=&echo=0&sort_column=' . $sortby . $exclude) :
		wp_list_pages( 'title_li=&echo=0&sort_column=' . $sortby . $exclude );
	
	if ( !empty( $out ) ) {
?>
	<?php echo $before_widget; ?>
		<ul class="menu">
			<?php echo $out; ?>
		</ul>
	<?php echo $after_widget; ?>
<?php
	}
}

if ( function_exists('register_sidebar_widget') ) {
    register_sidebar_widget(__('Search'), 'widget_sumanasa_search');
	register_sidebar_widget(__('Upcoming'), 'widget_upcoming_calendar');
	register_sidebar_widget(__('Pages'), 'widget_list_pages');
	register_sidebar_widget(__('Newsletter'), 'widget_newsletter_subscribe');
	register_sidebar_widget(__('Join Now'), 'widget_join_now');
	register_sidebar_widget(__('Recent Communiques'), 'widget_recent_communiques');
}


/** 
 * Returns a string containing a two letter state name abbreviation. If no abbreviation matches the state name, the name is returned.
 * 
 * @param int $stateName the full name of a US State 
 * @param array $customList an associative array with the abbreviations as the key and full names as the value,
 *        for adding, editing, or removing states 
 *       ie array('QC' => 'Quebec','MA' => 'Massachusett', 'NJ' => '') would add Quebec to the return possibilities, 
 *       change Massachusetts to Massachusett, and eliminate New Jersey from the return possibilities.
 * @return string two letter abbreviation for the given state  
 */ 
function stateAbbrev($stateName) 
{ 
	/* State names and stuff for upcoming */
	$states = array('AL'=>"Alabama", 'AK'=>"Alaska", 'AZ'=>"Arizona", 'AR'=>"Arkansas", 'CA'=>"California", 'CO'=>"Colorado", 'CT'=>"Connecticut", 'DE'=>"Delaware", 'DC'=>"District Of Columbia", 'FL'=>"Florida", 'GA'=>"Georgia", 'HI'=>"Hawaii", 'ID'=>"Idaho", 'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa", 'KS'=>"Kansas", 'KY'=>"Kentucky", 'LA'=>"Louisiana", 'ME'=>"Maine", 'MD'=>"Maryland", 'MA'=>"Massachusetts", 'MI'=>"Michigan", 'MN'=>"Minnesota", 'MS'=>"Mississippi", 'MO'=>"Missouri", 'MT'=>"Montana", 'NE'=>"Nebraska", 'NV'=>"Nevada", 'NH'=>"New Hampshire", 'NJ'=>"New Jersey", 'NM'=>"New Mexico", 'NY'=>"New York", 'NC'=>"North Carolina", 'ND'=>"North Dakota", 'OH'=>"Ohio", 'OK'=>"Oklahoma", 'OR'=>"Oregon", 'PA'=>"Pennsylvania", 'RI'=>"Rhode Island", 'SC'=>"South Carolina", 'SD'=>"South Dakota", 'TN'=>"Tennessee", 'TX'=>"Texas", 'UT'=>"Utah", 'VT'=>"Vermont", 'VA'=>"Virginia", 'WA'=>"Washington", 'WV'=>"West Virginia", 'WI'=>"Wisconsin", 'WY'=>"Wyoming");
     
    if($abbr = array_search($stateName, $states)) 
    { 
        return $abbr; 
    } 
    return $stateName; 
}

?>