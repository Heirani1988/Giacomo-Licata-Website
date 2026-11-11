<?php
/**
 * Fallback site footer (used when no Divi Theme Builder footer is assigned):
 * credit line, logo, footer menu and social icons.
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
				<?php echo gl_render_nav( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in gl_render_nav(). ?>
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


<?php wp_footer(); ?>
</body>
</html>
