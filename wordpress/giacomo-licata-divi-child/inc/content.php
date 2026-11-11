<?php
/**
 * Page + Theme Builder content for the Giacomo Licata site, built from native
 * Divi 5 modules (section / row / column / group / heading / text / image /
 * slider / video / link). Styling lives in each module's Divi design settings,
 * so everything is editable in the Visual Builder.
 *
 * Attribute paths follow the divi5-skill reference (github.com/divilovewp/divi5-skill),
 * which documents Divi 5.11-5.16.
 *
 * Image references use {{img:filename}} (Media Library, resolved by the
 * installer) and {{theme:filename}} (theme assets).
 *
 * The only things not expressed as Divi settings (approved custom extras in
 * assets/css/site.css + assets/js/site.js) are: the fixed header that turns
 * dark on scroll, the hover overlays on the Team / News images, the current
 * page highlight in the menu, and the News lightbox. Modules that take part
 * carry the CSS classes gl-header, gl-hover-card, gl-hover-overlay, gl-nav-item.
 *
 * @package GiacomoLicata
 */

defined( 'ABSPATH' ) || exit;

/* =========================================================================
 * Core block helpers
 * ====================================================================== */

/** Divi builder version stamped on every block (the installed Divi version). */
function gl_d5_version() {
	if ( defined( 'ET_BUILDER_PRODUCT_VERSION' ) ) {
		return ET_BUILDER_PRODUCT_VERSION;
	}
	$divi = wp_get_theme( 'Divi' );
	if ( $divi->exists() && $divi->get( 'Version' ) ) {
		return $divi->get( 'Version' );
	}
	return '5.16.0';
}

/** Parsed-block array for WordPress' serialize_block(). */
function gl_d5_block( $name, array $attrs, array $inner = array() ) {
	$attrs['builderVersion'] = gl_d5_version();
	return array(
		'blockName'    => $name,
		'attrs'        => $attrs,
		'innerBlocks'  => $inner,
		'innerHTML'    => '',
		'innerContent' => array_fill( 0, count( $inner ), null ),
	);
}

/** Serialize sections into Divi 5 post_content. */
function gl_d5_serialize( array $sections ) {
	$out = '';
	foreach ( $sections as $section ) {
		$out .= serialize_block( $section );
	}
	return "<!-- wp:divi/placeholder -->\n" . $out . "\n<!-- /wp:divi/placeholder -->";
}

/**
 * Responsive value: {"desktop":{"value":…},"tablet":{"value":…},"phone":{"value":…}}.
 * Pass null to leave a breakpoint unset. Extra states (hover/sticky) via $states.
 */
function gl_v( $desktop, $tablet = null, $phone = null, array $states = array() ) {
	$out = array( 'desktop' => array( 'value' => $desktop ) + $states );
	if ( null !== $tablet ) {
		$out['tablet'] = array( 'value' => $tablet );
	}
	if ( null !== $phone ) {
		$out['phone'] = array( 'value' => $phone );
	}
	return $out;
}

/** module.meta / module.advanced.htmlAttributes. */
function gl_meta( $label, $class = '' ) {
	$m = array();
	if ( $label ) {
		$m['meta'] = array( 'adminLabel' => gl_v( $label ) );
	}
	if ( $class ) {
		$m['advanced'] = array( 'htmlAttributes' => array( 'desktop' => array( 'value' => array( 'class' => $class ) ) ) );
	}
	return $m;
}

/* ---- decoration helpers ------------------------------------------------ */

function gl_box4( $t, $r = null, $b = null, $l = null ) {
	$r = null === $r ? $t : $r;
	$b = null === $b ? $t : $b;
	$l = null === $l ? $r : $l;
	return array( 'top' => $t, 'right' => $r, 'bottom' => $b, 'left' => $l );
}

function gl_radius( $tl, $tr = null, $br = null, $bl = null ) {
	$tr = null === $tr ? $tl : $tr;
	$br = null === $br ? $tl : $br;
	$bl = null === $bl ? $tl : $bl;
	return array(
		'topLeft'     => $tl,
		'topRight'    => $tr,
		'bottomRight' => $br,
		'bottomLeft'  => $bl,
		'sync'        => ( $tl === $tr && $tr === $br && $br === $bl ) ? 'on' : 'off',
	);
}

/** Box shadow (style is required or Divi emits nothing). */
function gl_shadow( $h, $v, $blur, $spread, $color ) {
	return gl_v(
		array(
			'style'      => 'preset1',
			'horizontal' => $h,
			'vertical'   => $v,
			'blur'       => $blur,
			'spread'     => $spread,
			'position'   => 'outer',
			'color'      => $color,
		)
	);
}

function gl_text_shadow() {
	return gl_v(
		array(
			'style'      => 'preset1',
			'horizontal' => '3px',
			'vertical'   => '4px',
			'blur'       => '4.5px',
			'color'      => 'rgba(0,0,0,0.5)',
		)
	);
}

/** Font value object. lineHeight "normal" matches the original (browser default). */
function gl_font( $size, $color, $weight = '400', $align = null, array $extra = array() ) {
	$f = array(
		'size'       => $size,
		'color'      => $color,
		'weight'     => $weight,
		'lineHeight' => 'normal',
	);
	if ( $align ) {
		$f['textAlign'] = $align;
	}
	return $f + $extra;
}

/* =========================================================================
 * Module builders
 * ====================================================================== */

function gl_section( $label, array $rows, array $deco = array(), $class = '' ) {
	$module = gl_meta( $label, $class );
	if ( $deco ) {
		$module['decoration'] = $deco;
	}
	return gl_d5_block( 'divi/section', array( 'module' => $module ), $rows );
}

/** Standard content section: 1200px container, 6rem/2rem/2rem padding (7rem/2rem/1rem on phones). */
function gl_page_section( $label, array $rows, $phone_padding = true ) {
	return gl_section(
		$label,
		$rows,
		array(
			'background' => gl_v( array( 'color' => 'rgba(0,0,0,0)' ) ),
			'spacing'    => gl_v(
				array( 'padding' => gl_box4( '96px', '32px', '32px', '32px' ) ),
				null,
				$phone_padding ? array( 'padding' => gl_box4( '112px', '32px', '16px', '32px' ) ) : null
			),
		)
	);
}

