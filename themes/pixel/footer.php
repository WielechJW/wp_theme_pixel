<?php
/**
 * The template for displaying the footer.
 *
 * @package pixel
 */

$site_name        = get_bloginfo( 'name' );
$site_description = get_bloginfo( 'description' );
$contact_page     = get_page_by_path( 'kontakt' );
$contact_url      = $contact_page instanceof WP_Post ? get_permalink( $contact_page ) : home_url( '/kontakt' );
$admin_email      = sanitize_email( (string) get_option( 'admin_email' ) );

?>
	<footer class="site-footer">
		<div class="site-footer__inner">
			<div class="site-footer__brand">
				<?php if ( has_custom_logo() ) : ?>
					<div class="site-footer__logo">
						<?php echo wp_kses_post( get_custom_logo() ); ?>
					</div>
				<?php endif; ?>

				<h2 class="site-footer__brand-name"><?php echo esc_html( $site_name ); ?></h2>

				<?php if ( '' !== $site_description ) : ?>
					<p class="site-footer__brand-description"><?php echo esc_html( $site_description ); ?></p>
				<?php else : ?>
					<p class="site-footer__brand-description">
						<?php esc_html_e( 'Projektujemy i realizujemy dopracowane wydruki 3D dla klientów indywidualnych i firm.', 'pixel' ); ?>
					</p>
				<?php endif; ?>

				<a class="site-footer__cta" href="<?php echo esc_url( $contact_url ); ?>">
					<?php esc_html_e( 'Napisz i wyceń projekt', 'pixel' ); ?>
				</a>
			</div>

			<div class="site-footer__column">
				<h3 class="site-footer__heading"><?php esc_html_e( 'Na skróty', 'pixel' ); ?></h3>
				<?php if ( has_nav_menu( 'primary' ) ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'menu_class'     => 'site-footer__menu',
							'container'      => false,
							'depth'          => 1,
						)
					);
					?>
				<?php else : ?>
					<ul class="site-footer__menu">
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Strona główna', 'pixel' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/#realizacje' ) ); ?>"><?php esc_html_e( 'Realizacje', 'pixel' ); ?></a></li>
						<li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Kontakt', 'pixel' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div class="site-footer__column">
				<h3 class="site-footer__heading"><?php esc_html_e( 'Kontakt', 'pixel' ); ?></h3>
				<ul class="site-footer__contact-list">
					<?php if ( '' !== $admin_email ) : ?>
						<li>
							<span class="site-footer__label"><?php esc_html_e( 'E-mail:', 'pixel' ); ?></span>
							<a href="<?php echo esc_url( 'mailto:' . $admin_email ); ?>"><?php echo esc_html( $admin_email ); ?></a>
						</li>
					<?php endif; ?>
					<li>
						<span class="site-footer__label"><?php esc_html_e( 'Formularz:', 'pixel' ); ?></span>
						<a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Przejdź do kontaktu', 'pixel' ); ?></a>
					</li>
				</ul>
			</div>
		</div>

		<div class="site-footer__bottom">
			<span class="site-footer__copyright">&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?></span>
			<span class="site-footer__meta"><?php esc_html_e( 'Druk 3D · Prototypy · Personalizacja', 'pixel' ); ?></span>
		</div>
	</footer>
</div>

<?php wp_footer(); ?>

</body>
</html>
