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

	jQuery(function () {
		// jQueryUI dialog element.
		$('body').append('<div id="dialog-modal"></div>');

		GigyaWp.logout = function (response) {
			jQuery.post(gigyaParams.ajaxurl, {action: 'gigya_logout'}, function (response) {
				window.location.reload();
			});
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
			/* Sets redirect after login page */
			if (typeof gigyaLoginParams !== 'undefined') {
				redirectTarget = gigyaLoginParams.redirect;
			}
			else if (typeof gigyaRaasParams !== 'undefined') {
				redirectTarget = gigyaRaasParams.redirect;
			}
		}
		else {
			/* Sets self-redirect (refresh) */
			redirectTarget = window.location.href;
		}

		/* This part relies on a global variable called sendSetSSOToken, which is not part of the connector's code base. It needs to be set in an outside script.
		 * This was done in order to allow to add logic to this variable from the outside, ideally in Gigya's global configuration, which can be set in the connector's UI. */
		if (typeof sendSetSSOToken === 'undefined' || sendSetSSOToken === false)
			location.replace(redirectTarget);
		else if (sendSetSSOToken === true)
			gigya.setSSOToken({redirectURL: redirectTarget});
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