/**
 * Row. $gap = column/row gap; $stack = stack columns on phones.
 *
 * @param array $columns  Column blocks.
 * @param array $opts     margin_bottom, margin_top, gap, stack, class, layout (extra layout values).
 */
function gl_row( $label, array $columns, array $opts = array() ) {
	$opts   = array_merge(
		array(
			'gap'           => '16px',
			'margin_bottom' => '0px',
			'margin_top'    => '0px',
			'stack'         => true,
			'class'         => '',
			'layout'        => array(),
			'max_width'     => '1136px',
			'deco'          => array(),
		),
		$opts
	);
	$module = gl_meta( $label, $opts['class'] );

	$module['advanced']['flexColumnStructure'] = gl_v( 'equal-columns_' . count( $columns ) );
	$module['decoration']                      = array_replace_recursive(
		array(
			'layout'  => gl_v(
				array_merge( array( 'flexWrap' => 'nowrap', 'columnGap' => $opts['gap'], 'rowGap' => $opts['gap'], 'alignItems' => 'stretch' ), $opts['layout'] ),
				array( 'flexWrap' => 'nowrap' ),
				array( 'flexWrap' => $opts['stack'] ? 'wrap' : 'nowrap' )
			),
			'sizing'  => gl_v( array( 'width' => '100%', 'maxWidth' => $opts['max_width'] ) ),
			'spacing' => gl_v(
				array(
					'padding' => gl_box4( '0px' ),
					'margin'  => array( 'top' => $opts['margin_top'], 'bottom' => $opts['margin_bottom'] ),
				)
			),
		),
		$opts['deco']
	);
	return gl_d5_block( 'divi/row', array( 'module' => $module ), $columns );
}

/**
 * Column. $span is the desktop width in 24ths (8 = 1/3, 12 = 1/2, 16 = 2/3, 24 = full).
 */
function gl_col( $span, array $children, array $layout = array(), $class = '', $stack = true ) {
	$module               = gl_meta( '', $class );
	$module['decoration'] = array(
		'sizing'  => gl_v(
			array( 'flexType' => $span . '_24' ),
			array( 'flexType' => $span . '_24' ),
			array( 'flexType' => $stack ? '24_24' : $span . '_24' )
		),
		'spacing' => gl_v( array( 'padding' => gl_box4( '0px' ) ) ),
	);
	if ( $layout ) {
		$module['decoration']['layout'] = gl_v( $layout );
	}
	return gl_d5_block( 'divi/column', array( 'module' => $module ), $children );
}

function gl_group( $label, array $children, array $deco = array(), $class = '' ) {
	$module               = gl_meta( $label, $class );
	$module['decoration'] = $deco;
	return gl_d5_block( 'divi/group', array( 'module' => $module ), $children );
}

/**
 * Heading module.
 *
 * @param array $font  gl_font() + headingLevel.
 * @param array $deco  module.decoration.
 */
function gl_heading( $text, $level, array $font, array $deco = array(), $class = '', $shadow = false ) {
	// Heading titles are plain text: store "&" not "&amp;".
	$text  = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$title = array(
		'innerContent' => gl_v( $text ),
		'decoration'   => array( 'font' => array( 'font' => gl_v( $font + array( 'headingLevel' => $level ) ) ) ),
	);
	if ( $shadow ) {
		$title['decoration']['font']['textShadow'] = gl_text_shadow();
	}
	$attrs = array(
		'title'  => $title,
		'module' => gl_meta( '', $class ) + ( $deco ? array( 'decoration' => $deco ) : array() ),
	);
	return gl_d5_block( 'divi/heading', $attrs );
}

/** Text module (rich text, raw HTML). */
function gl_text( $label, $html, array $font, array $deco = array(), $class = '', array $content_deco = array() ) {
	$content_deco = array_replace_recursive( array( 'bodyFont' => array( 'body' => array( 'font' => gl_v( $font ) ) ) ), $content_deco );
	return gl_d5_block(
		'divi/text',
		array(
			'module'  => gl_meta( $label, $class ) + ( $deco ? array( 'decoration' => $deco ) : array() ),
			'content' => array(
				'innerContent' => gl_v( $html ),
				'decoration'   => $content_deco,
			),
		)
	);
}

/**
 * Image module.
 *
 * @param array $deco       module.decoration (sizing, border radius…).
 * @param array $image_deco image.decoration (fit…).
 */
function gl_image( $file, $alt, array $deco = array(), array $image_deco = array(), $class = '', $label = '' ) {
	$image = array( 'innerContent' => gl_v( array( 'src' => '{{img:' . $file . '}}', 'alt' => $alt ) ) );
	if ( $image_deco ) {
		$image['decoration'] = $image_deco;
	}
	return gl_d5_block(
		'divi/image',
		array(
			'image'  => $image,
			'module' => gl_meta( $label ? $label : $alt, $class ) + ( $deco ? array( 'decoration' => $deco ) : array() ),
		)
	);
}

function gl_video( $youtube_id, $label ) {
	return gl_d5_block(
		'divi/video',
		array(
			'video'  => array( 'innerContent' => gl_v( array( 'src' => 'https://www.youtube.com/watch?v=' . $youtube_id ) ) ),
			'module' => gl_meta( $label ) + array( 'decoration' => array( 'sizing' => gl_v( array( 'width' => '100%' ) ) ) ),
		)
	);
}

function gl_code( $label, $html ) {
	return gl_d5_block(
		'divi/code',
		array(
			'module'  => gl_meta( $label ),
			'content' => array( 'innerContent' => gl_v( $html ) ),
		)
	);
}

/**
 * Slider of image slides (each slide = background image), 8px corners,
 * same height as the original 750×412 images (scaled on smaller screens).
 */
