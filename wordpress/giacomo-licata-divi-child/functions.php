<?php
/**
 * Giacomo Licata – Divi 5 child theme.
 *
 * @package GiacomoLicata
 */

defined( 'ABSPATH' ) || exit;

define( 'GL_THEME_VERSION', '1.0.0' );
define( 'GL_THEME_DIR', get_stylesheet_directory() );
define( 'GL_THEME_URI', get_stylesheet_directory_uri() );

require_once GL_THEME_DIR . '/inc/content.php';
require_once GL_THEME_DIR . '/inc/installer.php';

/**
 * Theme setup: menus and title tag.
 */
add_action( 'after_setup_theme', function () {
	register_nav_menus(
		array(
			'gl-primary' => __( 'Giacomo Licata – Header menu', 'giacomo-licata' ),
			'gl-footer'  => __( 'Giacomo Licata – Footer menu', 'giacomo-licata' ),
		)
	);
	add_theme_support( 'title-tag' );
}, 20 );

/**
 * Styles and scripts. Loaded late (priority 20) so they override Divi.
 */
add_action( 'wp_enqueue_scripts', function () {
	$v = GL_THEME_VERSION;

	wp_enqueue_style( 'gl-slick', GL_THEME_URI . '/assets/vendor/slick/slick.css', array(), '1.8.1' );
	wp_enqueue_style( 'gl-slick-theme', GL_THEME_URI . '/assets/vendor/slick/slick-theme.css', array( 'gl-slick' ), '1.8.1' );
	wp_enqueue_style( 'gl-site', GL_THEME_URI . '/assets/css/site.css', array( 'gl-slick-theme' ), $v );

	// The original site's body background image.
	wp_add_inline_style(
		'gl-site',
		'html body.gl-site{background-image:url("' . esc_url( GL_THEME_URI . '/assets/images/black-abstract-background-wallpaper-image.jpg' ) . '");}'
	);

	wp_enqueue_script( 'gl-slick', GL_THEME_URI . '/assets/vendor/slick/slick.min.js', array( 'jquery' ), '1.8.1', true );
	wp_enqueue_script( 'gl-site', GL_THEME_URI . '/assets/js/site.js', array( 'jquery', 'gl-slick' ), $v, true );
}, 20 );

/**
 * Keep text exactly as on the original site (no curly quotes / en dashes).
 */
add_filter( 'run_wptexturize', '__return_false' );

/**
 * Body class used to scope every style rule.
 */
add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'gl-site';
	if ( is_front_page() ) {
		$classes[] = 'gl-home';
	}
	return $classes;
} );

/**
 * Meta description from the original site (only if no SEO plugin is outputting one).
 */
add_action( 'wp_head', function () {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) ) {
		return;
	}
	echo '<meta name="description" content="' . esc_attr__( 'Giacomo Licata: Professional barber services in Bristol. Explore our services, prices, and products. Visit our website for more information.', 'giacomo-licata' ) . '" />' . "\n";
	echo '<meta name="theme-color" content="#000000" />' . "\n";
}, 1 );

/**
 * The original site's <title> is simply "Giacomo Licata" on every page.
 */
add_filter( 'pre_get_document_title', function ( $title ) {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) ) {
		return $title;
	}
	return get_bloginfo( 'name' );
} );

/**
 * Menu items for header/footer: assigned menu if set, otherwise the seven site pages.
 *
 * @param string $location Menu location.
 * @return array<int, array{title:string,url:string,active:bool}>
 */
function gl_menu_items( $location ) {
	$items     = array();
	$current   = untrailingslashit( strtok( home_url( add_query_arg( array() ) ), '?' ) );
	$locations = get_nav_menu_locations();

	if ( ! empty( $locations[ $location ] ) ) {
		$menu_items = wp_get_nav_menu_items( $locations[ $location ] );
		if ( $menu_items ) {
			foreach ( $menu_items as $item ) {
				if ( (int) $item->menu_item_parent ) {
					continue;
				}
				$items[] = array(
					'title'  => $item->title,
					'url'    => $item->url,
					'active' => untrailingslashit( $item->url ) === $current
						|| ( 'page' === $item->object && is_page( (int) $item->object_id ) )
						|| ( is_front_page() && untrailingslashit( $item->url ) === untrailingslashit( home_url() ) ),
				);
			}
			return $items;
		}
	}

	foreach ( gl_page_definitions() as $slug => $page ) {
		$url     = 'home' === $slug ? home_url( '/' ) : home_url( '/' . $slug . '/' );
		$items[] = array(
			'title'  => $page['title'],
			'url'    => $url,
			'active' => 'home' === $slug ? is_front_page() : is_page( $slug ),
		);
	}
	return $items;
}

/**
 * Social links shown in the footer (filterable).
 */
function gl_social_links() {
	return apply_filters(
		'gl_social_links',
		array(
			array(
				'label' => 'Facebook',
				'url'   => 'https://www.facebook.com/p/Giacomo-Licata-Barber-Shop-100042074387343/',
				'icon'  => 'facebookicon.svg',
			),
			array(
				'label' => 'Instagram',
				'url'   => 'https://www.instagram.com/giacomolicatasbarbershop/',
				'icon'  => 'instagramiconwhite.svg',
			),
			array(
				'label' => 'Google',
				'url'   => 'https://www.google.com/search?q=giacomo+licata&rlz=1C1ASUC_enGB652GB652&oq=giacomo+licata&gs_lcrp=EgZjaHJvbWUqBggAEEUYOzIGCAAQRRg7MgYIARBFGDwyBggCEEUYPNIBCDI5MTBqMGo5qAIAsAIB&sourceid=chrome&ie=UTF-8#lrd=0x487191dd79204213:0xc3b41a8c31805052,1,,,,',
				'icon'  => 'googleiconwhite.svg',
			),
		)
	);
}

/**
 * Header logo (Divi > Theme Options logo if set, otherwise the original logo with flag).
 */
function gl_header_logo_url() {
	$custom = function_exists( 'et_get_option' ) ? et_get_option( 'divi_logo' ) : '';
	if ( $custom && false === strpos( $custom, '/Divi/images/logo.png' ) ) {
		return $custom;
	}
	return GL_THEME_URI . '/assets/images/giac-logo.png';
}
