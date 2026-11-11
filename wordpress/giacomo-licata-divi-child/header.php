<?php
/**
 * Fallback site header (used when no Divi Theme Builder header is assigned).
 * Replicates the original fixed, transparent header that turns
 * dark (#242424) once the page is scrolled more than 50px.
 *
 * @package GiacomoLicata
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page-container">
	<header class="gl-header" id="gl-header">
		<div class="gl-header__bar">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gl-header__logo-link">
				<img src="<?php echo esc_url( gl_header_logo_url() ); ?>" alt="<?php esc_attr_e( 'Giacomo Licata Logo', 'giacomo-licata' ); ?>" class="gl-header__logo" />
			</a>
			<?php echo gl_render_nav( 'header' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in gl_render_nav(). ?>
		</div>
	</header>
	<div id="et-main-area">