function gl_slider( array $images ) {
	$slides = array();
	foreach ( $images as $i => $img ) {
		$file     = is_array( $img ) ? $img[0] : $img;
		$alt      = is_array( $img ) ? $img[1] : 'Slide ' . ( $i + 1 );
		$slides[] = gl_d5_block(
			'divi/slide',
			array(
				'module' => gl_meta( $alt ) + array(
					'decoration' => array(
						'background' => gl_v(
							array(
								'color' => '#000000',
								'image' => array(
									'url'      => '{{img:' . $file . '}}',
									'size'     => 'cover',
									'position' => 'center center',
									'repeat'   => 'no-repeat',
								),
							)
						),
						'sizing'     => gl_v( array( 'minHeight' => '412px' ), array( 'minHeight' => '340px' ), array( 'minHeight' => '179px' ) ),
						'spacing'    => gl_v( array( 'padding' => gl_box4( '0px' ) ) ),
					),
				),
			)
		);
	}
	return gl_d5_block(
		'divi/slider',
		array(
			'module' => gl_meta( 'Image slider' ) + array(
				'decoration' => array(
					'border' => gl_v( array( 'radius' => gl_radius( '8px' ) ) ),
					'sizing' => gl_v( array( 'width' => '100%' ) ),
				),
			),
		),
		$slides
	);
}

/** Menu-driven links (one Link module looped over a WordPress menu). */
function gl_menu_links( $menu_id, $menu_label, $font_desktop, $font_phone, $class ) {
	$hover = isset( $font_desktop['hover'] ) ? $font_desktop['hover'] : null;
	unset( $font_desktop['hover'] );
	$var = function ( $name ) {
		return '$variable({"type":"content","value":{"name":"' . $name . '","settings":{"before":"","after":"","loop_position":""}}})$';
	};
	return gl_d5_block(
		'divi/link',
		array(
			'module'  => array_replace_recursive(
				gl_meta( 'Menu links (Appearance > Menus)', $class ),
				array(
					'advanced'   => array(
						'loop' => gl_v(
							array(
								'enable'                       => 'on',
								'loopId'                       => 'loop-gl' . substr( md5( $class . $menu_id ), 0, 8 ),
								'queryType'                    => 'menus',
								'orderBy'                      => 'menu_order',
								'subTypes'                     => array( array( 'value' => (string) $menu_id, 'label' => $menu_label ) ),
								'includePostWithSpecificTerms' => '',
								'excludePostWithSpecificTerms' => '',
								'includeSpecificPosts'         => '',
								'excludeSpecificPosts'         => '',
							)
						),
					),
					'decoration' => array(
						'sizing' => gl_v( array( 'size' => array( 'flexShrink' ) ) ),
						'layout' => gl_v( array( 'justifyContent' => 'start', 'alignItems' => 'center' ) ),
					),
				)
			),
			'content' => array(
				'innerContent' => gl_v(
					array(
						'text'       => $var( 'loop_menu_text' ),
						'linkUrl'    => $var( 'loop_menu_link' ),
						'linkTarget' => 'off',
					)
				),
				'decoration'   => array( 'font' => array( 'font' => gl_v( $font_desktop, null, $font_phone, $hover ? array( 'hover' => array( 'color' => $hover ) ) : array() ) ) ),
			),
		)
	);
}

/* =========================================================================
 * Shared components (mirror the original React components)
 * ====================================================================== */

/** Space a block of rich text the way the original's <p> margins did (1em). */
function gl_body_deco() {
	return array( 'bodyFont' => array( 'body' => array( 'list' => gl_v( array( 'paragraphSpacing' => '16px' ) ) ) ) );
}

/**
 * The original "Box": dark card with optional image, a title with an underline,
 * and light-grey content. Built as a Group so it stays one movable unit.
 */
function gl_box( $title, $html = '', $image = '', array $opts = array() ) {
	$opts     = array_merge( array( 'align' => null, 'grow' => true ), $opts );
	$children = array();

	if ( $image ) {
		$children[] = gl_image(
			$image,
			wp_strip_all_tags( $title ),
			array(
				'sizing' => gl_v( array( 'width' => '100%', 'height' => '200px' ) ),
				'border' => gl_v( array( 'radius' => gl_radius( '8px', '8px', '0px', '0px' ) ) ),
			),
			array( 'fit' => gl_v( array( 'objectFit' => 'cover', 'objectPosition' => 'center center' ) ) )
		);
	}

	if ( '' !== $title ) {
		$children[] = gl_heading(
			$title,
			'h2',
			gl_font( '24px', '#e6e6e6', '700', $opts['align'] ),
			array(
				'spacing' => gl_v(
					array(
						'margin'  => array( 'top' => '19.92px', 'bottom' => '16px' ),
						'padding' => array( 'top' => '0px', 'bottom' => '8px' ),
					)
				),
				'border'  => gl_v( array( 'styles' => array( 'bottom' => array( 'width' => '2px', 'color' => '#555555', 'style' => 'solid' ) ) ) ),
			)
		);
	}

	if ( '' !== $html ) {
		$children[] = gl_text(
			'Content',
			$html,
			gl_font( '16px', '#cccccc', '400', $opts['align'] ),
			array( 'spacing' => gl_v( array( 'padding' => gl_box4( '16px', '0px' ) ) ) ),
			'',
			gl_body_deco() + array(
				'headingFont' => array(
					'h2' => array( 'font' => gl_v( gl_font( '24px', '#cccccc', '700' ) ) ),
				),
			)
		);
	}

	$deco = array(
		'background' => gl_v( array( 'color' => '#333333' ) ),
		'spacing'    => gl_v( array( 'padding' => gl_box4( '16px' ) ) ),
		'border'     => gl_v(
			array(
				'styles' => array( 'all' => array( 'width' => '1px', 'color' => '#444444', 'style' => 'solid' ) ),
				'radius' => gl_radius( '8px' ),
			)
		),
		'boxShadow'  => gl_shadow( '0px', '4px', '8px', '0px', 'rgba(0,0,0,0.2)' ),
		'layout'     => gl_v( array( 'flexDirection' => 'column', 'alignItems' => 'stretch' ) ),
	);
	if ( $opts['grow'] ) {
		$deco['sizing'] = gl_v( array( 'size' => array( 'flexGrow' ), 'width' => '100%' ) );
	}

	return gl_group( wp_strip_all_tags( $title ? $title : 'Box' ), $children, $deco );
}

