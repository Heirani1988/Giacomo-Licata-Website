<?php
/**
 * One-click installer: imports images into the Media Library, creates the
 * seven Divi 5 pages, sets the front page, permalinks and menus.
 *
 * Admin:  Appearance > Giacomo Licata Setup
 * WP-CLI: wp gl install [--overwrite] [--skip-theme-builder]
 *
 * @package GiacomoLicata
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin page.
 */
add_action( 'admin_menu', function () {
	add_theme_page(
		__( 'Giacomo Licata Setup', 'giacomo-licata' ),
		__( 'Giacomo Licata Setup', 'giacomo-licata' ),
		'manage_options',
		'gl-setup',
		'gl_render_setup_page'
	);
} );

/**
 * Nudge after activation.
 */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'gl_installed' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_gl-setup' === $screen->id ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p>%s <a class="button button-primary" href="%s">%s</a></p></div>',
		esc_html__( 'Giacomo Licata theme is active. Import the pages, images and menu to finish setting up the site.', 'giacomo-licata' ),
		esc_url( admin_url( 'themes.php?page=gl-setup' ) ),
		esc_html__( 'Run setup', 'giacomo-licata' )
	);
} );

function gl_render_setup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$log = null;
	if ( isset( $_POST['gl_install'] ) ) {
		check_admin_referer( 'gl_install' );
		$log = gl_run_install( ! empty( $_POST['gl_overwrite'] ), ! empty( $_POST['gl_theme_builder'] ) );
	}

	$divi_ok = 'Divi' === get_template();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Giacomo Licata Setup', 'giacomo-licata' ); ?></h1>
		<?php if ( ! $divi_ok ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'The Divi parent theme is required.', 'giacomo-licata' ); ?></p></div>
		<?php endif; ?>

		<?php if ( is_array( $log ) ) : ?>
			<div class="notice notice-success"><p><strong><?php esc_html_e( 'Setup complete.', 'giacomo-licata' ); ?></strong> <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View site', 'giacomo-licata' ); ?></a></p></div>
			<pre style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-height:360px;overflow:auto;"><?php echo esc_html( implode( "\n", $log ) ); ?></pre>
		<?php endif; ?>

		<p><?php esc_html_e( 'This will:', 'giacomo-licata' ); ?></p>
		<ol>
			<li><?php esc_html_e( 'Import the site images into the Media Library (skips ones already imported).', 'giacomo-licata' ); ?></li>
			<li><?php esc_html_e( 'Create the Home, About, Prices, Products, Location, Profiles and News pages as Divi 5 layouts.', 'giacomo-licata' ); ?></li>
			<li><?php esc_html_e( 'Set Home as the front page and use /page-name/ permalinks (so /about, /prices etc. match the old site).', 'giacomo-licata' ); ?></li>
			<li><?php esc_html_e( 'Create the header and footer menu.', 'giacomo-licata' ); ?></li>
			<li><?php esc_html_e( 'Optionally create a Global Header and Global Footer in Divi > Theme Builder, so both can be edited in the Divi Builder.', 'giacomo-licata' ); ?></li>
		</ol>
		<form method="post">
			<?php wp_nonce_field( 'gl_install' ); ?>
			<p><label><input type="checkbox" name="gl_theme_builder" value="1" checked="checked" /> <?php esc_html_e( 'Create the header and footer in the Divi Theme Builder (recommended – editable in the Divi Builder). Unticked, the theme’s built-in header and footer are used.', 'giacomo-licata' ); ?></label></p>
			<p><label><input type="checkbox" name="gl_overwrite" value="1" /> <?php esc_html_e( 'Overwrite pages that already exist with the same slug (also resets any edits made to pages created by this installer).', 'giacomo-licata' ); ?></label></p>
			<p><button type="submit" name="gl_install" value="1" class="button button-primary button-hero" <?php disabled( ! $divi_ok ); ?>><?php esc_html_e( 'Install Giacomo Licata website', 'giacomo-licata' ); ?></button></p>
		</form>
	</div>
	<?php
}

/**
 * Import one bundled image into the Media Library (idempotent).
 *
 * @return int Attachment ID or 0.
 */
