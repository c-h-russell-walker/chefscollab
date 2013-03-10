<?php if(!defined('HL_TWITTER_LOADED') or !HL_TWITTER_LOADED) die('Direct script access denied.');

/**
 * Updates all users and their tweets
 *
 * @param int User ID
 * @return array [result, messages]
 **/
function hl_twitter_import($user_id = false) {
	global $wpdb;

	// Connect to API
	$api = hl_twitter_get_api();
	if(!$api) {
		return array(
			'status' => 'error',
			'lines'  => array(
				'Could not connect to Twitter. Please make sure you have linked this plugin to your Twitter account.'
			)
		);
	}


	// Init
	$timer_start = microtime(true);
	$api->setTimeout(60, 60);
	set_time_limit(60);
	date_default_timezone_set(get_option('timezone_string','UTC'));


	// Build query for users
	$sql = '
		SELECT
			u.twitter_user_id,
			u.screen_name,
			u.pull_in_replies,
			(
				SELECT t.twitter_tweet_id
				FROM ' . HL_TWITTER_DB_PREFIX . 'tweets AS t
				WHERE t.twitter_user_id = u.twitter_user_id
				ORDER BY t.created DESC LIMIT 1
			) AS last_tweet_id
		FROM ' . HL_TWITTER_DB_PREFIX . 'users AS u
	';
	$user_id = intval($user_id);
	if($user_id > 0) $sql .= ' WHERE u.twitter_user_id= ' . $user_id;


	// Get users from DB
	$users = $wpdb->get_results($sql);
	$num_users = $wpdb->num_rows;
	if($num_users <= 0) {
		return array(
			'status' => 'error',
			'lines'  => array(
				'No users were found in the database. Please add at least one user to track and try again.'
			)
		);
	}


	// Import
	$lines = array();
	$lines[] = "Importing Twitter data for <strong>$num_users</strong> user(s)";
	$lines = array_merge($lines, hl_twitter_import_user_data($api, $users));
	$lines = array_merge($lines, hl_twitter_import_tweets($api, $users));
	$lines[] = 'Import completed in ' . round(microtime(true) - $timer_start, 3) . ' seconds';

	return array(
		'result' => 'success',
		'lines'  => $lines
	);

} // end func: hl_twitter_import



/**
 * Inserts any new tweets
 *
 * @return void
 **/
