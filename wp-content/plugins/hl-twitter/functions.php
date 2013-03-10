<?php if(!defined('HL_TWITTER_LOADED') or !HL_TWITTER_LOADED) die('Direct script access denied.');

/**
 * Default styling for HL Twitter shortcode
 *
 * To change how HL Twitter shortcodes are shown, make a
 * function called hl_twitter_shortcode and place it in
 * your functions.php file in your current theme directory.
 *
 * @return void
 **/
function hl_twitter_shortcode_default($tweets, $num_tweets) {

	$output = '<div class="hl_twitter_shortcode"><h3>'. ( ($num_tweets == 1) ? 'Tweet' : 'Tweets' ) .'</h3>';

	if($num_tweets == 0) {
		return $output .= '<p class="hl_twitter_shortcode_empty">No tweets were found.</p></div>';
	}

	$output .= '<ul>';

	if(!empty($tweets)) {
		foreach($tweets as $tweet) {

			$tweet->timestamp = strtotime($tweet->created);

			$output .= '<li>';

			$output .= '<p class="hl_twitter_shortcode_tweet">' . hl_twitter_show_tweet($tweet->tweet) . '</p>';

			$output .= '<p class="hl_twitter_shortcode_meta">Tweeted on';
			$output .= ' <a href="http://twitter.com/' . $tweet->screen_name . '/status/' . $tweet->twitter_tweet_id . '" title="' . date_i18n('g:ia', $tweet->timestamp) . '">' . date_i18n('F j, Y', $tweet->timestamp) . '</a>';
			$output .= ' by <a href="http://twitter.com/' . $tweet->screen_name . '" title="View Twitter profile">' . $tweet->screen_name . '</a>';
			if(!empty($tweet->reply_screen_name)) {
				$output .= ' in reply to <a href="http://twitter.com/' . $tweet->reply_screen_name . '/status/' . $tweet->reply_tweet_id . '">' . $tweet->reply_screen_name . '</a>';
			}

			$output .= '</p></li>';

		}
	}

	return $output.'</ul></div>';

} // end func: hl_twitter_shortcode_default



/**
 * Add WordPress URL Rewrite rules
 *
 * @return void
 **/
function hl_twitter_add_rewrite_rules() {

	$slug = get_option(HL_TWITTER_ARCHIVES_SLUG_KEY, HL_TWITTER_ARCHIVES_DEFAULT_SLUG);

	$regex = '(/?([a-zA-Z0-9_]+|[-]{1}))?(/?([0-9]{4}))?(/?([0-9]{1,2}))?(/?([0-9]{1,2}))?/?$';
	$redirect = 'index.php?is_hl_twitter=true&hl_twitter_username=$matches[2]&hl_twitter_year=$matches[4]&hl_twitter_month=$matches[6]&hl_twitter_day=$matches[8]';

	add_rewrite_rule($slug.$regex, $redirect, 'top');
	flush_rewrite_rules(false);

} // end func: hl_twitter_add_rewrite_rules



/**
 * Add query var parameters to WordPress whitelist
 *
 * @return void
 **/
function hl_twitter_add_query_vars($query_vars) {

	$query_vars[] = 'is_hl_twitter';
	$query_vars[] = 'hl_twitter_username';
	$query_vars[] = 'hl_twitter_year';
	$query_vars[] = 'hl_twitter_month';
	$query_vars[] = 'hl_twitter_day';

	return $query_vars;

} // end func: hl_twitter_add_query_vars



/**
 * Intercept requests for Archive pages
 *
 * @return void
 **/
function hl_twitter_rewrite_parse_request() {
	global $wp;

	if(!isset($wp->query_vars['is_hl_twitter']) or $wp->query_vars['is_hl_twitter'] !== true) return;

	hl_twitter_display_archive_page(
		$wp->query_vars['hl_twitter_username'], $wp->query_vars['hl_twitter_year'],
		$wp->query_vars['hl_twitter_month'], $wp->query_vars['hl_twitter_day'],
		$_GET['page'], $_GET['s']
	);

	die();

}



/**
 * Return HL Twitter Archives root location
 *
 * @return void
 **/
