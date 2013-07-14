
<!-- begin footer -->

	</div> <!-- /wrapper -->

	<div id="footer">
		<div class="cont">Copyright &copy; <?php echo('2007 - '.date('Y')) ?> by
			<?php wp_nav_menu(array('menu'=>'Bottom Nav')); ?>
		</div>
		<?php do_action('wp_footer', ''); ?>
		<div style="clear: both"></div>
	</div> <!-- /footer -->

</div> <!-- /page-wrapper -->
</div> <!-- /page -->

<script src="<?php bloginfo('template_url'); ?>/js/chefs.min.js?ver=071413" type="text/javascript"></script>

</body>
</html>
