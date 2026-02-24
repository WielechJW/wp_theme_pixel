<?php
/**
 * The header for our theme.
 *
 * @package pixel
 */

$has_primary_menu = has_nav_menu( 'primary' );

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
		<div class="site-header__inner">
			<?php if ( $has_primary_menu ) : ?>
				<button
					class="menu-toggle"
					type="button"
					aria-expanded="false"
					aria-controls="site-navigation"
					aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'pixel' ); ?>"
				>
					<span class="menu-toggle__icon" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'pixel' ); ?></span>
				</button>
			<?php endif; ?>

			<div class="site-branding">
				<?php the_custom_logo(); ?>
			</div>

			<?php if ( $has_primary_menu ) : ?>
				<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'pixel' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'menu_id'        => 'primary-menu',
							'container'      => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>

		<?php if ( $has_primary_menu ) : ?>
			<button
				class="menu-overlay"
				type="button"
				hidden
				aria-label="<?php esc_attr_e( 'Close navigation menu', 'pixel' ); ?>"
			></button>
		<?php endif; ?>
	</header>
