<li class="upcoming invert"><h2><img src="<?php bloginfo('template_url'); ?>/images/eggplant-with-bg.jpg" alt="" /><a href="http://upcoming.yahoo.com/group/3475/" target="_blank"><?php _e('Upcoming'); ?><img src="<?php bloginfo('template_url'); ?>/images/more-white-arrow.png" class="arrow" alt="view more" /></a></h2>
	<ul>
	<?php
	//
	// The upcoming.org event feed:
	$url = "http://upcoming.yahoo.com/syndicate/v2/group/3475/de15d0a206";

	// we have to fix Upcoming.org's feed to order by event date, not event posting date. This
	// compare function, combined with usort, will do just that.
	$compare = create_function('$a,$b','$x = mktime(substr($a["xcal"]["dtstart"], 9, 2), substr($a["xcal"]["dtstart"], 11, 2), 0, substr($a["xcal"]["dtstart"], 4, 2), substr($a["xcal"]["dtstart"], 6, 2), substr($a["xcal"]["dtstart"], 0, 4)); $y = mktime(substr($b["xcal"]["dtstart"], 9, 2), substr($b["xcal"]["dtstart"], 11, 2), 0, substr($b["xcal"]["dtstart"], 4, 2), substr($b["xcal"]["dtstart"], 6, 2), substr($b["xcal"]["dtstart"], 0, 4)); if ($x == $y) {return 0;} else {return $x < $y ? -1 : 1;}');

	require_once(ABSPATH . WPINC . '/rss-functions.php');


	if ($url) {
	    $rss = fetch_rss( $url );
	    usort($rss->items, $compare);

	    $count = 0;
	    foreach ($rss->items as $item) {
			// Title will probably be in the form of "Apr 2, 2007: Event Name"
			// So we cut out the date part...
	        $title = strstr($item['title'], ': ') ? substr(strstr($item['title'], ': '), 2) : $item['title'];
	        $link = $item['link'];
	    	$description = $item['description'];
	        $date_start = $item['xcal']['dtstart'];
	        // $date_end = $item['xcal']['dtend'];
			$nix_date_start = mktime(substr($date_start, 9, 2), substr($date_start, 11, 2), 0, substr($date_start, 4, 2), substr($date_start, 6, 2), substr($date_start, 0, 4));
	        $pretty_date_start = date("M. j, Y g.ia", $nix_date_start);
			$city = $item['xcal']['x-calconnect-venue_adr_x-calconnect-city'];
			if (function_exists('stateAbbrev')) {
				$region = stateAbbrev($item['xcal']['x-calconnect-venue_adr_x-calconnect-region']);
			} else {
				$region = $item['xcal']['x-calconnect-venue_adr_x-calconnect-region'];
			}
			$pretty_description = substr(strip_tags($description), 0, 150) . " ...";
		
	?>
	<li><p class="event"><span class="event_date"><?php echo $pretty_date_start; ?></span><br />
		<span class="event_city"><?php echo $city; ?>, <?php echo $region; ?></span><br />
		<span class="event_title"><a href="<?php echo $link; ?>" target="_blank"><?php echo $title; ?></a></span>
		</p></li>
	<?php
		// quit after 4 items
		if (++$count == 4) {
			break;
		}
	    }
	 }
	?>
	<li class="last"><a class="special" href="http://upcoming.yahoo.com/group/3475/" target="_blank"><?php _e('More Events...'); ?></a></li>
	</ul>
</li>