function hl_twitter_import_tweets($api, $users) {
	global $wpdb;

	$api->useAsynchronous();

	$lines = array();
	$final_data = array();
	$all_responses_served = false;
	$max_requests = 5; // try each request a max 5 times


	// Make array of users, and users who have not been downloaded yet
	$users_outstanding = array();
	foreach($users as $user) $users_outstanding[$user->twitter_user_id] = $user;
	$users = $users_outstanding;


	// Get tweets
	while(!$all_responses_served) {
		if($max_requests <= 0) break; // guard max tries

		$requests = array();


		// Add request for each user still outstanding
		foreach($users_outstanding as $user_id => $user) {

			$args = array(
				'user_id'     => $user->twitter_user_id,
				'include_rts' => true,
				'count'       => HL_TWITTER_API_TWEETS_PER_PAGE,
				'page'        => 1
			);

			if(!empty($user->last_tweet_id)) {
				$args['since_id'] = $user->last_tweet_id;
			}

			$requests[$user_id] = $api->get('/statuses/user_timeline.json', $args);

		}

		// Process response
		foreach($requests as $user_id=>$response) {
			try {
				if($response->code === 200) {
					$final_data[$user_id] = $response->response;
					unset($users_outstanding[$user_id]);
				}
			} catch(Exception $e) {
				// This is horrible, but our loop catches it
			}
		}

		if(empty($users_outstanding)) {
			$all_responses_served = true;
		}

		$max_requests--;

	} // end while: responses


	// Add warning if users still outstanding
	if(!$all_responses_served) {
		$lines[] = 'Warning: 1 or more requests failed to complete with Twitter. Please try again later.';
	}


	// Process new tweets
	$tweet_replies = array();
	$sql_inserts = array();

	foreach($final_data as $user_id => $tweets) {

		$num_tweets = count($tweets);

		if($num_tweets === 0) {
			$lines[] = 'No new tweets found for: <strong>'.$users[$user_id]->screen_name.'</strong>';
		}

		foreach($tweets as $raw_tweet) {

			if($users[$user_id]->pull_in_replies == 1 and $raw_tweet['in_reply_to_status_id'] != '') {
				$tweet_replies[$raw_tweet['in_reply_to_status_id_str']] = $raw_tweet['in_reply_to_status_id_str'];
			}

			$sql_inserts[] = $wpdb->prepare(
				'(
					%s, %d, %s,
					%s, %s, %s,
					%d, %s, %f,
					%f
				)',
				$raw_tweet['id_str'], $raw_tweet['user']['id'], $raw_tweet['text'],
				date_i18n('Y-m-d H:i:s', strtotime($raw_tweet['created_at'])), strip_tags($raw_tweet['source']), $raw_tweet['in_reply_to_status_id_str'],
				$raw_tweet['in_reply_to_user_id'], $raw_tweet['in_reply_to_screen_name'], $raw_tweet['geo']['coordinates'][0],
				$raw_tweet['geo']['coordinates'][1]
			);

		}

		$lines[] = "$num_tweets new tweet(s) found for: <strong>{$users[$user_id]->screen_name}</strong>";

	} // end foreach: final_data


	// No new tweets?
	$sql_count = count($sql_inserts);
	if($sql_count === 0) {
		$lines[] = "No new tweets found to insert";
		return $lines;
	}


	// Insert tweets
	$sql = '
		INSERT IGNORE INTO ' . HL_TWITTER_DB_PREFIX . 'tweets
		(twitter_tweet_id, twitter_user_id, tweet, created, source, reply_tweet_id, reply_user_id, reply_screen_name, lat, lon)
		VALUES '.implode(', ',$sql_inserts);
	$wpdb->query($sql);

	$new_tweets = ($sql_count === 0) ? 0 : $wpdb->rows_affected;
	$lines[] = "$new_tweets tweet(s) were added to your database.";


	// Import replies (if any)
	if(!empty($tweet_replies)) {
		$result = hl_twitter_import_tweet_replies($api, $tweet_replies);
		if($result) $lines = array_merge($lines, $result);
	}

	return $lines;

} // end func: hl_twitter_import_tweets



/**
 * Pulls in tweets that have been replied to by designated users
 *
 * @return void
 **/