/** Page heading in off-white (#fbf7f5), centered. */
function gl_section_title( $text, $level = 'h2', $margin_top = '0px', $margin_bottom = '32px' ) {
	$size = 'h1' === $level ? '32px' : '24px';
	return gl_heading(
		$text,
		$level,
		gl_font( $size, '#fbf7f5', '700', 'center' ),
		array( 'spacing' => gl_v( array( 'margin' => array( 'top' => $margin_top, 'bottom' => $margin_bottom ) ) ) )
	);
}

/** Top row used on About/Prices/Products/Profiles: box on the left, slider on the right. */
function gl_top_row_with_slider( $box_title, $box_html, array $slides ) {
	return gl_row(
		'Top',
		array(
			gl_col( 8, array( gl_box( $box_title, $box_html ) ) ),
			gl_col( 16, array( gl_slider( $slides ) ) ),
		),
		array( 'margin_bottom' => '32px' )
	);
}

/** Paragraph list helper. */
function gl_html_ps( array $lines ) {
	return '<p>' . implode( '</p><p>', $lines ) . '</p>';
}

/**
 * Image card with a hover overlay (dark tint + centered text), used for the
 * team and news grids. $link (optional) opens in the lightbox.
 */
function gl_hover_card( $file, $alt, $overlay_html, $link = '', array $image_deco = array() ) {
	if ( $link ) {
		$overlay_html = '<a href="{{img:' . $link . '}}" class="gl-lightbox">' . $overlay_html . '</a>';
	}
	return gl_group(
		$alt,
		array(
			gl_image( $file, $alt, array_replace_recursive( array( 'sizing' => gl_v( array( 'width' => '100%' ) ) ), $image_deco ) ),
			gl_text(
				'Hover text',
				$overlay_html,
				gl_font( '16px', '#fbf7f5', '400', 'center' ),
				array(),
				'gl-hover-overlay',
				array( 'headingFont' => array( 'h3' => array( 'font' => gl_v( gl_font( '18.72px', '#fbf7f5', '700', 'center' ) ) ) ) )
			),
		),
		array( 'sizing' => gl_v( array( 'width' => '100%' ) ) ),
		'gl-hover-card'
	);
}

/** Grid of items as rows of three columns. */
function gl_grid_rows( $label, array $cells, $gap = '16px', $stack = true ) {
	$rows = array();
	foreach ( array_chunk( $cells, 3 ) as $n => $chunk ) {
		$cols = array();
		for ( $i = 0; $i < 3; $i++ ) {
			$cols[] = gl_col( 8, isset( $chunk[ $i ] ) ? array( $chunk[ $i ] ) : array(), array(), '', $stack );
		}
		$rows[] = gl_row( $label . ' ' . ( $n + 1 ), $cols, array( 'gap' => $gap, 'margin_bottom' => $gap, 'stack' => $stack ) );
	}
	return $rows;
}

/* =========================================================================
 * Pages
 * ====================================================================== */

/**
 * The seven pages, keyed by slug, in menu order.
 *
 * @return array<string, array{title:string, sections:callable}>
 */
function gl_page_definitions() {
	return array(
		'home'     => array( 'title' => 'Home', 'sections' => 'gl_page_home' ),
		'about'    => array( 'title' => 'About', 'sections' => 'gl_page_about' ),
		'prices'   => array( 'title' => 'Prices', 'sections' => 'gl_page_prices' ),
		'products' => array( 'title' => 'Products', 'sections' => 'gl_page_products' ),
		'location' => array( 'title' => 'Location', 'sections' => 'gl_page_location' ),
		'profiles' => array( 'title' => 'Profiles', 'sections' => 'gl_page_profiles' ),
		'news'     => array( 'title' => 'News', 'sections' => 'gl_page_news' ),
	);
}

function gl_page_home() {
	$line = function ( $text, $level, $size, $phone ) {
		return gl_heading(
			$text,
			$level,
			gl_font( $size, '#ffffff', '800', 'center' ),
			array(
				'spacing' => gl_v( array( 'margin' => gl_box4( '0px' ), 'padding' => gl_box4( '0px' ) ) ),
			),
			'',
			true
		);
	};

	$modules = array(
		$line( 'WHERE TRADITION MEETS TREND', 'h2', '36px', '18px' ),
		$line( 'GIACOMO LICATA', 'h1', '128px', '64px' ),
		$line( 'Est. 1986', 'h2', '64px', '32px' ),
		$line( '374 FILTON AVENUE, HORFIELD, BRISTOL, BS7 0BE', 'h3', '36px', '18px' ),
		$line( '0117 923 6663', 'h3', '36px', '18px' ),
	);
	// Phone font sizes.
	$phone = array( '18px', '64px', '32px', '18px', '18px' );
	foreach ( $modules as $i => &$m ) {
		$m['attrs']['title']['decoration']['font']['font']['phone'] = array( 'value' => array( 'size' => $phone[ $i ], 'textAlign' => 'center' ) );
	}
	unset( $m );

	return array(
		gl_section(
			'Hero',
			array(
				gl_row(
					'Hero',
					array( gl_col( 24, $modules, array( 'flexDirection' => 'column', 'justifyContent' => 'center', 'alignItems' => 'center', 'rowGap' => '0px' ) ) ),
					array(
						'max_width' => '100%',
						'gap'       => '0px',
						'deco'      => array( 'sizing' => gl_v( array( 'minHeight' => '100vh' ) ) ),
					)
				),
			),
			array(
				'background' => gl_v(
					array(
						'color' => '#242424',
						'image' => array(
							'url'      => '{{img:stockbarberimage.jpg}}',
							'size'     => 'cover',
							'position' => 'center center',
							'repeat'   => 'no-repeat',
						),
					)
				),
				'spacing'    => gl_v( array( 'padding' => gl_box4( '0px' ) ) ),
				'sizing'     => gl_v( array( 'minHeight' => '100vh' ) ),
			)
		),
	);
}

