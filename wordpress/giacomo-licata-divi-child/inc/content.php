<?php
/**
 * Page content for the Giacomo Licata site, expressed as Divi 5 layouts.
 *
 * Each page is a tree of Divi 5 blocks (divi/section > divi/row > divi/column >
 * divi/text | divi/code). The HTML inside the modules reproduces the original
 * React site's markup, and is styled by assets/css/site.css.
 *
 * Image references use the token {{img:filename}} and are swapped for the
 * Media Library URL when the installer runs.
 *
 * @package GiacomoLicata
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Divi 5 block helpers
 * ---------------------------------------------------------------------- */

/**
 * Divi builder version stamped on every block.
 */
function gl_d5_version() {
	if ( defined( 'ET_BUILDER_PRODUCT_VERSION' ) ) {
		return ET_BUILDER_PRODUCT_VERSION;
	}
	$divi = wp_get_theme( 'Divi' );
	if ( $divi->exists() && $divi->get( 'Version' ) ) {
		return $divi->get( 'Version' );
	}
	return '5.0.0';
}

/**
 * Common "module" attribute group: admin label + CSS class.
 *
 * @param string $label Admin label shown in the Divi layers panel.
 * @param string $class CSS class(es).
 * @param array  $advanced Extra entries for module.advanced.
 */
function gl_d5_module_attrs( $label, $class, $advanced = array() ) {
	$module = array();
	if ( $label ) {
		$module['meta'] = array( 'adminLabel' => array( 'desktop' => array( 'value' => $label ) ) );
	}
	if ( $class ) {
		$advanced['htmlAttributes'] = array( 'desktop' => array( 'value' => array( 'class' => $class ) ) );
	}
	if ( $advanced ) {
		$module['advanced'] = $advanced;
	}
	return $module;
}

/**
 * Build a parsed-block array that WordPress' serialize_block() understands.
 */
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

function gl_d5_section( $label, $class, array $rows ) {
	return gl_d5_block( 'divi/section', array( 'module' => gl_d5_module_attrs( $label, $class ) ), $rows );
}

/**
 * Divi 5 rows are flex rows: `flexColumnStructure` must name the column count,
 * and each column's width comes from its own `flexType` (see gl_d5_column()).
 *
 * @param string $structure Column layout, e.g. "1_3,2_3" (documentation only; widths live on the columns).
 */
function gl_d5_row( $label, $class, $structure, array $columns ) {
	unset( $structure );
	return gl_d5_block(
		'divi/row',
		array(
			'module' => gl_d5_module_attrs(
				$label,
				$class,
				array( 'flexColumnStructure' => array( 'desktop' => array( 'value' => 'equal-columns_' . count( $columns ) ) ) )
			),
		),
		$columns
	);
}

/**
 * @param string $type Column width, e.g. "1_3", "2_3", "1_2", "4_4".
 */
function gl_d5_column( $type, $class, array $modules ) {
	list( $num, $den ) = array_map( 'intval', explode( '_', $type ) );
	$flex              = (int) round( 24 * $num / max( 1, $den ) ) . '_24';
	return gl_d5_block(
		'divi/column',
		array(
			'module' => array_merge_recursive(
				gl_d5_module_attrs( '', $class, array( 'type' => array( 'desktop' => array( 'value' => $type ) ) ) ),
				array( 'decoration' => array( 'sizing' => array( 'desktop' => array( 'value' => array( 'flexType' => $flex ) ) ) ) )
			),
		),
		$modules
	);
}

function gl_d5_text( $label, $html, $class = '' ) {
	return gl_d5_block(
		'divi/text',
		array(
			'module'  => gl_d5_module_attrs( $label, $class ),
			'content' => array( 'innerContent' => array( 'desktop' => array( 'value' => $html ) ) ),
		)
	);
}

function gl_d5_code( $label, $html, $class = '' ) {
	return gl_d5_block(
		'divi/code',
		array(
			'module'  => gl_d5_module_attrs( $label, $class ),
			'content' => array( 'innerContent' => array( 'desktop' => array( 'value' => $html ) ) ),
		)
	);
}

/**
 * Serialize a list of sections into Divi 5 post_content.
 */
function gl_d5_serialize( array $sections ) {
	$out = '';
	foreach ( $sections as $section ) {
		$out .= serialize_block( $section );
	}
	return "<!-- wp:divi/placeholder -->\n" . $out . "\n<!-- /wp:divi/placeholder -->";
}

/* -------------------------------------------------------------------------
 * Markup snippets (mirroring the original React components)
 * ---------------------------------------------------------------------- */

