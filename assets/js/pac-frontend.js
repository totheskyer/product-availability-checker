(function () {
	'use strict';

	function qs(sel, ctx) { return (ctx || document).querySelector(sel); }

	document.addEventListener('DOMContentLoaded', function () {
		var root = qs('[data-pac]');
		if (!root) return;

		var input = qs('#pac-zip-input', root);
		var btn   = qs('#pac-zip-btn', root);
		var out   = qs('#pac-zip-result', root);

		// Add-to-cart button within the product form
		var addToCartBtn = document.querySelector('form.cart button[type="submit"]');

		function setAddToCartDisabled(disabled) {
			if (!addToCartBtn) return;
			addToCartBtn.disabled = !!disabled;
			if (disabled) {
				addToCartBtn.classList.add('pac-disabled');
			} else {
				addToCartBtn.classList.remove('pac-disabled');
			}
		}

		btn && btn.addEventListener('click', function () {
			var zip = (input && input.value || '').trim();
			if (!zip) {
				out && (out.textContent = 'Please enter a ZIP code.');
				return;
			}

			out && (out.textContent = 'Checking...');

			var body = new URLSearchParams();
			body.set('action', 'pac_check_zip');
			body.set('nonce', (PAC && PAC.nonce) || '');
			body.set('zip', zip);

			fetch(PAC.ajax, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString()
			})
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json || !('success' in json)) throw new Error('Bad response');

				if (json.success) {
					var avail = !!(json.data && json.data.available);
					var msg   = (json.data && json.data.message) || (avail ? PAC.msgAvail : PAC.msgUnavail);
					out && (out.textContent = msg);
					setAddToCartDisabled(!avail);
				} else {
					var err = (json.data && json.data.message) || 'Error.';
					out && (out.textContent = err);
					// On error, keep button enabled (safer UX).
				}
			})
			.catch(function () {
				out && (out.textContent = 'Network error. Try again.');
			});
		});
	});
})();
