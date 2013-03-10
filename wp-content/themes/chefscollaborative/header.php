<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">

<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>

<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="generator" content="WordPress" /> <!-- leave this for stats -->

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url'); ?>/css/jMenu.jquery.css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="shortcut icon" type="image/ico" href="<?php bloginfo('template_url'); ?>/favicon.ico" />

<script src="<?php bloginfo('template_url'); ?>/js/chefs.js" type="text/javascript"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js"></script>
<script src="<?php bloginfo('template_url'); ?>/js/jquery.jlnav.js" type="text/javascript"></script>

<script type="text/javascript">
    jQuery(function($){
        $(".nav-menu").jlnav({
			 nav_font: '12px inherit',
			 nav_width: '100%',
			 nav_padding: '0 0',
             nav_margin: '0px auto 0px auto',
             nav_shadow_rgba: '0, 0, 0, 0.4',
             nav_shadow_width: '0px 1px 2px 0px',
			 nav_text_color: 'fff',
			 nav_bgcolor: '417630',
             nav_gradient_from: '417630',
             nav_gradient_to: '417630',
             subnav_font: '14px inherit',
             subnav_bgcolor: '417630',
             subnav_text_color: 'fff',
             subnav_hover_bgcolor: '8CAF63',
             subnav_hover_gradient_from: '8CAF63',
             subnav_hover_gradient_to: '8CAF63',
             subnav_hover_text_color: '000',
             subnav_focus_bgcolor: '8CAF63',
             subnav_border_color: 'transparent'
        });
    });
</script>

<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php // wp_get_archives('type=monthly&format=link'); ?>
<?php // comments_popup_script(); // off by default ?>
<?php wp_head(); ?>

</head>

<body>

<div id="page">

<div id="skip">
	<p><a href="#content" title="Skip to site content">Skip to content</a></p>
	<p><a href="#search" title="Skip to search" accesskey="s">Skip to search - Accesskey = s</a></p>
</div> <!-- /skip -->

<div id="header">
		<div id="homelink"><a href="<?php bloginfo('url'); ?>" id="home"><i>Home</i></a></div>
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>
		<h1><a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></h1>
		<h4><?php bloginfo('description'); ?></h4>
</div> <!-- /header -->

<?php // get_navigation(); ?>
<style type="text/css">
#top-nav {
  background-color: #417630;
	height: 47px;
	text-align: center;
}
#top-nav ul {
  list-style: none;
	margin: 0;
	vertical-align: middle;
	padding: 0;
}
#top-nav ul li {
	display: inline-block;
	color: #FFF;
	margin-right: 15px;
}
#top-nav ul li a {
	color: #FFF;
	line-height: 3.333em;
  padding: 0 0.75em;
	-moz-border-radius: 5px;
 	-webkit-border-radius: 5px;
  border-radius: 5px;
}
#top-nav ul li a:hover {
	color: #000;
	text-decoration: none;
}
#top-nav .sub-nav {
	display: none;
}
#top-nav .sub-nav a {
	border: 1px solid black;
	border-bottom: none;
}
#top-nav .sub-nav a.last-nav {
	border-bottom: 1px solid black;
}
</style>

<nav id="top-nav" class="nav-menu">
	<ul>
		<?php //wswwpx_fold_page_list('sort_column=menu_order,post_title&title_li=&exclude=661,632,670,673,1786'); ?>
		<li><a href="<?php bloginfo('url'); ?>/about" >About</a>
			<ul class='sub-nav'>
				<li><a href='<?php bloginfo('url'); ?>/about/board-of-overseers/'>Board of Overseers</a></li>
				<li><a href='<?php bloginfo('url'); ?>/about/chapters/'>Chapters</a></li>
				<li><a href='<?php bloginfo('url'); ?>/about/staff/'>Staff</a></li>
				<li><a href='<?php bloginfo('url'); ?>/about/advisory-board/'>Advisory Board</a></li>
				<li><a href='<?php bloginfo('url'); ?>/about/job-openings/'>Jobs and Internships</a></li>
				<li><a href='<?php bloginfo('url'); ?>/about/friends-sponsors/'>Friends & Sponsors</a></li>
				<li><a class="last-nav" href='<?php bloginfo('url'); ?>/about/ustainability-awards/'>Sustainability Awards</a></li>
			</ul>
		</li>
		<li><a href="<?php bloginfo('url'); ?>/programs">Programs</a>
			<ul class='sub-nav'>
				<li><a href='<?php bloginfo('url'); ?>/programs/chefs-collaborative-locals/'>Local Networks</a></li>
				<li><a href='<?php bloginfo('url'); ?>/programs/cookbook/'>Cookbook</a></li>
				<li><a class="last-nav" href='<?php bloginfo('url'); ?>/programs/chef-the-sea/'>Seafood Solutions</a></li>
			</ul>
		</li>
		<li><a href="<?php bloginfo('url'); ?>/join-us">Members</a>
			<ul class='sub-nav'>
				<li><a class="last-nav" href-'http://guide.chefscollaborative.org/'>Member Search</a></li>
			</ul>
		</li>
		<li><a href="<?php bloginfo('url'); ?>/sustainable-food-summit/national-summit-<?php echo date('Y') ?>/">Sustainable Food Summit</a>
			<ul class='sub-nav'>
				<li><a href-'<?php bloginfo('url'); ?>/sustainable-food-summit/national-summit-2013/'>National Summit 2013</a></li>
				<li><a href-'<?php bloginfo('url'); ?>/sustainable-food-summit/2012-2/'>National Summit 2012</a></li>
				<li><a class="last-nav" href-'<?php bloginfo('url'); ?>/sustainable-food-summit/summit/'>National Summit 2011</a></li>
			</ul>
		</li>
		<li><a href="<?php bloginfo('url'); ?>/events/">Events</a>
			<ul class='sub-nav'>
				<li><a href-'<?php bloginfo('url'); ?>/events/'>function call get events - iterate</a></li>
				<li><a class="last-nav" href-'<?php bloginfo('url'); ?>/events/archive/'>Archive</a></li>
			</ul>
		</li>
		<li><a href="<?php bloginfo('url'); ?>/category/articles">Publications</a>
			<ul class='sub-nav'>
				<li><a href='<?php bloginfo('url'); ?>/2013/'>2013</a></li>
				<li><a href='<?php bloginfo('url'); ?>/2012/'>2012</a></li>
				<li><a href='<?php bloginfo('url'); ?>/2011/'>2011</a></li>
				<li><a href='<?php bloginfo('url'); ?>/2010/'>2010</a></li>
				<li><a href='<?php bloginfo('url'); ?>/2009/'>2009</a></li>
				<li><a href='<?php bloginfo('url'); ?>/2008/'>2008</a></li>
				<li><a class="last-nav" href='<?php bloginfo('url'); ?>/2007/'>2007</a></li>
			</ul>
		</li>
		<li><a href="<?php bloginfo('url'); ?>/category/blog">Blog</a></li>
		<li><a href="<?php bloginfo('url'); ?>/press">News &amp; Media</a>
			<ul class='sub-nav'>
				<li><a class="last-nav" href='<?php bloginfo('url'); ?>/press/media-kit/'>Media Kit</a></li>
			</ul>
		</li>
		<li><a href="<?php bloginfo('url'); ?>/contact-us">Contact Us</a></li>
	</ul>
</nav>

<div id="wrapper">