/** "Box" component: dark card with optional image, title and content. */
function gl_html_box( $title, $content, $image = '', $image_alt = '' ) {
	$html = '<div class="gl-box">';
	if ( $image ) {
		$html .= '<img src="{{img:' . $image . '}}" alt="' . esc_attr( $image_alt ? $image_alt : $title ) . '" class="gl-box__image" />';
	}
	if ( '' !== $title ) {
		$html .= '<h2 class="gl-box__title">' . $title . '</h2>';
	}
	$html .= '<div class="gl-box__content">' . $content . '</div></div>';
	return $html;
}

/** "ImageSlider" component (initialised with slick by assets/js/site.js). */
function gl_html_slider( array $images ) {
	$html = '<div class="gl-slider">';
	foreach ( $images as $i => $img ) {
		$alt   = is_array( $img ) ? $img[1] : 'Slide ' . ( $i + 1 );
		$file  = is_array( $img ) ? $img[0] : $img;
		$html .= '<div><img src="{{img:' . $file . '}}" alt="' . esc_attr( $alt ) . '" /></div>';
	}
	return $html . '</div>';
}

/** "YouTubeVideo" component. */
function gl_html_youtube( $id ) {
	return '<div class="gl-video"><iframe width="100%" height="315" src="https://www.youtube.com/embed/' . esc_attr( $id ) . '" allowfullscreen=""></iframe><h4></h4></div>';
}

/** Paragraph list helper. */
function gl_html_ps( array $lines ) {
	return '<p>' . implode( '</p><p>', $lines ) . '</p>';
}

/** Standard page top row: box on the left, slider on the right. */
function gl_top_row_with_slider( $box_title, $box_content, array $slides ) {
	return gl_d5_row(
		'Top',
		'gl-row gl-row--top',
		'1_3,2_3',
		array(
			gl_d5_column( '1_3', 'gl-col gl-col--box', array( gl_d5_text( $box_title, gl_html_box( $box_title, $box_content ) ) ) ),
			gl_d5_column( '2_3', 'gl-col gl-col--slider', array( gl_d5_text( 'Image slider', gl_html_slider( $slides ) ) ) ),
		)
	);
}

