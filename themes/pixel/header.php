<?php
/**
 * The header for our theme.
 *
 * @package pixel
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="site">
	<header class="site-header">
		<div class="site-branding">
			<?php the_custom_logo(); ?>
			<div class="site-identity">
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<p class="site-tagline"><?php bloginfo( 'description' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'pixel' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</header>
