<?php
/**
 * Site footer – credit line, logo, footer menu and social icons.
 *
 * @package GiacomoLicata
 */

defined( 'ABSPATH' ) || exit;
?>
		<p class="gl-credit"><?php esc_html_e( 'Background by rawpixel.com on Freepik', 'giacomo-licata' ); ?></p>
		<footer class="gl-footer">
			<div class="gl-footer__section">
				<img src="<?php echo esc_url( GL_THEME_URI . '/assets/images/logo.png' ); ?>" alt="<?php esc_attr_e( 'Footer Logo', 'giacomo-licata' ); ?>" class="gl-footer__logo" />
			</div>
			<div class="gl-footer__section">
				<nav class="gl-footer__nav" aria-label="<?php esc_attr_e( 'Footer menu', 'giacomo-licata' ); ?>">
					<?php foreach ( gl_menu_items( 'gl-footer' ) as $item ) : ?>
						<a class="gl-footer-link" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>
			<div class="gl-footer__section">
				<div class="gl-social">
					<?php foreach ( gl_social_links() as $social ) : ?>
						<a href="<?php echo esc_url( $social['url'] ); ?>"><img src="<?php echo esc_url( GL_THEME_URI . '/assets/images/' . $social['icon'] ); ?>" alt="<?php echo esc_attr( $social['label'] ); ?>" /></a>
					<?php endforeach; ?>
				</div>
			</div>
		</footer>
	</div><!-- #et-main-area -->
</div><!-- #page-container -->

<div class="gl-modal" id="gl-modal" aria-hidden="true">
	<div class="gl-modal__content">
		<img class="gl-modal__image" src="" alt="<?php esc_attr_e( 'Enlarged', 'giacomo-licata' ); ?>" />
		<button type="button" class="gl-modal__close"><?php esc_html_e( 'Close', 'giacomo-licata' ); ?></button>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
