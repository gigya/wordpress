(function ($) {

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

			var options = {
				url : gigyaParams.ajaxurl,
				type: 'POST',
				data: {
					data  : data,
					action: gigyaLoginParams.actionLogin
				}
			};

			var req = $.ajax(options);

			req.done(function (res) {
				if (res.success == true) {
					if (typeof res.data != 'undefined') {

						// The user didn't register, and need more field to fill.
						$('#dialog-modal').html(res.data.html).dialog({ modal: true });

					}
					else {

						// Redirect.
						location.replace(gigyaLoginParams.redirect);
					}
				}
				else {
					if (typeof res.data != 'undefined') {

						// Message modal.
						$('#dialog-modal').html(res.data.msg).dialog({ modal: true });
					}
				}
			});

			req.fail(function (jqXHR, textStatus, errorThrown) {
				console.log(errorThrown);
			});
		}

// --------------------------------------------------------------------

		/**
		 * Login validator.
		 * @param response
		 * @returns {boolean}
		 */
		GigyaWp.loginValidate = function (response) {
			if (response.provider === 'site') {
				return false;
			}

			// We check there an email field.
			// Only for the first time.
			if (( response.user.email.length === 0 ) && ( response.user.isSiteUID !== true )) {

				// Building an 'get email' form.
				var html =
						'<div class="form-get-email">' +
								'<div class="description">' +
									'Additional information is required in order ' +
									'to complete your registration. ' +
									'Please fill-in your Email' +
									'<br><br>' +
								'</div>' +
								'<label for="email">Email</label>' +
								'<input type="text" id="get-email" name="email">' +
								'<button type="button" class="button button-get-email">Submit</button>' +
						'</div>';

				// Modal with the email form.
				$('#dialog-modal').html(html).dialog({ modal: true });

				$(document).on('click', '.button-get-email', function() {
					var email = $('input#get-email').val();
					if (email.length > 0) {

						// When we get a value, we update the user object,
						// And put a flag for 'email not verified'.
						response.user.email = email;
						response.user.email_not_verified = true;
						$('#dialog-modal').dialog( "close" );

						// Go on with register
						GigyaWp.socialLogin(response);
					}
				})
			}

			else {
				GigyaWp.socialLogin(response);
			}

		}

// --------------------------------------------------------------------

		/**
		 * Gigya's event handlers.
		 */
		// Attach event handlers.
		if (typeof GigyaWp.regEvents === 'undefined') {

			// Social Login.
			gigya.socialize.addEventHandlers({
				onLogin: GigyaWp.loginValidate,
				onLogout: GigyaWp.logout
			});

			GigyaWp.regEvents = true;

		}
	});

// --------------------------------------------------------------------

})(jQuery);

