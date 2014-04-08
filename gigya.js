var GigyaWp = GigyaWp || {};

(function ($) {

// --------------------------------------------------------------------

  window.__gigyaConf = {
    connectWithoutLoginBehavior: gigyaParams.connectBehavior
  }

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

