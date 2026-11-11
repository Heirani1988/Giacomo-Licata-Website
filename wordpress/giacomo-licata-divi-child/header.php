<?php
/**
 * Site header – replicates the original fixed, transparent header that turns
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
			<nav class="gl-header__nav" aria-label="<?php esc_attr_e( 'Main menu', 'giacomo-licata' ); ?>">
				<?php foreach ( gl_menu_items( 'gl-primary' ) as $item ) : ?>
					<a class="gl-nav-link<?php echo $item['active'] ? ' active' : ''; ?>" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>
	</header>
	<div id="et-main-area">
