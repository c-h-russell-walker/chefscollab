<?php get_header(); ?>

<?php if (get_post_meta($post->ID, 'page_header_graphic', TRUE)) { ?>
	<style type="text/css">
		#header {
			background: url(<?php echo get_post_meta($post->ID, 'page_header_graphic', TRUE); ?>) no-repeat;
		}
	</style>
<?php } elseif (get_post_meta($post->post_parent, 'page_header_graphic', TRUE)) { ?>
	<style type="text/css">
		#header {
			background: url(<?php echo get_post_meta($post->post_parent, 'page_header_graphic', TRUE); ?>) no-repeat;
		}
	</style>
<?php } ?>

	<div id="content" class="column">
		<div class="main">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<h2 id="post-<?php the_ID(); ?>" class="pagetitle"><?php the_title(); ?></h2><?php if (get_post_meta($post->ID, 'header_graphic', TRUE)) { ?>

				<div class="splash">
					<img src="<?php echo get_post_meta($post->ID, 'header_graphic', TRUE); ?>" width="432" alt="" />
				</div><?php } ?>

				<div class="entrymeta">
					<?php if (get_post_meta($post->ID, 'subhead', TRUE)) { ?>

					<h3><?php echo get_post_meta($post->ID, 'subhead', TRUE); ?></h3><?php } elseif (!is_page()) { ?>

					<h3><?php the_time('F jS, Y') ?></h3><?php } ?>
				</div>

				<div class="entry">
					<div class="post">
						<?php the_content(''); ?>
					</div><?php wp_link_pages(); ?><?php edit_post_link('&raquo; Edit this page', '<p>', '</p>'); ?>
				</div>
			<?php endwhile; else: ?>

				<h2 class="pagetitle"><?php _e('Sorry, no posts matched your criteria, please try and search again.'); ?></h2>

				<div class="entry2">
					&nbsp;<br>
					<br>
					<br>
					<br>
				</div>
			<?php endif; ?>

			<div class="navigation">
				<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>
			</div>
		</div><!-- /main -->
	</div><!-- /content -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>