function hl_twitter_get_archives_root() {
	return get_bloginfo('wpurl') . '/' . get_option(HL_TWITTER_ARCHIVES_SLUG_KEY, 'hl-twitter');
} // end func: hl_twitter_get_archives_root



/**
 * Initiate HL Twitter Import job via Cron
 *
 * @return void
 **/
function hl_twitter_init() {

	if(!isset($_GET['hl_twitter_cron']) or $_GET['hl_twitter_cron'] !== HL_TWITTER_CRON_KEY) return;

	echo '<h1>HL Twitter</h1><hr />';

	$result = hl_twitter_import();
	if($result['status'] === 'success') {
		echo '<p>Import completed successfully.</p>';
	} else {
		echo '<p>One or more errors were encountered while importing.';
	}

	echo '<ul><li>' . implode('</li><li>', $result['lines']) . '</li></ul>';

	die();


} // end func: hl_twitter_init



/**
 * Returns true if User has set up Twitter OAuth connection details
 *
 * @return void
 **/
function hl_twitter_is_oauth_verified() {
	return (get_option(HL_TWITTER_OAUTH_TOKEN) != '') ? true : false;
} // end func: hl_twitter_is_oauth_verified



/**
 * Returns a single instance of the Twitter OAuth interface
 *
 * @return void
 **/
function hl_twitter_get_api() {
	static $hl_twitter_api;

	if(!$hl_twitter_api) {

		$token = get_option(HL_TWITTER_OAUTH_TOKEN);
		$token_secret = get_option(HL_TWITTER_OAUTH_TOKEN_SECRET);
		if(empty($token) or empty($token_secret)) return false;

		try {
			$hl_twitter_api = new EpiTwitter(HL_TWITTER_OAUTH_CONSUMER_KEY, HL_TWITTER_OAUTH_CONSUMER_SECRET, $token, $token_secret);
		} catch(Exception $e) {
			return $hl_twitter_api = false;
		}

	}

	return $hl_twitter_api;

} // end func: hl_twitter_get_api



/**
 * Posts a new Tweet to twitter
 *
 * @return void
 **/
function hl_twitter_tweet($tweet) {


	$tweet = stripslashes(trim($tweet));
	if(empty($tweet) or strlen($tweet) > 140) return false;

	$api = hl_twitter_get_api();
	if(!$api) return false;

	$max_retries = 3;
	while($max_retries>=0) {

		try {
			$response = $api->post('/statuses/update.json', array('status' => $tweet));
			if($response->code == 200) return true;
		} catch(Exception $e) {}

		$max_retries--;

	}

	return false;

} // end func: hl_twitter_tweet



/**
 * Generates the string to be genereated for a post
 *
 * @return void
 **/
function hl_twitter_generate_post_tweet_text($post_id) {

	// Existing format?
	$prior_auto_tweet = get_post_meta($post_id, HL_TWITTER_AUTO_TWEET_POSTMETA, true);
	if(!empty($prior_auto_tweet)) return $prior_auto_tweet;


	// Data
	$tweet_format = get_option(HL_TWITTER_TWEET_FORMAT, HL_TWITTER_DEFAULT_TWEET_FORMAT);
	$post = get_post($post_id);


	// Title
	$post_title = get_the_title($post_id);
	if(empty($post_title)) return false;


	// Permalink
	$post_permalink = get_permalink($post_id);
	if(function_exists('wp_get_shortlink')) $post_shortlink = wp_get_shortlink($post_id);
	if(empty($post_shortlink)) $post_shortlink = $post_permalink;


	// Date
	$post_date = mysql2date(get_option('date_format'), $post->post_date);
	$post_time = mysql2date(get_option('time_format'), $post->post_date);


	// Categories
	if(strpos($tweet_format, '%categories%') !== false) {

		$categories = get_the_category($post_id);

		if(!empty($categories)) {
			$post_categories = array();
			foreach($categories as $cat) $post_categories[] = $cat->name;
			$post_categories = implode(', ', $post_categories);
		} else {
			$post_categories = '';
		}
	}


	// Tags
	if(strpos($tweet_format, '%tags%') !== false) {
		$tags = get_the_terms($post_id, 'post_tag');
		if(!empty($tags)) {
			$post_tags = array();
			foreach($tags as $tag) $post_tags[] = $tag->name;
			$post_tags = implode(', ', $post_tags);
		} else {
			$post_tags = '';
		}
	}


	// Generate
	$pairs = array(
		'%title%'      => $post_title,
		'%shortlink%'  => $post_shortlink,
		'%permalink%'  => $post_permalink,
		'%date%'       => $post_date,
		'%time%'       => $post_time,
		'%categories%' => $post_categories,
		'%tags%'       => $post_tags,
	);
	$tweet = strtr($tweet_format, $pairs);
	return $tweet;

} // end func: hl_twitter_generate_post_tweet_text



