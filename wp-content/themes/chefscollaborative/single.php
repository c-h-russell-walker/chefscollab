<?php get_header(); ?>
<style type="text/css">
	#header {
		
	}
</style>
<div id="content" class="column">
	<div class="main">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<div class="navigation">
					<div class="alignleft"><?php previous_post_link('&laquo; %link', '%title', TRUE); ?></div>
					<div class="alignright"><?php next_post_link('%link &raquo;', '%title', TRUE); ?> </div>
				</div>

			<h2 id="post-<?php the_ID(); ?>" class="pagetitle"><?php the_title(); ?></h2><?php if (get_post_meta($post->ID, 'header_graphic', TRUE)) { ?>

			<div class="splash">
				<img src="<?php echo get_post_meta($post->ID, 'header_graphic', TRUE); ?>" width="432" alt="" />
			</div><?php } ?>

			<div class="entrymeta">
				<?php if (get_post_meta($post->ID, 'subhead', TRUE)) { ?>

				<h3><?php echo get_post_meta($post->ID, 'subhead', TRUE); ?></h3><?php } elseif (!is_page()) { ?>

				<h3><?php the_time('F jS, Y') ?>, <?php the_time() ?></h3><?php } ?>
			</div>

			<div class="entry">
				<div class="post">
					<?php the_content(''); ?>
				</div>
				<p><strong>Posted by: <?php the_author_link(); ?></strong></p>
				<?php wp_link_pages(); ?><?php edit_post_link('Edit this entry &raquo;', '<p>', '</p>'); ?>
		
				<?php comments_template(); ?>
			</div><!--
					<?php trackback_rdf(); ?>
				-->
		<?php endwhile; else: ?>

			<h2 class="pagetitle"><?php _e('Sorry, no posts matched your criteria, please try and search again.'); ?></h2>

			<div class="entry2">
				&nbsp;<br>
				<br>
				<br>
				<br>
				<br>
			</div>
		<?php endif; ?>
	</div><!-- /main -->
</div><!-- /content -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
