<?php
/**
 * The template for displaying the footer.
 *
 * @package pixel
 */

?>
	<footer class="site-footer">
		<div class="site-info">
			<span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		</div>
	</footer>
</div>

<?php wp_footer(); ?>

</body>
</html>
