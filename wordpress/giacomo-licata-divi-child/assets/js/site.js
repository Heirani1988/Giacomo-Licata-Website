/**
 * Giacomo Licata – front-end behaviour ported from the original React site:
 *  - header background turns #242424 after scrolling 50px
 *  - image sliders (slick, same settings as react-slick on the old site)
 *  - News page lightbox
 */
(function ($) {
	'use strict';

	// Header scroll state.
	// Coded header (#gl-header) or Divi Theme Builder header section (.gl-header).
	var header = document.querySelector('.gl-header');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 50);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	// Sliders.
	function initSliders(context) {
		if (!$.fn.slick) {
			return;
		}
		$(context || document).find('.gl-slider').not('.slick-initialized').each(function () {
			// Same slide wrapper DOM as react-slick on the original site.
			$(this).children().each(function () {
				$(this).wrapInner('<div><div class="gl-slide-inner" style="width:100%;display:inline-block"></div></div>');
			});
			$(this).slick({
				dots: true,
				infinite: true,
				speed: 500,
				slidesToShow: 1,
				slidesToScroll: 1,
				autoplay: false,
				pauseOnHover: true,
				arrows: true
			});
		});
	}
	$(function () { initSliders(); });
	// Re-measure once images have loaded (slide heights depend on them).
	$(window).on('load', function () {
		$('.gl-slider.slick-initialized').slick('setPosition');
	});

	// Lightbox (News page).
	var modal = document.getElementById('gl-modal');
	if (modal) {
		var modalImg = modal.querySelector('.gl-modal__image');
		var close = function () {
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
			modalImg.setAttribute('src', '');
		};
		$(document).on('click', '[data-gl-lightbox]', function (e) {
			e.preventDefault();
			modalImg.setAttribute('src', this.getAttribute('href'));
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
		});
		modal.addEventListener('click', close);
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { close(); }
		});
	}
})(jQuery);
