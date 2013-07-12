
<?php get_header(); ?>
<?php get_sidebar(); ?>
<div id="content" class="column">
	<div class="main">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<h2 id="post-<?php the_ID(); ?>" class="pagetitle"><?php the_title(); ?></h2>
				<?php if (get_post_meta($post->ID, 'header_graphic', TRUE)) { ?>
				<div class="splash"><img src="<?php echo get_post_meta($post->ID, 'header_graphic', TRUE); ?>" width="432" /></div>
				<?php } ?>

				<div class="entrymeta">
					<?php if (get_post_meta($post->ID, 'subhead', TRUE)) { ?>
						<h3><?php echo get_post_meta($post->ID, 'subhead', TRUE); ?></h3>
					<?php } elseif (!is_page()) { ?>
						<h3><?php the_time('F jS, Y') ?></h3>
					<?php } ?>
				</div>

				<div class="entry">
					<div class="post">
					<?php the_content(''); ?>

					</div>
					<p><strong>Posted by: <?php the_author_link(); ?></strong></p>
					<?php wp_link_pages(); ?>

					<?php edit_post_link('&raquo; Edit this entry', '<p>', '</p>'); ?>
					
					<p class="postmetadata"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">Permalink</a> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
				</div>

			<?php endwhile; else: ?>

				<h2 class="pagetitle"><?php _e('Sorry, no posts matched your criteria, please try and search again.'); ?></h2>

				<div class="entry2">&nbsp;<br/><br/><br/><br/></div>

			<?php endif; ?>

			<div class="navigation">
				<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>
			</div>
	</div> <!-- /main -->
</div> <!-- /content -->
<?php get_footer(); ?>