function gl_page_about() {
	$opening = gl_html_ps(
		array(
			'Open Six Days A Week Except For Bank Holidays',
			'Mon-Fri: 9am - 5:30pm',
			'Sat: 8am - 5pm',
			'Sun: Closed',
			'OAP Rates Monday to Thursday',
			'No Appointment Needed',
		)
	);
	$help     = gl_html_ps(
		array(
			'We do wash hair at Giacomos and there is a small extra price to pay. Clean hair makes cutting and creating your style easier for us and gives you a better finish to your cut. If you do not want a wash we kindly ask that you wash any gel, wax or spray from your hair before you visit us.',
			'When washing the wax from your hair put the shampoo on your hair before you get it wet, get a good lather and then wash as normal.',
		)
	);
	$customer = gl_html_ps( array( 'If you need a particular job done we are full of useful information. We talk to our clients and know their backgrounds, from local tradesmen to financial advisors. That leaky pipe, decorating, garden renovations&#8230; the list is endless. Just ask!' ) );
	$styles   = gl_html_ps( array( 'It may be the traditional gents short back and sides, flat top, mohawk, wedge, boston, spikey, shaved pattern, long layered cut, short layered cut or something else unique to you, whatever your style we can be of service!' ) );

	$video_box = function ( $title, $id ) {
		$box = gl_box( $title );
		// Box = group(heading); add the Video module under the heading.
		$box['innerBlocks'][]  = gl_video( $id, 'Video: ' . $title );
		$box['innerContent'][] = null;
		return $box;
	};

	return array(
		gl_page_section(
			'About',
			array(
				gl_top_row_with_slider( 'Opening Times', $opening, array( 'aboutimage1.jpg', 'aboutimage2.jpg' ) ),
				gl_row(
					'Info boxes',
					array(
						gl_col( 8, array( gl_box( 'Help us help you', $help, 'hair-wash.jpg' ) ) ),
						gl_col( 8, array( gl_box( 'Helping the Customer', $customer, 'helping-customer.jpg' ) ) ),
						gl_col( 8, array( gl_box( 'Styles', $styles, 'styles.jpg' ) ) ),
					)
				),
				gl_row(
					'Videos',
					array(
						gl_col( 12, array( $video_box( 'I know what you did this summer', '_4s2QAaKvlo' ) ) ),
						gl_col( 12, array( $video_box( 'Giacomo Licata Barber Shop Bristol', '8tXIz-b66aY' ) ) ),
					),
					array( 'margin_top' => '32px' )
				),
			)
		),
	);
}

function gl_page_prices() {
	$prices = gl_html_ps(
		array(
			'Men\'s Haircut - &#163;14.00',
			'Skin-Fade - &#163;16.50',
			'Boys (under 11) - &#163;13.00',
			'W-C-B - &#163;13.50',
			'O.A.P - &#163;9.50',
			'O.A.P (WCB)',
			'Flattop - &#163;15.00',
			'Shave - &#163;16.50',
			'Restyling - &#163;16.50',
			'Beard Trim - &#163;6.00',
			'<strong>Ear Piercing</strong>',
			'1 Ear - &#163;20.00',
			'2 Ears - &#163;35.00',
			'<strong>CASH ONLY</strong>',
			'All prices are subject to change depending on length &amp; style required',
			'*OAP haircuts Mon - Thurs',
			'**Cut throat shaves are by appointment only please',
		)
	);
	$services = gl_html_ps(
		array(
			'As well as gentlemen\'s haircuts we are pleased to offer ear piercing for men and women, or when England play football why not have St Georges cross painted on your head, but don\'t worry, it will wash off!',
			'A wide range of products are available including studs for your ears, and if something\'s not in stock don\'t be afraid to ask, we may be able to order it in for you or tell you where you can purchase it.',
			'Come and see us for yourself, enjoy our warm &amp; friendly atmosphere and tell a friend!',
		)
	);
	$special  = gl_html_ps( array( 'Special bookings can be made for weddings, stag do\'s, birthday\'s and special occassions. For more information contact Giacomo on 0117 923 6663.' ) );

	return array(
		gl_page_section(
			'Prices',
			array(
				gl_top_row_with_slider(
					'Aftershave &amp; Toiletries',
					'<p>We now stock a wide range of Proraso Aftershare &amp; Toiletries, please click here for more information</p>',
					array( 'pricesImage1.png', 'pricesImage2.png' )
				),
				gl_row(
					'Prices & services',
					array(
						gl_col( 12, array( gl_box( 'Prices', $prices ) ) ),
						gl_col(
							12,
							array( gl_box( 'Services', $services ), gl_box( 'Special Occasions', $special ) ),
							array( 'flexDirection' => 'column', 'rowGap' => '16px' )
						),
					)
				),
			)
		),
	);
}

