(function ($) {

	$(document).ready(function () {

		/**
		 * @class	gigya.socialize
		 */
		/**
		 * @class	gigyaLoginParams
		 * @property	actionCustomLogin
		 * @property	actionLogin
		 * @property	addConnection
		 */

// --------------------------------------------------------------------

		/**
		 * Set the showLoginUI.
		 * @param params
		 */
		var showLoginUI = function (params) {
			params.onError = GigyaWp.errHandle;

			if (typeof params.ui !== 'undefined') {
				var paramsUI = {};
				$.each(params.ui, function (index, value) {
					paramsUI[index] = value;
				});

				$.extend(params, paramsUI);
			}

			// Attach the Gigya block.
			/** @function	gigya.socialize.showLoginUI */
			gigya.socialize.showLoginUI(params);
		};

		/**
		 * Initialize each login widget on page.
		 */
		var showLoginWidget = function () {
			$('.gigya-login-widget').each(function (index) {

				var id = 'gigya-login-widget-' + index;
				$(this).attr('id', id);

				// Get the data.
				var dataEl = $('#' + id).next('script.data-login');
				var params = JSON.parse(dataEl.text());

				// Define the Feed Plugin params object.
				params.containerID = id;
				params.context = {id: id};
				params.onError = GigyaWp.errHandle;
				showLoginUI(params);
			});
		};

		/**
		 * Show Gigya's login block on login/register forms pages.
		 */
		var showLoginDefault = function () {
			// Add an HTML element to attach the Gigya Login UI to.
			$('#registerform, #loginform').after('<div class="gigya-login-or">- Or -</div><div id="gigya-login"></div>');

			// Add the Gigya's social login block to login/register pages.
			// Define the Feed Plugin params object.
			var params = $.extend(true, {}, gigyaLoginParams);
			params.containerID = "gigya-login";
			params.context = {id: 'default'};
			params.onError = GigyaWp.errHandle;

			showLoginUI(params);
		};

// --------------------------------------------------------------------

		/**
		 * Show Gigya's add connections block on profile page.
		 */
		var showAddConnectionsUI = function () {
			// Add 'Add Connections UI' block to the profile page.
			$('form#your-profile').before('<div id="gigya-add-connections"></div>');

			// Setting Parameters.
			var addConnectionsParams = {};
			addConnectionsParams.containerID = "gigya-add-connections";

			if (typeof gigyaLoginParams.addConnection !== 'undefined') {
				var addConnectionsParamsUI = {};
				$.each(gigyaLoginParams.addConnection, function (index, value) {
					addConnectionsParamsUI[index] = value;
				});

				$.extend(addConnectionsParams, addConnectionsParamsUI);
			}

			/**
			 * Attach the Gigya block
			 *
			 * @function	gigya.socialize.showAddConnectionsUI
			 */
			gigya.socialize.showAddConnectionsUI(addConnectionsParams);
		};

// --------------------------------------------------------------------

		/**
		 * On Social login with Gigya behavior.
		 * Send Gigya's response object to server.
		 * @param response
		 */
		var socialLogin = function (response) {

			var options = {
				url: gigyaParams.ajaxurl,
				type: 'POST',
				data: {
					data: response,
					action: gigyaLoginParams.actionLogin
				}
			};
			var req = $.ajax(options);
			$('body').prepend('<span class="spinner"></span>');
			$('.spinner').show();

			req.done(function (res) {
				if (res.success) {
					if (typeof res.data !== 'undefined') {

						// The user didn't register, and need more field to fill.
						$('#dialog-modal').html(res.data.html).dialog({modal: true});
						gigyaDisconnectOnClose(); // if dialog is closed without linking accounts, log user out
					}
					else {
						GigyaWp.redirect();
					}
				}
				else {
					if (typeof res.data !== 'undefined') {
						// Message modal.
						$('#dialog-modal').html(res.data.msg).dialog({modal: true});
					}
					gigya.socialize.logout();
				}
			});

			req.fail(function (jqXHR, textStatus, errorThrown) {
				console.log(errorThrown);
			});
		};

// --------------------------------------------------------------------

		/**
		 * Login validator.
		 * @param response
		 * @param response.provider
		 * @param response.UID
		 * @param response.user
		 * @param response.user.firstName
		 * @param response.user.isSiteUID
		 * @returns {boolean}
		 */
		var loginValidate = function (response) {
			// Came from site.
			if (response.provider === 'site') {
				return false;
			}
			// Gigya temp user.
			if (response.UID.indexOf('_temp_') === 0) {
				return false;
			}

			// We check there is an email field.
			// Only for the first time.
			if (( response.user.email.length === 0 ) && ( response.user.isSiteUID !== true )) {

				// Building a 'get email' form.
				var html =
					'<div class="form-get-email">' +
					'<div class="description">' +
					'Hi ' + response.user.firstName + ', we still need some details from you, please provide your email address.' +
					'<br><br>' +
					'</div>' +
					'<label for="email">Email</label>' +
					'<input type="text" id="get-email" name="email">' +
					'<button type="button" class="button button-get-email">Submit</button>' +
					'</div>';
				var dialog_modal_obj = $('#dialog-modal');

				// Modal with the email form.
				dialog_modal_obj.html(html).dialog({modal: true});

				$(document).on('click', '.button-get-email', function () {
					// The email input.
					var email = $('input#get-email').val();
					// Check it's not empty.
					if (email.length > 0) {
						// When we get a value, we update the user object,
						// And put a flag for 'email not verified'.
						response.user.email = email;
						response.user.email_not_verified = true;
						$('#dialog-modal').dialog("close");

						// Go on with register
						socialLogin(response);
					}

				});

				dialog_modal_obj.on("dialogclose", function (event, ui) {
				});
				$(document).on('click', ".ui-dialog-titlebar-close", function () {
					gigya.socialize.logout();
				})
			}

			else {
				socialLogin(response);
			}

		};

// --------------------------------------------------------------------

		var loginInit = function () {
			showLoginWidget();
			showLoginDefault();
			showAddConnectionsUI();

			// Attach event handlers.
			if (typeof GigyaWp.regEvents === 'undefined') {

				// Social Login.
				gigya.socialize.addEventHandlers({
					onLogin: loginValidate,
					onLogout: GigyaWp.logout
				});

				GigyaWp.regEvents = true;

			}
		};

// --------------------------------------------------------------------

		loginInit();

// --------------------------------------------------------------------

		var customLogin = function (form) {
			var formData = form.serialize();

			var options = {
				type: 'POST',
				url: gigyaParams.ajaxurl,
				data: {
					data: formData,
					action: gigyaLoginParams.actionCustomLogin
				}
			};

			var req = $.ajax(options);

			/**
			 * @param	res
			 * @param	res.data
			 * @param	res.data.msg
			 */
			req.done(function (res) {
				if (res.success) {
					GigyaWp.redirect();
				}
				else {
					if (typeof res.data !== 'undefined') {
						$('.gigya-wp-msg').remove();
						$('#dialog-modal').prepend('<div class="gigya-wp-msg">' + res.data.msg + '</div>');
					}
				}
			});

			req.fail(function (jqXHR) { /* Use status, error function parameters for a more robust response */
				console.log(jqXHR.statusCode());
			});
		};

		// Email verify form.
		$(document).on('click', '#email-verify-form #gigya-submit', function () {
			customLogin($('#email-verify-form'));
		});

		// Link account form.
		$(document).on('click', '#link-accounts-form #gigya-submit', function () {
			customLogin($('#link-accounts-form'));
		});

// --------------------------------------------------------------------

		// When link accounts form (social) is open,
		// If the user is closing the form without entering email, log Gigya user out
		function gigyaDisconnectOnClose() {

			$(".login-action-login button[title='Close']").click(function () {
				gigya.socialize.logout();
			});

			$(document).keydown(function (e) {
				if (e.keyCode === 27) { // ESCAPE key pressed
					gigya.socialize.logout();
				}
			});

		}
	});
})(jQuery);