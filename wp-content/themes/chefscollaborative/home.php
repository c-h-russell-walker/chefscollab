<?php
/*
Template Name: Home
*/
?>

<?php get_header(); ?>
<?php get_sidebar(); ?>
<div id="content" class="column">
	<div class="main">
		<?php query_posts('pagename=home'); ?>
		<?php while (have_posts()) : the_post(); // start your Loop ?>
		<?php echo get_the_content(); ?>
		<?php endwhile; ?>
	</div>
</div> <!-- /content -->

<?php query_posts($query_string); // we have to do this so that the sidebar's call to is_frontpage sees the right thing. ?>
<?php while (have_posts()) : the_post(); // start your Loop ?>
<?php endwhile; ?>

<?php get_footer(); ?>