function gl_page_products() {
	$products = array(
		array( 'shave-cream.jpg', 'Pro Shaving Cream 100ml', '&#163;9.50' ),
		array( 'aftershave-cream.jpg', 'After Shave Cream', '&#163;11.50' ),
		array( 'pre-shave.jpg', 'Pre Shaving Cream', '&#163;9.50' ),
		array( 'after-shave.jpg', 'After Shave 100ml', '&#163;11.50' ),
		array( 'shaving-cream.jpg', 'Shaving Cream Tube', '&#163;6.00' ),
		array( 'shaving-cream-jar.jpg', 'Shaving Cream Jars Sensitive', '&#163;6.00' ),
		array( 'brush-bowl.jpg', 'Omega Shaving Brush, Bowl, Stand Set', '&#163;19.50' ),
		array( 'brush-stand.jpg', 'Omega Pure Bristle Brush with Stand', '&#163;9.50' ),
		array( 'shaving-mug.jpg', 'Shaving Mug (Red, Black, Blue)', '&#163;28.50' ),
		array( 'shave-stand.jpg', 'Frank Shaving 3 Piece Set', '&#163;29.50' ),
		array( 'metal-stand.jpg', 'Razor Metal Stand', '&#163;10.50' ),
		array( 'sweeny.jpg', 'Sweeney Todd Razor', '&#163;29.50' ),
		array( 'vent-brush.jpg', 'Vent Brush', '&#163;2.50' ),
		array( 'BlackRedGreen.jpg', 'Hair Powder - Semi Matte', '&#163;7.50' ),
		array( 'BlackRedOrange.jpg', 'Hair Powder - Volumizing Matte', '&#163;7.50' ),
		array( 'BlackRedYellowResize.jpg', 'Hair Powder - Strong Touch', '&#163;7.50' ),
		array( 'RedOneBlueResize.jpg', 'Hair Wax - Blue', '&#163;10.00' ),
		array( 'RedOneGreenResize.jpg', 'Hair Wax - Green', '&#163;10.00' ),
		array( 'RedOneRedResize.jpg', 'Hair Wax - Red', '&#163;10.00' ),
	);

	$cells = array();
	foreach ( $products as $i => $p ) {
		$cells[] = gl_group(
			wp_strip_all_tags( $p[1] ),
			array(
				gl_image(
					$p[0],
					'Product ' . ( $i + 1 ),
					array(
						'sizing' => gl_v( array( 'width' => '100%', 'height' => '250px' ) ),
						'border' => gl_v( array( 'radius' => gl_radius( '8px' ) ) ),
					),
					array( 'fit' => gl_v( array( 'objectFit' => 'fill' ) ) )
				),
				gl_heading( $p[1], 'h3', gl_font( '18.72px', '#fbf7f5', '700', 'center' ), array( 'spacing' => gl_v( array( 'margin' => array( 'top' => '8px', 'bottom' => '0px' ) ) ) ) ),
				gl_text( 'Price', '<p>' . $p[2] . '</p>', gl_font( '16px', '#fbf7f5', '400', 'center' ) ),
			),
			array(
				'sizing' => gl_v( array( 'width' => '100%' ) ),
				'layout' => gl_v( array( 'flexDirection' => 'column', 'rowGap' => '0px' ) ),
			)
		);
	}

	return array(
		gl_page_section(
			'Products',
			array_merge(
				array(
					gl_top_row_with_slider(
						'Our Premium Products',
						'<p>Explore our range of premium products including aftershaves, toiletries, and more. Click on any product to learn more.</p>',
						array( array( 'productsImage1.png', 'Proraso' ), array( 'redone2-bckgroundremoved.png', 'Product 2' ) )
					),
					gl_row( 'Heading', array( gl_col( 24, array( gl_section_title( 'OUR PRODUCTS' ) ) ) ) ),
				),
				gl_grid_rows( 'Products', $cells )
			)
		),
	);
}

function gl_page_location() {
	$address = '<p>374 Filton Avenue, Horfield, Bristol BS7 0BE</p><p>Telephone: 0117 923 6663</p>'
		. '<h2>Opening Times</h2>' . gl_html_ps( array( 'Mon-Fri: 9am - 7pm', 'Sat: 9am - 5pm', 'Sun: Closed' ) );

	$photo = gl_group(
		'Shop photo',
		array(
			gl_image(
				'outside-shop.png',
				'Barbershop',
				array(
					'sizing' => gl_v( array( 'width' => '100%' ) ),
					'border' => gl_v( array( 'radius' => gl_radius( '8px' ) ) ),
				)
			),
		),
		array(
			'background' => gl_v( array( 'color' => '#333333' ) ),
			'spacing'    => gl_v( array( 'padding' => gl_box4( '16px' ) ) ),
			'border'     => gl_v(
				array(
					'styles' => array( 'all' => array( 'width' => '1px', 'color' => '#444444', 'style' => 'solid' ) ),
					'radius' => gl_radius( '8px' ),
				)
			),
			'boxShadow'  => gl_shadow( '0px', '4px', '8px', '0px', 'rgba(0,0,0,0.2)' ),
			'sizing'     => gl_v( array( 'size' => array( 'flexGrow' ), 'width' => '100%' ) ),
		)
	);

	$map = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2483.999317481905!2d-2.576987623075407!3d51.49488001161741!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x487191dd79204213%3A0xc3b41a8c31805052!2sGiacomo%20Licata!5e0!3m2!1sen!2suk!4v1720784502194!5m2!1sen!2suk" style="width:100%;height:450px;border:0;border-radius:8px;" allowfullscreen="" loading="lazy" title="Giacomo Licata on Google Maps"></iframe>';

	return array(
		gl_page_section(
			'Location',
			array(
				gl_row(
					'Top',
					array(
						gl_col( 12, array( gl_box( 'Address', $address ) ) ),
						gl_col( 12, array( $photo ) ),
					),
					array( 'margin_bottom' => '32px' )
				),
				gl_row(
					'Map',
					array(
						gl_col(
							24,
							array(
								gl_section_title( 'FIND US ON MAPS', 'h1', '0px', '21.44px' ),
								gl_code( 'Google map (embed)', $map ),
							)
						),
					)
				),
			)
		),
	);
}

function gl_page_profiles() {
	$staff = array(
		array( 'Giacomo', 'Owner/Barber', 'giacomo-sm.png' ),
		array( 'Carmelo', 'Barber', 'carmelo-sm.png' ),
		array( 'Domenico', 'Barber', 'domenico-sm.png' ),
		array( 'Alec', 'Barber', 'alec-sm.png' ),
		array( 'Gavin', 'Barber', 'gavin_sm.png' ),
	);
	// Rounded outer corners of the grid, as in the original.
	$corners = array(
		0 => gl_radius( '8px', '0px', '0px', '0px' ),
		2 => gl_radius( '0px', '8px', '0px', '0px' ),
		3 => gl_radius( '0px', '0px', '0px', '8px' ),
		5 => gl_radius( '0px', '0px', '8px', '0px' ),
	);

	$cells = array();
	foreach ( $staff as $i => $s ) {
		$radius  = isset( $corners[ $i ] ) ? array( 'border' => gl_v( array( 'radius' => $corners[ $i ] ) ) ) : array();
		$cells[] = gl_hover_card( $s[2], $s[0], '<h3>' . $s[1] . '</h3><p>' . $s[0] . '</p>', '', $radius );
	}

	return array(
		gl_page_section(
			'Profiles',
			array_merge(
				array(
					gl_top_row_with_slider(
						'Aftershave &amp; Toiletries',
						'<p>We now stock a wide range of Proraso Aftershave &amp; Toiletries. Please click here for more information.</p>',
						array( 'profileImage1.png', 'profileImage2.png' )
					),
					gl_row( 'Heading', array( gl_col( 24, array( gl_section_title( 'MEET THE TEAM', 'h1' ) ) ) ) ),
				),
				gl_grid_rows( 'Team', $cells, '0px', false )
			)
		),
	);
}

