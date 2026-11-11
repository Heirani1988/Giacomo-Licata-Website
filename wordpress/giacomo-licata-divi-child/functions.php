<?php
/**
 * Giacomo Licata – Divi 5 child theme.
 *
 * @package GiacomoLicata
 */

defined( 'ABSPATH' ) || exit;

define( 'GL_THEME_VERSION', '2.0.0' );
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

	wp_enqueue_style( 'gl-site', GL_THEME_URI . '/assets/css/site.css', array(), $v );

	// The original site's body background image.
	wp_add_inline_style(
		'gl-site',
		'html body.gl-site{background-image:url("' . esc_url( GL_THEME_URI . '/assets/images/black-abstract-background-wallpaper-image.jpg' ) . '");}'
	);

	wp_enqueue_script( 'gl-site', GL_THEME_URI . '/assets/js/site.js', array(), $v, true );
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
 * Render the header or footer navigation.
 *
 * @param string $variant "header" or "footer".
 */
function gl_render_nav( $variant = 'header' ) {
	$footer   = 'footer' === $variant;
	$location = $footer ? 'gl-footer' : 'gl-primary';
	$html     = '<nav class="' . ( $footer ? 'gl-footer__nav' : 'gl-header__nav' ) . '" aria-label="' . esc_attr( $footer ? __( 'Footer menu', 'giacomo-licata' ) : __( 'Main menu', 'giacomo-licata' ) ) . '">';
	foreach ( gl_menu_items( $location ) as $item ) {
		$class = $footer ? 'gl-footer-link' : 'gl-nav-link' . ( $item['active'] ? ' active' : '' );
		$html .= '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '</a>';
	}
	return $html . '</nav>';
}

/**
 * [gl_nav type="header|footer"] – used inside the Divi Theme Builder header/footer
 * so the menu stays editable from Appearance > Menus and shows the active page.
 */
add_shortcode( 'gl_nav', function ( $atts ) {
	$atts = shortcode_atts( array( 'type' => 'header' ), $atts, 'gl_nav' );
	return gl_render_nav( 'footer' === $atts['type'] ? 'footer' : 'header' );
} );

/**
 * Make sure [gl_*] shortcodes run inside Divi 5 modules, whatever module they are in.
 */
add_filter( 'render_block', function ( $html, $block ) {
	if ( ! empty( $block['blockName'] ) && 0 === strpos( $block['blockName'], 'divi/' ) && false !== strpos( $html, '[gl_' ) ) {
		$html = do_shortcode( $html );
	}
	return $html;
}, 10, 2 );

/**
 * News lightbox container (output on every page, independent of header/footer source).
 */
add_action( 'wp_footer', function () {
	?>
	<div class="gl-modal" id="gl-modal" aria-hidden="true">
		<div class="gl-modal__content">
			<img class="gl-modal__image" src="" alt="<?php esc_attr_e( 'Enlarged', 'giacomo-licata' ); ?>" />
			<button type="button" class="gl-modal__close"><?php esc_html_e( 'Close', 'giacomo-licata' ); ?></button>
		</div>
	</div>
	<?php
}, 5 );

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