/**
 * Returns a 48x48 (max) avatar image URL and caches locally
 *
 * N.B. 2013-01-26 Removed caching of local images
 *
 * @return void
 **/
function hl_twitter_get_avatar($url) {
	return $url;
} // end func: hl_twitter_get_avatar



/**
 * Returns a tweet with all links, hashtags and usernames converted to links
 *
 * @return void
 **/
function hl_twitter_show_tweet($tweet) {
	$tweet = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\">\\2</a>", $tweet);
	$tweet = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\">\\2</a>", $tweet);
	$tweet = preg_replace("/@(\w+)/", "<a href=\"http://twitter.com/\\1\">@\\1</a>", $tweet);
	$tweet = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\">#\\1</a>", $tweet);
	return $tweet;
} // end func: hl_twitter_show_tweet



/**
 * Display widget (extracted from widget.php:widget() for use outside dynamic sidebars)
 *
 * @return void
 **/
function hl_twitter_display_widget(
	$num_tweets = 5, $twitter_user_id = false, $widget_title = 'Tweets',
	$show_avatars = true, $show_powered_by = true, $show_more_link = true,
	$before_widget = false, $after_widget = false, $before_title = false, $after_title = false, $widget_file = 'hl_twitter_widget.php'
) {
	global $wpdb;


	// Num tweets
	$num_tweets = intval($num_tweets);
	if($num_tweets <= 0 or $num_tweets > 200) $num_tweets = 5;


	// Specific user?
	$single_user = false;
	$twitter_user_id = intval($twitter_user_id);
	if($twitter_user_id > 0) {
		$user = $wpdb->get_row($wpdb->prepare('
			SELECT twitter_user_id, screen_name, name, num_friends, num_followers, num_tweets, registered, url, description, location, avatar
			FROM ' . HL_TWITTER_DB_PREFIX . 'users
			WHERE twitter_user_id=%d
			LIMIT 1
		', $twitter_user_id));
		$single_user = ($wpdb->num_rows === 1) ? true : false;
	}


	// Get tweets query
	$query_args = array(
		'per_page' => $num_tweets,
		'page'     => 1,
		'order_by' => 'created',
		'order'    => 'desc'
	);
	if($single_user) $query_args['twitteruserid'] = $twitter_user_id;
	$query = hl_twitter_build_tweets_query_object($query_args);


	// Query DB
	$tweets = $wpdb->get_results($query->sql);
	$num_tweets = $wpdb->num_rows;


	// Presentation
	if(empty($widget_title)) $widget_title = ( $single_user ? "{$user->name}'s " : '') . 'Recent Tweets';
	$show_avatars    = (bool) $show_avatars;
	$show_powered_by = (bool) $show_powered_by;
	$show_more_link  = (bool) $show_more_link;


	// Load view (look in theme first)
	$current_template_directory = get_template_directory();
	if(file_exists($current_template_directory . '/' . $widget_file)) {
		include $current_template_directory . '/' . $widget_file;
	} else {
		include HL_TWITTER_DIR . '/' . $widget_file;
	}


} // end func: hl_twitter_display_widget



/**
 * Display tweets in a post/page
 *
 * @return void
 **/
function hl_twitter_display_shortcode($atts) {
	global $wpdb;

	$sc = shortcode_atts(array(
		'num'    => '1',
		'tweet'  => false,
		'user'   => false,
		'search' => false,
		'year'   => false,
		'month'  => false,
		'day'    => false
	), $atts);

	$sc['num'] = ($sc['num'] <= 0 or $sc['num'] > 200) ? 5 : absint($sc['num']);

	$args = array(
		'per_page' => $sc['num'],
		'page'     => 1,
		'order_by' => 'created',
		'order'    => 'desc'
	);

	if($sc['tweet']  != '') $args['tweetid'] = $sc['tweet'];
	if($sc['user']   != '') $args['twitteruserid'] = $sc['user'];
	if($sc['search'] != '') $args['search'] = $sc['search'];

	if($sc['year'] >= 2000 and $sc['year'] <= date('Y')) $args['year'] = absint($sc['year']);
	if($sc['month'] >= 1 and $sc['month'] <= 12) $args['month'] = absint($sc['month']);
	if($sc['day'] >= 1 and $sc['day'] <= 31) $args['day'] = absint($sc['day']);

	$query      = hl_twitter_build_tweets_query_object($args);
	$tweets     = $wpdb->get_results($query->sql);
	$num_tweets = (int) $wpdb->num_rows;

	if(function_exists('hl_twitter_shortcode')) {
		return hl_twitter_shortcode($tweets, $num_tweets);
	}

	return hl_twitter_shortcode_default($tweets, $num_tweets);

} // end func: hl_twitter_display_shortcode



/**
 * Called by the internal WordPress Event Scheduler
 *
 * @return void
 **/
function hl_twitter_cron_handler() {
	hl_twitter_import();
} // end func: hl_twitter_cron_handler



/**
 * Add custom time intervals
 *
 * @return void
 **/
function hl_twitter_cron_schedules($schedules) {

	$schedules['hl_10mins'] = array(
		'interval' => 600,
		'display'  =>  __('every 10 minutes')
	);

	$schedules['hl_15mins'] = array(
		'interval' => 900,
		'display'  =>  __('every 15 minutes')
	);

	$schedules['hl_30mins'] = array(
		'interval' => 1800,
		'display'  =>  __('every 30 minutes')
	);

	$schedules['hl_1hr'] = array(
		'interval' => 3600,
		'display'  =>  __('every hour')
	);

	$schedules['hl_3hrs'] = array(
		'interval' => 10800,
		'display'  =>  __('every 3 hours')
	);

	$schedules['hl_12hrs'] = array(
		'interval' => 43200,
		'display'  =>  __('every 12 hours')
	);

	$schedules['hl_24hrs'] = array(
		'interval' => 86400,
		'display'  =>  __('every 24 hours')
	);

	return $schedules;

} // end func: hl_twitter_cron_schedules



/**
 * Return a query object for tweets
 *
 * @return void
 **/
function hl_twitter_build_tweets_query_object($params) {
	global $wpdb;

	$params = (object) $params;

	// SELECT, FROM, JOIN
	$query = new stdClass;
	$query->select = '
		SELECT
			t.twitter_tweet_id, t.tweet, t.lat, t.lon, t.created, t.reply_tweet_id, t.reply_screen_name, t.source,
			u.screen_name, u.name, u.avatar,
			r.tweet AS reply_tweet, r.created AS reply_created, r.twitter_user_name AS reply_name, r.twitter_user_avatar AS reply_avatar, r.source AS reply_source
	';
	$query->from = 'FROM '.HL_TWITTER_DB_PREFIX.'tweets AS t';
	$query->join = 'JOIN '.HL_TWITTER_DB_PREFIX.'users AS u ON t.twitter_user_id = u.twitter_user_id';
	$query->join .= ' LEFT JOIN '.HL_TWITTER_DB_PREFIX.'replies AS r ON t.reply_tweet_id = r.twitter_tweet_id';


	// WHERE
	$where_conditions = array();
	if($params->day>=1 and $params->day<=31) $where_conditions['day'] = 'DAY(t.created)='.absint($params->day);
	if($params->month>=1 and $params->month<=12) $where_conditions['month'] = 'MONTH(t.created)='.absint($params->month);
	if($params->year>=2000 and $params->year<=date('Y')) $where_conditions['year'] = 'YEAR(t.created)='.absint($params->year);

	if($params->twitteruserid>0) $where_conditions['twitteruserid'] = 't.twitter_user_id='.absint($params->twitteruserid);
	if($params->tweetid!='') $where_conditions['tweetid'] = $wpdb->prepare('t.twitter_tweet_id=%s',$params->tweetid);
	if($params->search!='') $where_conditions['search'] = $wpdb->prepare('MATCH(t.tweet) AGAINST(%s)',$params->search);

	$query->where = '';
	if(!empty($where_conditions)) $query->where = 'WHERE ('.implode(') AND (', $where_conditions).')';


	// ORDER
	$query->order = 'ORDER BY ';
	if($params->search) {
		$params->order_by = 'search';
		$params->order = 'desc';
	}
	if($params->order_by=='search' and !$params->search) $params->order_by = false;
	switch($params->order_by) {
		case 'user':
			$query->order .= 'u.screen_name';
			break;
		case 'search':
			$query->order .= $where_conditions['search'];
			break;
		case 'created':
		default:
			$query->order .= 't.created';
	}
	$query->order .= (strtolower($params->order)=='desc')?' DESC':' ASC';


	// LIMIT
	$limit = ($params->per_page>0 and $params->per_page<200) ? absint($params->per_page) : 20;
	$page = ($params->page>0) ? absint($params->page) : 1;
	$offset = ($page-1) * $limit;
	$query->limit = 'LIMIT '.$offset.', '.$limit;


	// Output SQL
	$query->sql = $query->select.' '.$query->from.' '.$query->join.' '.$query->where.' '.$query->order.' '.$query->limit;
	return $query;

} // end func: hl_twitter_build_tweets_query_object



/**
 * On install...
 *
 * @return void
 **/
function hl_twitter_install() {
	global $wpdb, $wp_rewrite, $table_prefix;

	$_SESSION['hl_twitter_just_installed'] = true;


	// Add hooks
	hl_twitter_add_rewrite_rules();
	wp_schedule_event(time(), 'hl_1hr', HL_TWITTER_SCHEDULED_EVENT_ACTION); # Add cron event handler
	update_option(HL_TWITTER_UPDATE_FREQUENCY, 'hl_1hr');


	// Replies table
	$sql = "
		CREATE TABLE `" . $table_prefix . HL_TWITTER_DB_PREFIX . "replies` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`twitter_tweet_id` bigint(20) unsigned DEFAULT NULL,
			`twitter_user_id` int(11) DEFAULT NULL,
			`twitter_user_name` varchar(40) DEFAULT NULL,
			`twitter_user_screen_name` varchar(40) DEFAULT NULL,
			`twitter_user_url` varchar(255) DEFAULT NULL,
			`twitter_user_avatar` varchar(255) DEFAULT NULL,
			`tweet` varchar(160) DEFAULT NULL,
			`lat` double DEFAULT NULL,
			`lon` double DEFAULT NULL,
			`created` datetime DEFAULT NULL,
			`reply_tweet_id` bigint(20) unsigned DEFAULT NULL,
			`reply_user_id` int(11) DEFAULT NULL,
			`reply_screen_name` varchar(40) DEFAULT NULL,
			`source` varchar(40) DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `twitter_tweet_id` (`twitter_tweet_id`),
			FULLTEXT KEY `tweet` (`tweet`)
		) ENGINE=MyISAM;
	";
	$wpdb->query($sql);


	// Tweets table
	$sql = "
		CREATE TABLE `" . $table_prefix . HL_TWITTER_DB_PREFIX . "tweets` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`twitter_tweet_id` bigint(20) unsigned DEFAULT NULL,
			`twitter_user_id` int(11) DEFAULT NULL,
			`tweet` varchar(160) DEFAULT NULL,
			`lat` double DEFAULT NULL,
			`lon` double DEFAULT NULL,
			`created` datetime DEFAULT NULL,
			`reply_tweet_id` bigint(20) unsigned DEFAULT NULL,
			`reply_user_id` int(11) DEFAULT NULL,
			`reply_screen_name` varchar(40) DEFAULT NULL,
			`source` varchar(40) DEFAULT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `twitter_tweet_id` (`twitter_tweet_id`),
			KEY `twitter_user_id` (`twitter_user_id`),
			FULLTEXT KEY `tweet` (`tweet`)
		) ENGINE=MyISAM;
	";
	$wpdb->query($sql);


	// Users table
	$sql = "
		CREATE TABLE `" . $table_prefix . HL_TWITTER_DB_PREFIX . "users` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`twitter_user_id` int(11) DEFAULT NULL,
			`screen_name` varchar(40) DEFAULT NULL,
			`name` varchar(40) DEFAULT NULL,
			`num_friends` int(11) DEFAULT NULL,
			`num_followers` int(11) DEFAULT NULL,
			`num_tweets` int(11) DEFAULT NULL,
			`registered` datetime DEFAULT NULL,
			`url` varchar(255) DEFAULT NULL,
			`description` varchar(255) DEFAULT NULL,
			`location` varchar(40) DEFAULT NULL,
			`avatar` varchar(255) DEFAULT NULL,
			`created` datetime DEFAULT NULL,
			`last_updated` datetime DEFAULT NULL,
			`pull_in_replies` tinyint(1) DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `twitter_user_id` (`twitter_user_id`)
		) ENGINE=MyISAM;
	";
	$wpdb->query($sql);

} // end func: hl_twitter_install



