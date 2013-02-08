<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">

<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>

<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="generator" content="WordPress" /> <!-- leave this for stats -->

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="shortcut icon" type="image/ico" href="<?php bloginfo('template_url'); ?>/favicon.ico" />
<script src="<?php bloginfo('template_url'); ?>/js/chefs.js" type="text/javascript"></script> 

<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php wp_get_archives('type=monthly&format=link'); ?>
<?php //comments_popup_script(); // off by default ?>
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

<div id="wrapper">