function hl_twitter_import_tweet_replies($api, $tweet_reply_ids) {
	global $wpdb;

	if(empty($tweet_reply_ids)) return false;

	$lines = array();
	$tweets_outstanding = $tweet_reply_ids;
	$all_responses_served = false;
	$max_requests = 3; // try each request a max 3 times
	$final_data = array();


	// Download replies
	while(!$all_responses_served) {
		if($max_requests <= 0) break;
		$requests = array();

		foreach($tweets_outstanding as $tweet_key => $tweet_id) {
			$requests[$tweet_key] = $api->get("/statuses/show/$tweet_id.json");
		}

		foreach($requests as $tweet_key => $response) {
			try {
				if($response->code == 200) {
					$final_data[$tweet_key] = $response->response;
					unset($tweets_outstanding[$tweet_key]);
				}
			} catch(Exception $e) {
				// Allow loop to proceed and try again
			}
		}

		if(empty($tweets_outstanding)) {
			$all_responses_served = true;
		}

		$max_requests--;

	} // end while: $all_responses_served


	// Failed to import all tweets
	if(!$all_responses_served) {
		$lines[] = 'Warning: failed to retrieve 1 or more tweets from Twitter.';
	}


	// No replies found
	if(empty($final_data)) return array('No replied to tweets were returned by Twitter.');


	// Prepare Inserts
	$sql_inserts = array();
	foreach($final_data as $raw_tweet) {

		$sql_inserts[] = $wpdb->prepare(
			'(
				%s, %d, %s,
				%s, %s, %s,
				%s, %s, %s,
				%s, %d, %s,
				%f, %f
			)',
			$raw_tweet['id_str'], $raw_tweet['user']['id'], $raw_tweet['user']['name'],
			$raw_tweet['user']['screen_name'], $raw_tweet['user']['url'], $raw_tweet['user']['profile_image_url'],
			$raw_tweet['text'], date_i18n('Y-m-d H:i:s', strtotime($raw_tweet['created_at'])), strip_tags($raw_tweet['source']),
			$raw_tweet['in_reply_to_status_id_str'], $raw_tweet['in_reply_to_user_id'], $raw_tweet['in_reply_to_screen_name'],
			$raw_tweet['geo']['coordinates'][0], $raw_tweet['geo']['coordinates'][1]
		);

	}


	// Insert
	$sql = '
		INSERT IGNORE INTO '.HL_TWITTER_DB_PREFIX.'replies
		(twitter_tweet_id, twitter_user_id, twitter_user_name, twitter_user_screen_name, twitter_user_url, twitter_user_avatar, tweet, created, source, reply_tweet_id, reply_user_id, reply_screen_name, lat, lon)
		VALUES '.implode(', ',$sql_inserts);
	$wpdb->query($sql);


	// Return
	$new_tweets = (empty($sql_inserts)) ? 0 : $wpdb->rows_affected;
	$lines[] = "$new_tweets replied to tweet(s) were added to your database.";
	return $lines;

} // end func: hl_twitter_import_tweet_replies



/**
 * Updates profile information e.g. name, num_followers etc
 *
 * @return void
 **/
function hl_twitter_import_user_data($api, $users) {
	global $wpdb;


	$lines = array();
	$request_served = false;
	$max_requests = 3;
	$user_ids = array();
	foreach($users as $user) $user_ids[$user->twitter_user_id] = $user->twitter_user_id;


	// Download users
	while(!$request_served) {


		// If max number of retries has been hit, fail out
		if($max_requests <= 0) {
			return array('Could not retrieve user data from Twitter. Reached maximum number of retries.');
		}


		// Get users
		try {
			$user_data = $api->get('/users/lookup.json', array(
				'user_id' => implode(',', $user_ids
			)));
		} catch(Exception $e) {
			return array('An exception was thrown by Twitter API. Please try again later.');
		}

		// Response
		if($user_data->code == 200) {
			$request_served = true;
		}

		$max_requests--;

	} // end while: request_served


	// One final check to make sure we have a valid response
	if(!isset($user_data->code) or $user_data->code !== 200) {
		return array('No user data was returned by Twitter. Please try again later.');
	}


	// Save users
	$date = date_i18n('Y-m-d H:i:s');
	foreach($user_data as $user) {

		if(!array_key_exists($user->id, $user_ids)) continue;

		$wpdb->update(
			HL_TWITTER_DB_PREFIX . 'users',
			array(
				'screen_name'   => $user->screen_name,
				'name'          => $user->name,
				'num_friends'   => $user->friends_count,
				'num_followers' => $user->followers_count,
				'num_tweets'    => $user->statuses_count,
				'url'           => $user->url,
				'description'   => $user->description,
				'location'      => $user->location,
				'avatar'        => $user->profile_image_url,
				'last_updated'  => $date
			),
			array('twitter_user_id' => $user->id),
			array('%s','%s','%d','%d','%d','%s','%s','%s','%s','%s'),
			'%d'
		);

		$lines[] = "Profile information updated for: <strong>{$user->screen_name}</strong>";

	}

	return $lines;

} // end func: hl_twitter_import_user_data