function gl_page_news() {
	$news = array(
		array( 'carm-lee-small.jpg', 'carm-lee-large.jpg', 'Carm with World Champion Lee Haskins' ),
		array( 'james-small.jpg', 'james-large.jpg', 'Double Champ James \'Lights Out\' Lee' ),
		array( 'bet-small.jpg', 'bet-large.jpg', 'Double or quits for return clash' ),
		array( '30-sm.jpg', '30-sm.jpg', 'Giacomo Licata Celebrates 30 Years' ),
		array( 'lee-small.jpg', 'lee-large.jpg', 'World Title Champion Lee Haskins' ),
		array( '25years.png', '25years-large.png', 'Giacomo Licata 25 Years in Business' ),
		array( 'john-small.jpg', 'john-large.jpg', 'Giac and John Barnes' ),
		array( 'ian-small.jpg', 'ian-large.jpg', 'Giac and Ian Rush' ),
		array( 'stone-small.jpg', 'stone-large.jpg', 'Adrian Stone signed poster' ),
		array( 'frank-small.jpg', 'frank-large.jpg', 'Frank Bruno signed poster' ),
		array( 'rovers-manager-small.jpg', 'rovers-manager-large.jpg', 'Giac and Former Bristol Rovers Manager' ),
		array( 'kevin-maggs-small.jpg', 'kevin-maggs-large.jpg', 'Giacomo Licata &amp; Kevin Maggs' ),
		array( 'lee-haskins-small.jpg', 'lee-haskins-large.jpg', 'Giacomo Licata &amp; Lee Haskins with his belt' ),
		array( '8-small.png', '8-large.png', 'Giacomo Licata Fund-raiser' ),
		array( '7-small.png', '7-large.png', 'Giacomo Licata South Africa World Cup bet for charity' ),
		array( '6-small.png', '6-large.png', 'Bristol for World Cup host city' ),
		array( '4-small.png', '4-large.png', 'Giac\'s Bet on new England manager' ),
		array( '2-small.png', '2-large.png', 'Giac spray-painting heads' ),
		array( '1-small.png', '1-large.png', 'England vs Italy' ),
		array( '2.png', '2-l.jpg', 'St George flag painting' ),
		array( '3.jpg', '3-l.jpg', 'Glass of wine and a short back and sides' ),
		array( '4.jpg', '4-l.jpg', 'Giacomo\'s flag fiesta' ),
		array( '5.jpg', '5-l.jpg', 'Can Junior cut it at Rovers?' ),
		array( '6.jpg', '6-l.jpg', 'Red nose day' ),
		array( '7.jpg', '7-l.jpg', 'Midweek Misery' ),
		array( '8.jpg', '8-l.jpg', 'Rivals\' bet carries a lifetime penalty' ),
		array( '9.jpg', '9-l.jpg', 'Giacomo Licata, Lee Haskins &amp; Former Mayor of Bristol' ),
	);
	$collection = array(
		array( 'collection1.jpg', 'collection1.jpg', 'Giacomo\'s Collection' ),
		array( 'collection2.jpg', 'collection2.jpg', 'Shirt signed by the Scottish Rugby Team' ),
		array( 'collection3.jpg', 'collection3.jpg', 'Shirt signed by the Irish Rugby Team' ),
		array( 'collection4.jpg', 'collection4.jpg', 'Shirt signed by the English Rugby Team' ),
		array( 'collection5.jpg', 'collection5.jpg', 'Shirt signed by the Italian Rugby Team' ),
		array( 'collection6.jpg', 'collection6.jpg', 'Shirt signed by the Welsh Rugby Team' ),
		array( 'collection7.jpg', 'collection7.jpg', 'Giac &amp; Lee Haskins with his belt' ),
	);

	$cards = function ( array $items, $prefix ) {
		$cells = array();
		foreach ( $items as $i => $item ) {
			$cells[] = gl_hover_card(
				$item[0],
				$prefix . ' ' . ( $i + 1 ),
				'<h3>' . $item[2] . '</h3>',
				$item[1],
				array( 'border' => gl_v( array( 'radius' => gl_radius( '8px' ) ) ) )
			);
		}
		return $cells;
	};

	$video = gl_video( '4bVZKPoG5Fc', 'World record video' );

	return array(
		gl_page_section(
			'News',
			array_merge(
				array(
					gl_row(
						'Top',
						array(
							gl_col( 8, array( gl_box( 'GIACOMO LICATA SETS WORLD RECORD FOR HIGHEST HAIRCUT', '', '', array( 'align' => 'center' ) ) ) ),
							gl_col( 16, array( $video ), array( 'flexDirection' => 'column', 'justifyContent' => 'center' ) ),
						),
						array( 'margin_bottom' => '32px' )
					),
					gl_row( 'Heading: In the news', array( gl_col( 24, array( gl_section_title( 'IN THE NEWS' ) ) ) ) ),
				),
				gl_grid_rows( 'News', $cards( $news, 'News' ), '16px', false ),
				array( gl_row( 'Heading: Collection', array( gl_col( 24, array( gl_section_title( 'GIAC\'S COLLECTION', 'h2', '16px' ) ) ) ) ) ),
				gl_grid_rows( 'Collection', $cards( $collection, 'Collection' ), '16px', false )
			),
			false
		),
	);
}

/* =========================================================================
 * Theme Builder: Global Header + Global Footer
 * ====================================================================== */

/**
 * Header: logo left, menu right; fixed over the page, transparent until the
 * page scrolls (class gl-header – see site.css/site.js).
 */
