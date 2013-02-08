
<!-- begin left sidebar -->

<div id="left" class="column">
<?php if (is_frontpage()) { ?>
	<div class="sidebar">
		<ul>
			<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar('Front Left Navigation') ) : else : ?>

			<?php wp_list_pages('sort_column=menu_order,post_title&title_li='); ?>
			
			<?php endif; ?>
		</ul>
	</div> <!-- /sidebar front -->
	<?php } else { ?>
	<div class="sidebar">
		<ul>
			<li>
				<ul class="menu">
					<?php wswwpx_fold_page_list('sort_column=menu_order,post_title&title_li=&exclude=661,632,670,673,1786'); ?>
				</ul>
			</li>
		</ul>
	</div> <!-- /sidebar inside -->
	<?php } ?>
</div> <!-- /left -->

<!-- begin right sidebar -->

<div id="right" class="column">
<?php if (is_frontpage()) { ?>
	<div class="sidebar">
		<ul>
			<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar('Front Right Sidebar') ) : else : ?>

			<li><h2><?php _e('Last Entries'); ?></h2>
				<ul><?php get_archives('postbypost', '10', 'custom', '<li>', '</li>'); ?></ul>
			</li>
			<li><h2><?php _e('Archives'); ?></h2>
				<ul><?php wp_get_archives('type=monthly'); ?></ul>
			</li>
		<?php endif; ?>
		</ul>
	</div> <!-- /sidebar front -->
<?php } else { ?>
	<div class="sidebar">
		<ul>
			<?php widget_newsletter_subscribe(); ?>
			<?php widget_join_now(); ?>
		</ul>
	</div> <!-- /sidebar inside -->
<?php } ?>
	<div id="navi_end_right"> </div>
</div> <!-- end right sidebar -->
