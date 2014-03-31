var GigyaWp = GigyaWp || {};

(function ($) {

// --------------------------------------------------------------------

	$(document).ready(function () {
		// jQueryUI dialog element.
		$('body').append('<div id="dialog-modal"></div>');

		GigyaWp.logout = function () {
			document.location = gigyaParams.logouUrl;
		}
	});

// --------------------------------------------------------------------

})(jQuery);