function gl_layout_header( $menu_id ) {
	$nav_font = array(
		'size'       => '16px',
		'color'      => '#ffffff',
		'weight'     => '800',
		'lineHeight' => 'normal',
	);

	return array(
		gl_section(
			'Header',
			array(
				gl_row(
					'Header bar',
					array(
						gl_col(
							12,
							array( gl_image( 'giac-logo.png', 'Giacomo Licata Logo', array( 'sizing' => gl_v( array( 'width' => 'auto', 'maxWidth' => '200px' ) ) ) ) ),
							array( 'justifyContent' => 'center', 'alignItems' => 'flex-start' )
						),
						gl_col(
							12,
							array( gl_menu_links( $menu_id, 'Giacomo Licata Menu', $nav_font, array( 'size' => '12px' ), 'gl-nav-item' ) ),
							array( 'flexDirection' => 'row', 'flexWrap' => 'wrap', 'justifyContent' => 'flex-end', 'alignItems' => 'center', 'columnGap' => '20px' ),
							'gl-header-nav'
						),
					),
					array(
						'max_width' => '100%',
						'gap'       => '0px',
						'layout'    => array( 'justifyContent' => 'space-between', 'alignItems' => 'center' ),
					)
				),
			),
			array(
				'background' => gl_v( array( 'color' => 'rgba(0,0,0,0)' ) ),
				'spacing'    => gl_v( array( 'padding' => gl_box4( '10px', '20px' ) ) ),
				'border'     => gl_v( array( 'styles' => array( 'bottom' => array( 'width' => '1px', 'color' => '#d9d9d9', 'style' => 'solid' ) ) ) ),
			),
			'gl-header'
		),
	);
}

/** Footer: credit line, then a dark bar with logo / menu / social icons. */
function gl_layout_footer( $menu_id ) {
	$social = '';
	foreach ( gl_social_links() as $s ) {
		$social .= '<a href="' . esc_url( $s['url'] ) . '"><img src="{{theme:' . $s['icon'] . '}}" alt="' . esc_attr( $s['label'] ) . '" width="24" height="24" /></a>';
	}

	$nav_font = array(
		'size'       => '16px',
		'color'      => '#ffffff',
		'weight'     => '800',
		'lineHeight' => 'normal',
		'hover'      => '#996e52',
	);
	$center   = array( 'flexDirection' => 'row', 'flexWrap' => 'wrap', 'justifyContent' => 'center', 'alignItems' => 'center', 'columnGap' => '20px' );

	return array(
		gl_section(
			'Footer',
			array(
				gl_row(
					'Credit',
					array(
						gl_col(
							24,
							array(
								gl_text(
									'Background credit',
									'<p>Background by rawpixel.com on Freepik</p>',
									gl_font( '10px', '#aaaaaa', '400', 'right' ),
									array( 'spacing' => gl_v( array( 'margin' => array( 'top' => '10px', 'bottom' => '10px' ) ) ) )
								),
							)
						),
					),
					array( 'max_width' => '100%' )
				),
				gl_row(
					'Footer bar',
					array(
						gl_col( 8, array( gl_image( 'logo.png', 'Footer Logo', array( 'sizing' => gl_v( array( 'width' => 'auto', 'maxWidth' => '200px' ) ) ) ) ), array( 'alignItems' => 'center', 'justifyContent' => 'center' ) ),
						gl_col( 8, array( gl_menu_links( $menu_id, 'Giacomo Licata Menu', $nav_font, array( 'size' => '12px' ), 'gl-footer-nav-item' ) ), $center ),
						gl_col( 8, array( gl_text( 'Social icons', '<div class="gl-social">' . $social . '</div>', gl_font( '16px', '#ffffff', '400', 'center' ) ) ), $center ),
					),
					array(
						'max_width' => '100%',
						'gap'       => '0px',
						'class'     => 'gl-footer-bar',
						'layout'    => array( 'alignItems' => 'center' ),
						'deco'      => array(
							'background' => gl_v( array( 'color' => '#222325' ) ),
							'spacing'    => gl_v( array( 'padding' => gl_box4( '30px', '0px' ) ) ),
							'border'     => gl_v( array( 'styles' => array( 'top' => array( 'width' => '1px', 'color' => '#d9d9d9', 'style' => 'solid' ) ) ) ),
						),
					)
				),
			),
			array(
				'background' => gl_v( array( 'color' => 'rgba(0,0,0,0)' ) ),
				'spacing'    => gl_v( array( 'padding' => gl_box4( '0px' ) ) ),
			)
		),
	);
}

/* =========================================================================
 * Output
 * ====================================================================== */

/**
 * Swap {{img:file}} (Media Library) and {{theme:file}} (theme assets) tokens for URLs.
 * Tokens sit inside JSON strings; plain URLs need no extra escaping there.
 */
function gl_resolve_tokens( $content, $resolver ) {
	$content = preg_replace_callback(
		'/\{\{img:([^}]+)\}\}/',
		function ( $m ) use ( $resolver ) {
			return esc_url_raw( call_user_func( $resolver, $m[1] ) );
		},
		$content
	);
	return preg_replace_callback(
		'/\{\{theme:([^}]+)\}\}/',
		function ( $m ) {
			return esc_url_raw( GL_THEME_URI . '/assets/images/' . $m[1] );
		},
		$content
	);
}

/** Final Divi 5 post_content for a page. */
function gl_build_page_content( $slug, $resolver ) {
	$defs = gl_page_definitions();
	if ( empty( $defs[ $slug ] ) ) {
		return '';
	}
	return gl_resolve_tokens( gl_d5_serialize( call_user_func( $defs[ $slug ]['sections'] ) ), $resolver );
}

/** Final Divi 5 content for the header ("header") or footer ("footer") layout. */
function gl_build_layout_content( $which, $resolver, $menu_id = 0 ) {
	$sections = 'footer' === $which ? gl_layout_footer( $menu_id ) : gl_layout_header( $menu_id );
	return gl_resolve_tokens( gl_d5_serialize( $sections ), $resolver );
}
