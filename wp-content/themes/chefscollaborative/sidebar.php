<?php
	global $post; 
	$id = $post->ID;
?>

<!-- begin left sidebar -->
<div id="left" class="column">
	<div class="sidebar">
	<?php if (is_frontpage()) { ?>
	<ul>
		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('front-left-navigation') ) :      endif; ?>
	</ul>
	<?php } else { ?>
	<ul class="nav-cont">
		<?php wswwpx_fold_page_list('sort_column=menu_order,post_title&title_li=&exclude=3,661,632,670,673,1786', true); ?>
	</ul>
	<?php
		echo '<ul class="blog-cont">';
    	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('front-left-blog') ) :      endif;
    	echo '</ul>';
	?>
	<ul>
		<?php widget_newsletter_subscribe(); ?>
	</ul>
	<?php } ?>
	</div> <!-- /sidebar inside -->
	<div id="navi_end_right"> </div>
</div> <!-- end left sidebar -->
