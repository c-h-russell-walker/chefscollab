<!-- begin left sidebar -->

<div id="left" class="column">
<?php if (is_frontpage()) { ?>
	<div class="sidebar">
		<ul class="nav-cont"></ul>
		<ul>
			<?php widget_newsletter_subscribe(); ?>
			<?php widget_join_now(); ?>
		</ul>
	</div> <!-- /sidebar front -->
<?php } else { ?>
	<div class="sidebar">
		<ul class="nav-cont">
			<?php wswwpx_fold_page_list('sort_column=menu_order,post_title&title_li=&exclude=3,661,632,670,673,1786'); ?>
		</ul>
		<ul>
			<?php widget_newsletter_subscribe(); ?>
		</ul>
	</div> <!-- /sidebar inside -->
<?php } ?>
	<div id="navi_end_right"> </div>
</div> <!-- end left sidebar -->