/* -------------------------------------------------------------------------
 * Pages
 * ---------------------------------------------------------------------- */

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
	$hero = '<div class="gl-hero" style="background-image:url({{img:stockbarberimage.jpg}})">'
		. '<div class="gl-hero__slogan">WHERE TRADITION MEETS TREND</div>'
		. '<div class="gl-hero__title">GIACOMO LICATA</div>'
		. '<div class="gl-hero__sub">Est. 1986</div>'
		. '<div class="gl-hero__address">374 FILTON AVENUE, HORFIELD, BRISTOL, BS7 0BE</div>'
		. '<div class="gl-hero__address">0117 923 6663</div>'
		. '</div>';

	return array(
		gl_d5_section(
			'Hero',
			'gl-hero-section',
			array( gl_d5_row( 'Hero', 'gl-row gl-row--full', '4_4', array( gl_d5_column( '4_4', 'gl-col', array( gl_d5_text( 'Hero', $hero ) ) ) ) ) )
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

	$help = gl_html_ps(
		array(
			'We do wash hair at Giacomos and there is a small extra price to pay. Clean hair makes cutting and creating your style easier for us and gives you a better finish to your cut. If you do not want a wash we kindly ask that you wash any gel, wax or spray from your hair before you visit us.',
			'When washing the wax from your hair put the shampoo on your hair before you get it wet, get a good lather and then wash as normal.',
		)
	);
	$customer = gl_html_ps( array( 'If you need a particular job done we are full of useful information. We talk to our clients and know their backgrounds, from local tradesmen to financial advisors. That leaky pipe, decorating, garden renovations&#8230; the list is endless. Just ask!' ) );
	$styles   = gl_html_ps( array( 'It may be the traditional gents short back and sides, flat top, mohawk, wedge, boston, spikey, shaved pattern, long layered cut, short layered cut or something else unique to you, whatever your style we can be of service!' ) );

	return array(
		gl_d5_section(
			'About',
			'gl-page',
			array(
				gl_top_row_with_slider( 'Opening Times', $opening, array( 'aboutimage1.jpg', 'aboutimage2.jpg' ) ),
				gl_d5_row(
					'Info boxes',
					'gl-row gl-row--three',
					'1_3,1_3,1_3',
					array(
						gl_d5_column( '1_3', 'gl-col gl-col--box', array( gl_d5_text( 'Help us help you', gl_html_box( 'Help us help you', $help, 'hair-wash.jpg' ) ) ) ),
						gl_d5_column( '1_3', 'gl-col gl-col--box', array( gl_d5_text( 'Helping the Customer', gl_html_box( 'Helping the Customer', $customer, 'helping-customer.jpg' ) ) ) ),
						gl_d5_column( '1_3', 'gl-col gl-col--box', array( gl_d5_text( 'Styles', gl_html_box( 'Styles', $styles, 'styles.jpg' ) ) ) ),
					)
				),
				gl_d5_row(
					'Videos',
					'gl-row gl-row--videos',
					'1_2,1_2',
					array(
						gl_d5_column( '1_2', 'gl-col gl-col--box', array( gl_d5_code( 'Video: I know what you did this summer', gl_html_box( 'I know what you did this summer', gl_html_youtube( '_4s2QAaKvlo' ) ) ) ) ),
						gl_d5_column( '1_2', 'gl-col gl-col--box', array( gl_d5_code( 'Video: Barber Shop Bristol', gl_html_box( 'Giacomo Licata Barber Shop Bristol', gl_html_youtube( '8tXIz-b66aY' ) ) ) ) ),
					)
				),
			)
		),
	);
}

function gl_page_prices() {
	$prices = '<div>' . gl_html_ps(
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
		)
	) . '<p class="gl-bold">Ear Piercing</p>' . gl_html_ps(
		array(
			'1 Ear - &#163;20.00',
			'2 Ears - &#163;35.00',
		)
	) . '<p class="gl-bold">CASH ONLY</p>' . gl_html_ps(
		array(
			'All prices are subject to change depending on length &amp; style required',
			'*OAP haircuts Mon - Thurs',
			'**Cut throat shaves are by appointment only please',
		)
	) . '</div>';

	$services = gl_html_ps(
		array(
			'As well as gentlemen\'s haircuts we are pleased to offer ear piercing for men and women, or when England play football why not have St Georges cross painted on your head, but don\'t worry, it will wash off!',
			'A wide range of products are available including studs for your ears, and if something\'s not in stock don\'t be afraid to ask, we may be able to order it in for you or tell you where you can purchase it.',
			'Come and see us for yourself, enjoy our warm &amp; friendly atmosphere and tell a friend!',
		)
	);
	$special  = gl_html_ps( array( 'Special bookings can be made for weddings, stag do\'s, birthday\'s and special occassions. For more information contact Giacomo on 0117 923 6663.' ) );

	return array(
		gl_d5_section(
			'Prices',
			'gl-page',
			array(
				gl_top_row_with_slider(
					'Aftershave &amp; Toiletries',
					'<p>We now stock a wide range of Proraso Aftershare &amp; Toiletries, please click here for more information</p>',
					array( 'pricesImage1.png', 'pricesImage2.png' )
				),
				gl_d5_row(
					'Prices & services',
					'gl-row gl-row--bottom',
					'1_2,1_2',
					array(
						gl_d5_column( '1_2', 'gl-col gl-col--box', array( gl_d5_text( 'Prices', gl_html_box( 'Prices', $prices ) ) ) ),
						gl_d5_column(
							'1_2',
							'gl-col gl-col--stack',
							array(
								gl_d5_text( 'Services', gl_html_box( 'Services', $services ) ),
								gl_d5_text( 'Special Occasions', gl_html_box( 'Special Occasions', $special ) ),
							)
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

	$grid = '<div class="gl-products">';
	foreach ( $products as $i => $p ) {
		$grid .= '<div class="gl-product"><img src="{{img:' . $p[0] . '}}" alt="Product ' . ( $i + 1 ) . '" /><h3>' . $p[1] . '</h3><p>' . $p[2] . '</p></div>';
	}
	$grid .= '</div>';

	return array(
		gl_d5_section(
			'Products',
			'gl-page',
			array(
				gl_top_row_with_slider(
					'Our Premium Products',
					'<p>Explore our range of premium products including aftershaves, toiletries, and more. Click on any product to learn more.</p>',
					array( array( 'productsImage1.png', 'Proraso' ), array( 'redone2-bckgroundremoved.png', 'Product 2' ) )
				),
				gl_d5_row(
					'Products grid',
					'gl-row gl-row--full',
					'4_4',
					array(
						gl_d5_column(
							'4_4',
							'gl-col',
							array(
								gl_d5_text( 'Heading', '<h2 class="gl-section-title gl-section-title--first">OUR PRODUCTS</h2>' ),
								gl_d5_text( 'Products', $grid ),
							)
						),
					)
				),
			)
		),
	);
}

function gl_page_location() {
	$address = '<div>' . gl_html_ps( array( '374 Filton Avenue, Horfield, Bristol BS7 0BE', 'Telephone: 0117 923 6663' ) ) . '</div>'
		. '<div><h2>Opening Times</h2>' . gl_html_ps( array( 'Mon-Fri: 9am - 7pm', 'Sat: 9am - 5pm', 'Sun: Closed' ) ) . '</div>';

	$image = '<img src="{{img:outside-shop.png}}" alt="Barbershop" class="gl-location__image" />';

	$map = '<div class="gl-map"><h1>FIND US ON MAPS</h1><iframe class="gl-map__iframe" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2483.999317481905!2d-2.576987623075407!3d51.49488001161741!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x487191dd79204213%3A0xc3b41a8c31805052!2sGiacomo%20Licata!5e0!3m2!1sen!2suk!4v1720784502194!5m2!1sen!2suk" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe></div>';

	return array(
		gl_d5_section(
			'Location',
			'gl-page',
			array(
				gl_d5_row(
					'Top',
					'gl-row gl-row--top',
					'1_2,1_2',
					array(
						gl_d5_column( '1_2', 'gl-col gl-col--box', array( gl_d5_text( 'Address', gl_html_box( 'Address', $address ) ) ) ),
						gl_d5_column( '1_2', 'gl-col gl-col--box', array( gl_d5_text( 'Shop photo', gl_html_box( '', $image ) ) ) ),
					)
				),
				gl_d5_row( 'Map', 'gl-row gl-row--full', '4_4', array( gl_d5_column( '4_4', 'gl-col', array( gl_d5_code( 'Google map', $map ) ) ) ) ),
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

	$grid = '<div class="gl-team">';
	foreach ( $staff as $s ) {
		$grid .= '<div class="gl-team__item"><img src="{{img:' . $s[2] . '}}" alt="' . $s[0] . '" /><div class="gl-overlay"><h3>' . $s[1] . '</h3><p>' . $s[0] . '</p></div></div>';
	}
	$grid .= '</div>';

	return array(
		gl_d5_section(
			'Profiles',
			'gl-page',
			array(
				gl_top_row_with_slider(
					'Aftershave &amp; Toiletries',
					'<p>We now stock a wide range of Proraso Aftershave &amp; Toiletries. Please click here for more information.</p>',
					array( 'profileImage1.png', 'profileImage2.png' )
				),
				gl_d5_row(
					'Meet the team',
					'gl-row gl-row--full',
					'4_4',
					array(
						gl_d5_column(
							'4_4',
							'gl-col',
							array(
								gl_d5_text( 'Heading', '<h1 class="gl-team-title">MEET THE TEAM</h1>' ),
								gl_d5_text( 'Team', $grid ),
							)
						),
					)
				),
			)
		),
	);
}

/** Lightbox gallery grid used on the News page. */
function gl_html_gallery( array $items ) {
	$html = '<div class="gl-gallery">';
	foreach ( $items as $i => $item ) {
		$html .= '<a class="gl-gallery__item" href="{{img:' . $item[1] . '}}" data-gl-lightbox="1">'
			. '<img src="{{img:' . $item[0] . '}}" alt="' . esc_attr( $item[3] . ' ' . ( $i + 1 ) ) . '" />'
			. '<div class="gl-overlay"><h3>' . $item[2] . '</h3></div></a>';
	}
	return $html . '</div>';
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
	foreach ( $news as &$n ) {
		$n[] = 'News';
	}
	unset( $n );

	$collection = array(
		array( 'collection1.jpg', 'collection1.jpg', 'Giacomo\'s Collection' ),
		array( 'collection2.jpg', 'collection2.jpg', 'Shirt signed by the Scottish Rugby Team' ),
		array( 'collection3.jpg', 'collection3.jpg', 'Shirt signed by the Irish Rugby Team' ),
		array( 'collection4.jpg', 'collection4.jpg', 'Shirt signed by the English Rugby Team' ),
		array( 'collection5.jpg', 'collection5.jpg', 'Shirt signed by the Italian Rugby Team' ),
		array( 'collection6.jpg', 'collection6.jpg', 'Shirt signed by the Welsh Rugby Team' ),
		array( 'collection7.jpg', 'collection7.jpg', 'Giac &amp; Lee Haskins with his belt' ),
	);
	foreach ( $collection as &$c ) {
		$c[] = 'Collection';
	}
	unset( $c );

	return array(
		gl_d5_section(
			'News',
			'gl-page gl-page--news',
			array(
				gl_d5_row(
					'Top',
					'gl-row gl-row--top',
					'1_3,2_3',
					array(
						gl_d5_column( '1_3', 'gl-col gl-col--box', array( gl_d5_text( 'World record', gl_html_box( 'GIACOMO LICATA SETS WORLD RECORD FOR HIGHEST HAIRCUT', '' ) ) ) ),
						gl_d5_column( '2_3', 'gl-col gl-col--video', array( gl_d5_code( 'World record video', '<div class="gl-videobox">' . gl_html_youtube( '4bVZKPoG5Fc' ) . '</div>' ) ) ),
					)
				),
				gl_d5_row(
					'In the news',
					'gl-row gl-row--full',
					'4_4',
					array(
						gl_d5_column(
							'4_4',
							'gl-col',
							array(
								gl_d5_text( 'Heading: In the news', '<h2 class="gl-section-title gl-section-title--first">IN THE NEWS</h2>' ),
								gl_d5_text( 'News gallery', gl_html_gallery( $news ) ),
								gl_d5_text( 'Heading: Collection', '<h2 class="gl-section-title">GIAC\'S COLLECTION</h2>' ),
								gl_d5_text( 'Collection gallery', gl_html_gallery( $collection ) ),
							)
						),
					)
				),
			)
		),
	);
}

/**
 * Final Divi 5 post_content for a page, with image tokens resolved.
 *
 * @param string   $slug     Page slug.
 * @param callable $resolver fn( string $filename ): string URL.
 */
function gl_build_page_content( $slug, $resolver ) {
	$defs = gl_page_definitions();
	if ( empty( $defs[ $slug ] ) ) {
		return '';
	}
	return gl_resolve_tokens( gl_d5_serialize( call_user_func( $defs[ $slug ]['sections'] ) ), $resolver );
}

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

/* -------------------------------------------------------------------------
 * Divi Theme Builder: Global Header + Global Footer
 * ---------------------------------------------------------------------- */

/**
 * Global header: fixed bar with logo (left) and menu (right).
 * The menu is the [gl_nav] shortcode so it follows Appearance > Menus.
 */
function gl_layout_header() {
	$logo = '<a href="' . esc_url( home_url( '/' ) ) . '" class="gl-header__logo-link"><img src="{{img:giac-logo.png}}" alt="Giacomo Licata Logo" class="gl-header__logo" /></a>';

	return array(
		gl_d5_section(
			'Header',
			'gl-header gl-tb-header',
			array(
				gl_d5_row(
					'Header bar',
					'gl-header__bar',
					'1_2,1_2',
					array(
						gl_d5_column( '1_2', 'gl-tb-col gl-tb-col--logo', array( gl_d5_text( 'Logo', $logo ) ) ),
						gl_d5_column( '1_2', 'gl-tb-col gl-tb-col--nav', array( gl_d5_code( 'Menu (Appearance > Menus)', '[gl_nav type="header"]' ) ) ),
					)
				),
			)
		),
	);
}

/**
 * Global footer: credit line, then logo / menu / social icons bar.
 */
function gl_layout_footer() {
	$social = '<div class="gl-social">';
	foreach ( gl_social_links() as $s ) {
		$social .= '<a href="' . esc_url( $s['url'] ) . '"><img src="{{theme:' . $s['icon'] . '}}" alt="' . esc_attr( $s['label'] ) . '" /></a>';
	}
	$social .= '</div>';

	return array(
		gl_d5_section(
			'Footer',
			'gl-tb-footer',
			array(
				gl_d5_row(
					'Credit',
					'gl-credit-row',
					'4_4',
					array( gl_d5_column( '4_4', 'gl-tb-col', array( gl_d5_text( 'Background credit', '<p class="gl-credit">Background by rawpixel.com on Freepik</p>' ) ) ) )
				),
				gl_d5_row(
					'Footer bar',
					'gl-footer',
					'1_3,1_3,1_3',
					array(
						gl_d5_column( '1_3', 'gl-footer__section', array( gl_d5_text( 'Footer logo', '<img src="{{img:logo.png}}" alt="Footer Logo" class="gl-footer__logo" />' ) ) ),
						gl_d5_column( '1_3', 'gl-footer__section', array( gl_d5_code( 'Footer menu (Appearance > Menus)', '[gl_nav type="footer"]' ) ) ),
						gl_d5_column( '1_3', 'gl-footer__section', array( gl_d5_text( 'Social icons', $social ) ) ),
					)
				),
			)
		),
	);
}

/**
 * Final Divi 5 content for the header ("header") or footer ("footer") layout.
 */
function gl_build_layout_content( $which, $resolver ) {
	$sections = 'footer' === $which ? gl_layout_footer() : gl_layout_header();
	return gl_resolve_tokens( gl_d5_serialize( $sections ), $resolver );
}