/**
 * On uninstall...
 *
 * @return void
 **/
function hl_twitter_uninstall() {
	# Leave tables alone, just in case
	wp_clear_scheduled_hook(HL_TWITTER_SCHEDULED_EVENT_ACTION); # Remove cron
} // end func: hl_twitter_uninstall



### COMMON FUNCTIONS ########################


/**
 * Display time ago, seconds, minutes etc
 *
 * @return void
 **/
if(!function_exists('hl_time_ago')):
	function hl_time_ago($timestamp, $now=false) {
		if(!$now) $now = date_i18n('U');
		$then = (is_integer($timestamp))?$timestamp:strtotime($timestamp);
		$seconds = abs($now-$then);
		if($seconds<60) return $seconds.' '.hl_plural($seconds,'second');
		$minutes = round($seconds/60);
		if($minutes<60) return $minutes.' '.hl_plural($minutes,'minute');
		$hours = round($seconds/3600);
		if($hours<24) return $hours.' '.hl_plural($hours,'hour');
		$days = round($seconds/86400);
		if($days<7) return $days.' '.hl_plural($days,'day');
		$weeks = round($seconds/604800);
		if($weeks<=4) return $weeks.' '.hl_plural($weeks,'week');
		$months = round($seconds/2613600);
		if($months<=12) return $months.' '.hl_plural($months,'month');
		$years = round($seconds/31557600);
		return $years.' '.hl_plural($years,'year');
	} // end func: hl_time_ago
