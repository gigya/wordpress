var GigyaWp = GigyaWp || {};

(function ($) {

	/**
	 * @class gigyaParams
	 * @property	logoutUrl
	 *
	 * @class gigyaLoginParams
	 * @class gigyaRaasParams
	 */

// --------------------------------------------------------------------

	window.__gigyaConf = gigyaParams;

// --------------------------------------------------------------------

	$(document).ready(function () {
		// jQueryUI dialog element.
		$('body').append('<div id="dialog-modal"></div>');

		GigyaWp.logout = function ( response ) {
			/** @function	wp_loginout */
			wp_loginout(gigyaParams.logoutUrl);
		};
	} );

	GigyaWp.userLoggedIn = function (response) {
		GigyaWp.loggedUser = response.user.UID;
		if (GigyaWp.loggedUser.length === 0) {
			location.replace(gigyaParams.logoutUrl);
		}
		return GigyaWp.loggedUser;
	};

// --------------------------------------------------------------------

	GigyaWp.errHandle = function (errEvent) {
		return false;
	};

// --------------------------------------------------------------------

	GigyaWp.redirect = function () {
		var redirectTarget = '';
		if (location.pathname.indexOf('wp-login.php') !== -1) {
			/* Redirect after login page */
			if (typeof gigyaLoginParams !== 'undefined') {
				redirectTarget = gigyaLoginParams.redirect;
			}
			else if (typeof gigyaRaasParams !== 'undefined') {
				redirectTarget = gigyaRaasParams.redirect;
			}
		}
		else {
			/* Refresh */
			redirectTarget = window.location.href;
		}

		if (typeof sendSetSSOToken === 'undefined')
			location.replace(redirectTarget);
		else if (sendSetSSOToken === true)
			gigya.setSSOToken({ redirectURL: redirectTarget });
	};
	GigyaWp.getEssentialParams = function (gigyaObj) {
		var esData = {};
		var primitive = ['string', 'number', 'boolean'];
		$.each(gigyaObj.response, function (key, val) {
			if ($.inArray($.type(val), primitive) >= 0) {
				esData[key] = val;
			}
		});
		return esData;
	};

// --------------------------------------------------------------------

})(jQuery);