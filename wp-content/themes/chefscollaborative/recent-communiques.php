<li class="recent">
	<h2><a href="<?php echo get_category_link(3);?>">Publications<img src="<?php bloginfo('template_url'); ?>/images/more-white-arrow.png" class="arrow" alt="view more" /></a></h2>
	<div class="postbox">
		<ul>
		<?php query_posts('showposts=5&cat=3'); ?>
		<?php while (have_posts()) : the_post(); // start your Loop ?>
		 <li><div class="meta"><h3><a href="<?php the_permalink(); ?>" class="permalink"><?php the_title(); ?></a></h3><div class="postdate"><?php the_time('F j, Y'); ?></div></div></li>
		<?php endwhile; ?>
		<li class="last"><a class="special" href="<?php echo get_category_link(3);?>" target="_blank"><?php _e('More Publications...'); ?></a></li>
		</ul>
	</div>
</li>
