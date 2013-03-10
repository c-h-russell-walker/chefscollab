<?php
/*
Template Name: Home
*/
?>

<?php get_header(); ?>

<div id="content" class="column">
				<div class="main">
					<?php query_posts('pagename=home'); ?>
					<?php while (have_posts()) : the_post(); // start your Loop ?>
					<?php echo get_the_content(); ?>
					<?php endwhile; ?>
					<?php  // wp_list_bookmarks('categorize=0&category_name=Promos&orderby=rand&show_images=1&show_description=0&limit=1&title_li=&before=&after='); ?>
					<h2 class="invert"><a href="<?php echo get_category_link(1);?>">Fresh from the Field</a><a href="http://chefscollaborative.org/feed/" style="background-color: #c00000; color: white; position: absolute; right: 10px; font-weight: bold; font-size: 0.8em; padding: 2px;">RSS</a></h2>
					<div class="postbox">
					<ul class="blogposts">
					<?php query_posts('showposts=3&cat=1'); ?>
					<?php $x=0; while (have_posts()) : the_post(); $x++; // start your Loop ?>
					<?php if (get_post_meta($post->ID, 'splash_image', TRUE)) { ?>
						<li class="postimg"><a href="<?php the_permalink(); ?>" class="permalink"><img src="<?php echo get_post_meta($post->ID, 'splash_image', TRUE); ?>" alt="" /></a></li>
					<?php } ?>
					<li class="postmeta"><div class="meta"><h3><a href="<?php the_permalink(); ?>" class="permalink"><?php the_title(); ?></a></h3><div class="postdate"><?php the_time('F j, Y'); ?></div><?php the_excerpt(); ?></div></li>
					<?php endwhile; ?>
					</ul>
					</div>
				</div>

</div> <!-- /content -->


<?php query_posts($query_string); // we have to do this so that the sidebar's call to is_frontpage sees the right thing. ?>
<?php while (have_posts()) : the_post(); // start your Loop ?>
<?php endwhile; ?>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