function gl_import_image( $filename, &$map, &$log ) {
	if ( ! empty( $map[ $filename ] ) && get_post( $map[ $filename ] ) ) {
		return (int) $map[ $filename ];
	}

	$source = GL_THEME_DIR . '/assets/images/' . $filename;
	if ( ! file_exists( $source ) ) {
		$log[] = "Missing image: {$filename}";
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( $filename );
	copy( $source, $tmp );

	$id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		),
		0,
		pathinfo( $filename, PATHINFO_FILENAME )
	);

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$log[] = "Failed to import {$filename}: " . $id->get_error_message();
		return 0;
	}

	update_post_meta( $id, '_gl_source', $filename );
	$map[ $filename ] = (int) $id;
	$log[]            = "Imported {$filename} (#{$id})";
	return (int) $id;
}

/**
 * Create or update a builder post (page or Theme Builder layout) and flag it as a Divi 5 builder post.
 *
 * @return int Post ID or 0.
 */
function gl_save_builder_post( array $postarr, &$log, $label ) {
	$is_layout = in_array( $postarr['post_type'], array( 'et_header_layout', 'et_body_layout', 'et_footer_layout' ), true );
	if ( ! empty( $postarr['ID'] ) ) {
		$id = wp_update_post( $postarr, true );
	} elseif ( $is_layout && function_exists( 'et_theme_builder_insert_layout' ) ) {
		// Divi's own helper also sets the layout_type term the Theme Builder expects.
		$id = et_theme_builder_insert_layout( $postarr );
	} else {
		$id = wp_insert_post( $postarr, true );
	}
	if ( is_wp_error( $id ) ) {
		$log[] = "Failed to save {$label}: " . $id->get_error_message();
		return 0;
	}
	if ( ! $id ) {
		$log[] = "Failed to save {$label}.";
		return 0;
	}
	update_post_meta( $id, '_et_pb_use_builder', 'on' );
	update_post_meta( $id, '_et_pb_use_divi_5', 'on' );
	update_post_meta( $id, '_et_builder_version', 'VB|Divi|' . gl_d5_version() );
	update_post_meta( $id, '_et_pb_old_content', '' );
	// Divi 5 caches each post's module list to decide which scripts to load; a stale
	// cache can leave modules without their scripts. Divi rebuilds it on next view.
	delete_post_meta( $id, '_divi_dynamic_assets_cached_modules' );
	return (int) $id;
}

/**
 * Divi Theme Builder: create "Giacomo Licata Header/Footer" layouts and assign
 * them to the Default Website Template.
 *
 * Storage (same in Divi 4 and 5): an `et_theme_builder` post lists its
 * templates in `_et_template` meta; each `et_template` post points at its
 * header/body/footer layouts (`et_header_layout` / `et_footer_layout` posts).
 */
