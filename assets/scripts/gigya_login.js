(function ($) {
	var GigyaWp = GigyaWp || {};

	$(document).ready(function () {

// --------------------------------------------------------------------

		// Add an HTML element to attach the Gigya Login UI to.
		$('#registerform, #loginform').append('<div id="gigya-login"></div>');

// --------------------------------------------------------------------

		// Setting Parameters.
		var params = {};
		params.containerID = "gigya-login";

		if (typeof gigyaLoginParams.ui != 'undefined') {
			var paramsUI = {};
			$.each(gigyaLoginParams.ui, function (index, value) {
				paramsUI[index] = value;
			});

			$.extend(params, paramsUI);
		}

		// Attach the Gigya block.
		gigya.socialize.showLoginUI(params);

// --------------------------------------------------------------------

		// Display ConnectUI if necessary.
		if (typeof gigyaLoginParams.connectUI !== 'undefined') {
			gigya.services.socialize.showAddConnectionsUI(gigyaLoginParams.connectUI);
		}
// --------------------------------------------------------------------

		/**
		 * On Social login with Gigya behavior.
		 * @param data
		 */
		GigyaWp.socialLogin = function (data) {

			if (response.provider === 'site') {
				return false;
			}

			var options = {
				url : gigyaLoginParams.ajaxurl,
				type: 'POST',
//			dataType: 'json',
				data: {
					data  : data,
					action: gigyaLoginParams.actionLogin
				}
			};

			$.ajax(options)
					.done(function (res) {
						if (res.success == true) {
							if (typeof res.data != 'undefined' && res.data.type == 'register_form') {
								// The user didn't register, and need more field to fill.
								$('body').append('<div id="dialog-modal"></div>');
								$('#dialog-modal').html(res.data.html);
								$('#dialog-modal').dialog({ modal: true });
							}
							else {
								location.replace(gigyaLoginParams.redirect);
							}
						}
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						console.log(errorThrown);
					});
		}

		/**
		 * Login validator.
		 * @param response
		 * @returns {boolean}
		 */
//		GigyaWp.loginCallback = function (response) {
//			if (response.provider === 'site') {
//				return false;
//			}
//
//			if (( response.user.email.length === 0 ) && ( response.user.isSiteUID !== true )) {
//				var email = prompt("Please fill-in missing details\nEmail:");
//
//				if (email == null) {
//					// User clicked Cancel.
//					gigya.socialize.logout();
//				}
//				else {
//					// User clicked OK.
//					// TODO add validation: empty string, email chars ...
//					response.user.email = email;
//				}
//			}

//			var formData = JSON.parse($('#data-form').text());
//			if (( response.user.email.length === 0 && response.user.isSiteUID !== true ) || formData.type == 'extra_form') {
//				// There email missing , or the user need more field to fill.
//				$('body').append('<div id="dialog-modal"></div>');
//				$('#dialog-modal').html(formData.html);
//				$('#dialog-modal').dialog({ modal: true });
//			}
//			else {
//				All good, let's do it.
//				GigyaWp.socialLogin(response);
//			}
//		}

		/**
		 * On RaaS login with Gigya behavior.
		 * @param data
		 */
		GigyaWp.raasLogin = function (data) {

			if (response.provider === 'site') {
				return false;
			}

			var options = {
				url : gigyaLoginParams.ajaxurl,
				type: 'POST',
//			dataType: 'json',
				data: {
					data  : data,
					action: gigyaLoginParams.actionRaasLogin
				}
			};

			$.ajax(options)
					.done(function (res) {
						if (res.success == true) {
							if (typeof res.data != 'undefined' && res.data.type == 'register_form') {
								// The user didn't register, and need more field to fill.
								$('body').append('<div id="dialog-modal"></div>');
								$('#dialog-modal').html(res.data.html);
								$('#dialog-modal').dialog({ modal: true });
							}
							else {
								location.replace(gigyaLoginParams.redirect);
							}
						}
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						console.log(errorThrown);
					});
		}

		/**
		 * Gigya's event handlers.
		 */
		// Attach event handlers.
		if (typeof GigyaWp.regEvents === 'undefined') {
			if (gigyaLoginParams.loginMode === 'raas') {
				gigya.accounts.addEventHandlers({
					onLogin: GigyaWp.raasLogin
				});
			}
			else {
				gigya.socialize.addEventHandlers({
					onLogin: GigyaWp.socialLogin
				});
			}
			GigyaWp.regEvents = true;
		}

	});

})(jQuery);

