(function ($) {

	$(document).ready(function () {

// --------------------------------------------------------------------

		/**
		 * Override default WP links to use Gigya's RaaS behavior.
		 */
		var overrideLinks = function () {
			$(document).on('click', 'a[href]', function (e) {
				var path = $(this)[0].pathname;
				var search = $(this)[0].search;
				if (path.indexOf('wp-login.php') != -1) {
					switch (search) {

						case '':
							// Login page
							gigya.accounts.showScreenSet({screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasLoginScreen});
							e.preventDefault();
							break;

						case '?action=register':
							// Register page
							gigya.accounts.showScreenSet({screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasRegisterScreen});
							e.preventDefault();
							break;

						case '?action=lostpassword':
							// Lost Password page
							e.preventDefault();
							break;
					}
				}
				else if (path.indexOf('profile.php') != -1) {

					// Profile page
					gigya.accounts.showScreenSet({screenSet: gigyaRaasParams.raasProfileWebScreen, mobileScreenSet: gigyaRaasParams.raasProfileMobileScreen});
					e.preventDefault();
				}
			});

			// Hide the WP login screens navigation.
			$('#login #nav').hide();
		}


// --------------------------------------------------------------------

		// Embed Screens.
		gigya.accounts.showScreenSet({screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasLoginScreen, containerID: gigyaRaasParams.raasLoginDiv});
		gigya.accounts.showScreenSet({screenSet: gigyaRaasParams.raasWebScreen, mobileScreenSet: gigyaRaasParams.raasMobileScreen, startScreen: gigyaRaasParams.raasRegisterScreen, containerID: gigyaRaasParams.raasRegisterDiv});
		gigya.accounts.showScreenSet({screenSet: gigyaRaasParams.raasProfileWebScreen, mobileScreenSet: gigyaRaasParams.raasProfileMobileScreen, containerID: gigyaRaasParams.raasProfileDiv});

// --------------------------------------------------------------------

		/**
		 * On RaaS login with Gigya behavior.
		 * @param data
		 */
		GigyaWp.raasLogin = function (data) {

			if (data.provider === 'site') {
				return false;
			}

			var options = {
				url : gigyaParams.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					data  : data,
					action: gigyaRaasParams.actionRaas
				}
			};

			$.ajax(options)
					.done(function (res) {
						if (res.success == true) {
							if (typeof res.data != 'undefined') {
								// The user didn't register, and need more field to fill.
								$('#dialog-modal').html(res.data.html);
								$('#dialog-modal').dialog({ modal: true });
							}
							else {
								location.replace(gigyaRaasParams.redirect);
							}
						}
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						console.log(errorThrown);
					});
		}

		GigyaWp.raasLogout = function() {

		}

// --------------------------------------------------------------------

		/**
		 * Gigya's event handlers.
		 */
		// Attach event handlers.
		if (typeof GigyaWp.regEvents === 'undefined') {

			// Raas Login.
			gigya.accounts.addEventHandlers({
				onLogin: GigyaWp.raasLogin,
				onLogout: GigyaWp.logout
			});

			GigyaWp.regEvents = true;
		}

// --------------------------------------------------------------------

		// Override default WP links to use Gigya's RaaS behavior.
		if (gigyaRaasParams.raasOverrideLinks > 0) {
			overrideLinks();
		}

// --------------------------------------------------------------------

		// Check Connection to RaaS
//		function getAccountInfoResponse(response) {
//			if (response.errorCode == 0) {
//				var profile = response['profile'];
//				var msg = profile['firstName'] + ' is ' + profile['age'] + ' years old';
//				alert(msg);
//			}
//			else {
//				alert('Error :' + response.errorMessage);
//			}
//		}
//
//		gigya.accounts.getAccountInfo({ callback: getAccountInfoResponse });

	});
})(jQuery);