endif;



/**
 * Pluralise a word if necessary
 *
 * @return void
 **/
if(!function_exists('hl_plural')):
	function hl_plural($num, $single, $plural = false) {
		$num = intval($num);
		if($num==1) return $single;
		if($plural) return $plural;
		return $single.'s';
	} // end func: hl_plural
endif;



/**
 * Calculates percent, divbyzero check
 *
 * @return void
 **/
if(!function_exists('hl_percent')) {
	function hl_percent($numerator, $denominator=100) {
		if($denominator==0) return 0;
		return round($numerator/$denominator*100);
	} // end func: hl_percent
}



/**
 * print_r() wrapped in <pre> tags
 *
 * @return void
 **/
if(!function_exists('hl_print_r')) {
	function hl_print_r() {
		echo '<pre>';
		foreach(func_get_args() as $arg) {
			print_r($arg);
		}
		echo '</pre>';
	} // end func: hl_print_r
}



/**
 * var_dump() wrapped in <pre> tags
 *
 * @return void
 **/
if(!function_exists('hl_var_dump')) {
	function hl_var_dump() {
		echo '<pre>';
		foreach(func_get_args() as $arg) {
			var_dump($arg);
		}
		echo '</pre>';
	} // end func: hl_var_dump
}



/**
 * Escapes output via htmlspecialchars
 *
 * @return void
 **/
if(!function_exists('hl_e')) {
	function hl_e($str) {
		return htmlspecialchars($str);
	} // end func: hl_e
}



/**
 * Converts a string to a URL slug like format
 *
 * N.B. Does not test for uniqueness
 *
 * @return void
 **/
if(!function_exists('hl_slugify')) {
	function hl_slugify($str) {
		$str = strtolower(trim($str, '-'));
		$str = preg_replace('~[^\\pL\d]+~u', '-', $str);
		$str = preg_replace('~[^-\w]+~', '', $str);
		return $str;
	} // end func: hl_slugify
}
