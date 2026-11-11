/**
 * Giacomo Licata – small behaviours Divi has no setting for:
 *  - header turns #242424 after scrolling 50px (Theme Builder or fallback header)
 *  - current page highlighted in the header menu
 *  - News page lightbox
 */
(function () {
	'use strict';

	// Header scroll state.
	var header = document.querySelector('.gl-header');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 50);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	// Current page in the (Theme Builder) header menu.
	var norm = function (url) {
		try {
			var u = new URL(url, window.location.href);
			return (u.origin + u.pathname).replace(/\/+$/, '');
		} catch (e) {
			return url;
		}
	};
	var here = norm(window.location.href);
	document.querySelectorAll('.gl-nav-item a, a.gl-nav-item').forEach(function (a) {
		if (norm(a.href) === here) {
			a.classList.add('gl-active');
			var item = a.closest('.gl-nav-item');
			if (item) {
				item.classList.add('gl-active');
			}
		}
	});

	// Lightbox (News page): clicking a card opens its linked large image.
	var modal = document.getElementById('gl-modal');
	if (modal) {
		var img = modal.querySelector('.gl-modal__image');
		var close = function () {
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
			img.setAttribute('src', '');
		};
		document.addEventListener('click', function (e) {
			var card = e.target.closest('.gl-hover-card, [data-gl-lightbox]');
			if (!card) {
				return;
			}
			var link = card.matches('a') ? card : card.querySelector('a.gl-lightbox, a[data-gl-lightbox]');
			if (!link) {
				return;
			}
			e.preventDefault();
			img.setAttribute('src', link.getAttribute('href'));
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
		});
		modal.addEventListener('click', close);
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				close();
			}
		});
	}
})();