function gl_install_theme_builder( $overwrite, $resolver, &$log ) {
	$tb_type     = defined( 'ET_THEME_BUILDER_THEME_BUILDER_POST_TYPE' ) ? ET_THEME_BUILDER_THEME_BUILDER_POST_TYPE : 'et_theme_builder';
	$tpl_type    = defined( 'ET_THEME_BUILDER_TEMPLATE_POST_TYPE' ) ? ET_THEME_BUILDER_TEMPLATE_POST_TYPE : 'et_template';
	$header_type = defined( 'ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE' ) ? ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE : 'et_header_layout';
	$footer_type = defined( 'ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE' ) ? ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE : 'et_footer_layout';

	// 1. The live Theme Builder post.
	$tb_id = 0;
	if ( function_exists( 'et_theme_builder_get_theme_builder_post_id' ) ) {
		$tb_id = (int) et_theme_builder_get_theme_builder_post_id( true, true );
	}
	if ( ! $tb_id ) {
		$found = get_posts(
			array(
				'post_type'      => $tb_type,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);
		$tb_id = $found ? (int) $found[0] : (int) wp_insert_post(
			array(
				'post_type'   => $tb_type,
				'post_status' => 'publish',
				'post_title'  => 'Theme Builder',
			)
		);
	}
	if ( ! $tb_id ) {
		$log[] = 'Theme Builder: could not find or create the Theme Builder post – skipped.';
		return;
	}

	// 2. Its default template.
	$template_id = 0;
	foreach ( (array) get_post_meta( $tb_id, '_et_template' ) as $tid ) {
		if ( get_post( (int) $tid ) && '1' === (string) get_post_meta( (int) $tid, '_et_default', true ) ) {
			$template_id = (int) $tid;
			break;
		}
	}
	if ( ! $template_id ) {
		if ( function_exists( 'et_theme_builder_store_template' ) ) {
			$template_id = (int) et_theme_builder_store_template(
				$tb_id,
				array(
					'id'           => 0,
					'title'        => 'Default Website Template',
					'default'      => '1',
					'enabled'      => '1',
					'layouts'      => array(
						'header' => array( 'id' => 0, 'enabled' => true ),
						'body'   => array( 'id' => 0, 'enabled' => true ),
						'footer' => array( 'id' => 0, 'enabled' => true ),
					),
					'use_on'       => array(),
					'exclude_from' => array(),
				),
				true
			);
		} else {
			$template_id = (int) wp_insert_post(
				array(
					'post_type'   => $tpl_type,
					'post_status' => 'publish',
					'post_title'  => 'Default Website Template',
				)
			);
		}
		if ( ! $template_id ) {
			$log[] = 'Theme Builder: could not create the default template – skipped.';
			return;
		}
		add_post_meta( $tb_id, '_et_template', $template_id );
		update_post_meta( $template_id, '_et_default', '1' );
		update_post_meta( $template_id, '_et_enabled', '1' );
		update_post_meta( $template_id, '_et_body_layout_id', 0 );
		update_post_meta( $template_id, '_et_body_layout_enabled', '1' );
		$log[] = "Theme Builder: created Default Website Template (#{$template_id}).";
	}

	// 3. Header + footer layouts.
	foreach ( array( 'header' => $header_type, 'footer' => $footer_type ) as $which => $type ) {
		$current_id = (int) get_post_meta( $template_id, "_et_{$which}_layout_id", true );
		$current    = $current_id ? get_post( $current_id ) : null;
		$is_ours    = $current && get_post_meta( $current_id, '_gl_layout', true );

		if ( $current && 'trash' !== $current->post_status && ! $overwrite ) {
			$log[] = $is_ours
				? "Theme Builder: kept existing {$which} (tick Overwrite to reset it)."
				: "Theme Builder: the default template already has a {$which} – left unchanged (tick Overwrite to replace it).";
			continue;
		}

		$postarr = array(
			'post_type'    => $type,
			'post_status'  => 'publish',
			'post_title'   => 'header' === $which ? 'Giacomo Licata Header' : 'Giacomo Licata Footer',
			'post_content' => wp_slash( gl_build_layout_content( $which, $resolver ) ),
		);
		if ( $is_ours ) {
			$postarr['ID'] = $current_id;
		}
		$layout_id = gl_save_builder_post( $postarr, $log, "{$which} layout" );
		if ( ! $layout_id ) {
			continue;
		}
		update_post_meta( $layout_id, '_gl_layout', $which );
		delete_post_meta( $layout_id, '_et_theme_builder_marked_as_unused' );

		update_post_meta( $template_id, "_et_{$which}_layout_id", $layout_id );
		update_post_meta( $template_id, "_et_{$which}_layout_enabled", '1' );
		$log[] = 'Theme Builder: ' . ( $is_ours ? 'updated' : 'created' ) . " global {$which} (#{$layout_id}).";
	}

	if ( function_exists( 'et_theme_builder_clear_wp_cache' ) ) {
		et_theme_builder_clear_wp_cache( 'all' );
	}
}

/**
 * Run the full install.
 *
 * @param bool $overwrite     Overwrite existing pages / layouts.
 * @param bool $theme_builder Create the Divi Theme Builder header + footer.
 * @return string[] Log lines.
 */
function gl_run_install( $overwrite = false, $theme_builder = true ) {
	$log = array();

	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}

	// Keep images exactly as on the original site and import quickly.
	add_filter( 'big_image_size_threshold', '__return_false' );
	add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );

	/* 1. Images ----------------------------------------------------------- */
	$map = get_option( 'gl_media_map', array() );
	if ( ! is_array( $map ) ) {
		$map = array();
	}
	$resolver = function ( $filename ) use ( &$map, &$log ) {
		$id = gl_import_image( $filename, $map, $log );
		return $id ? wp_get_attachment_url( $id ) : GL_THEME_URI . '/assets/images/' . $filename;
	};

	/* 2. Pages ------------------------------------------------------------ */
	kses_remove_filters(); // Allow iframes / inline styles in Divi module content.
	$page_ids = array();
	$order    = 0;

	foreach ( gl_page_definitions() as $slug => $def ) {
		$content  = gl_build_page_content( $slug, $resolver );
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		$is_ours  = $existing && get_post_meta( $existing->ID, '_gl_page', true );

		$postarr = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $def['title'],
			'post_name'    => $slug,
			'post_content' => wp_slash( $content ),
			'menu_order'   => $order++,
		);

		if ( $existing && ! $is_ours && ! $overwrite ) {
			$log[]             = "Skipped page '{$slug}' (a page with this slug already exists – tick Overwrite to replace it).";
			$page_ids[ $slug ] = $existing->ID;
			continue;
		}
		if ( $existing && $is_ours && ! $overwrite ) {
			$log[]             = "Kept page '{$slug}' (already installed – tick Overwrite to reset it).";
			$page_ids[ $slug ] = $existing->ID;
			continue;
		}

		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
		}
		$id = gl_save_builder_post( $postarr, $log, "page '{$slug}'" );
		if ( ! $id ) {
			continue;
		}

		update_post_meta( $id, '_gl_page', 1 );
		update_post_meta( $id, '_et_pb_page_layout', 'et_full_width_page' );
		update_post_meta( $id, '_et_pb_side_nav', 'off' );
		update_post_meta( $id, '_et_pb_show_title', 'off' );
		update_post_meta( $id, '_et_pb_built_for_post_type', 'page' );
		update_post_meta( $id, '_wp_page_template', 'default' );

		$page_ids[ $slug ] = (int) $id;
		$log[]             = ( $existing ? 'Updated' : 'Created' ) . " page '{$slug}' (#{$id})";
	}

	/* 2b. Theme Builder header + footer ---------------------------------- */
	if ( $theme_builder ) {
		gl_install_theme_builder( $overwrite, $resolver, $log );
	}
	kses_init_filters();

	update_option( 'gl_media_map', $map, false );

	/* 3. Reading + permalinks -------------------------------------------- */
	if ( ! empty( $page_ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_ids['home'] );
		$log[] = 'Front page set to Home.';
	}
	if ( '' === get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$log[] = 'Permalinks set to /%postname%/.';
	}
	flush_rewrite_rules( false );

	/* 4. Menu ------------------------------------------------------------- */
	$menu_name = 'Giacomo Licata Menu';
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? $menu->term_id : wp_create_nav_menu( $menu_name );

	if ( ! is_wp_error( $menu_id ) ) {
		if ( ! $menu || ! wp_get_nav_menu_items( $menu_id ) ) {
			$pos = 1;
			foreach ( $page_ids as $slug => $pid ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => get_the_title( $pid ),
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $pid,
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $pos++,
					)
				);
			}
			$log[] = "Created menu '{$menu_name}'.";
		}
		$locations                = get_theme_mod( 'nav_menu_locations', array() );
		$locations['gl-primary']  = $menu_id;
		$locations['gl-footer']   = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
		$log[] = 'Menu assigned to header and footer.';
	}

	/* 5. Divi: clear static CSS cache so new pages render cleanly ---------- */
	if ( class_exists( 'ET_Core_PageResource' ) && method_exists( 'ET_Core_PageResource', 'remove_static_resources' ) ) {
		ET_Core_PageResource::remove_static_resources( 'all', 'all' );
		$log[] = 'Cleared Divi static CSS cache.';
	}

	update_option( 'gl_installed', time() );
	return $log;
}

/**
 * WP-CLI: wp gl install [--overwrite] [--skip-theme-builder]
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'gl install',
		function ( $args, $assoc ) {
			foreach ( gl_run_install( ! empty( $assoc['overwrite'] ), empty( $assoc['skip-theme-builder'] ) ) as $line ) {
				WP_CLI::log( $line );
			}
			WP_CLI::success( 'Giacomo Licata site installed.' );
		},
		array( 'shortdesc' => 'Import images, create the Divi 5 pages, front page and menu.' )
	);
}
