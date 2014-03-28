(function ($) {

	$(document).ready(function () {

// --------------------------------------------------------------------

		$(document).on('click', 'a[href]', function(e) {
			var path = $(this)[0].pathname;
			var search = $(this)[0].search;
			if (path.indexOf('wp-login.php') != -1)	{
				switch (search) {
					case '':

							// Login page
						gigya.accounts.showScreenSet({screenSet:'Login-web', mobileScreenSet:'Login-mobile', startScreen:'gigya-login-screen', containerID:'login-div'});
						e.preventDefault();
						break;

					case '?action=register':

							// Register page
						gigya.accounts.showScreenSet({screenSet:'Login-web', mobileScreenSet:'Login-mobile', startScreen:'gigya-login-screen', containerID:'login-div'});
						e.preventDefault();
						break;
				}
			}
			else if (path.indexOf('profile.php') != -1) {

				// Profile page
				gigya.accounts.showScreenSet({screenSet:'Profile-web', mobileScreenSet:'Profile-mobile', containerID:'edit-account-div'});
				e.preventDefault();
			}
		});

// --------------------------------------------------------------------

//		$('.gigya-raas-login').once('gigya-raas').click( function (e) {
//			e.preventDefault();
//			gigya.accounts.showScreenSet(Drupal.settings.gigya.raas.login);
//			Drupal.settings.gigya.raas.linkId = $(this).attr('id');
//		});
//
//// --------------------------------------------------------------------
//
//		$('.gigya-raas-reg').once('gigya-raas').click( function (e) {
//			e.preventDefault();
//			gigya.accounts.showScreenSet(Drupal.settings.gigya.raas.register);
//			Drupal.settings.gigya.raas.linkId = $(this).attr('id');
//		});
//
//// --------------------------------------------------------------------
//
//		$('.gigya-raas-prof, a:[href="/user"]').once('gigya-raas').click( function (e) {
//			e.preventDefault();
//			gigya.accounts.showScreenSet(Drupal.settings.gigya.raas.profile);
//		});
//
//// --------------------------------------------------------------------
//
//		var loginDiv = $('#gigya-raas-login-div');
//		if (loginDiv.size() > 0 && (typeof Drupal.settings.gigya.raas.login !== 'undefined')) {
//			var id = loginDiv.eq(0).attr('id');
//			Drupal.settings.gigya.raas.login.containerID = id;
//			Drupal.settings.gigya.raas.linkId = id;
//			gigya.accounts.showScreenSet(Drupal.settings.gigya.raas.login);
//		}
//
//// --------------------------------------------------------------------
//
//		var regDiv = $('#gigya-raas-register-div');
//		if (regDiv.size() > 0 && (typeof Drupal.settings.gigya.raas.register !== 'undefined')) {
//			var id = regDiv.eq(0).attr('id');
//			Drupal.settings.gigya.raas.register.containerID = id;
//			Drupal.settings.gigya.raas.linkId = id;
//			gigya.accounts.showScreenSet(Drupal.settings.gigya.raas.register);
//		}

// --------------------------------------------------------------------

		var profDiv = $('#gigya-raas-profile-div');
		if ((profDiv.size() > 0) && (typeof Drupal.settings.gigya.raas.profile !== 'undefined')) {
			Drupal.settings.gigya.raas.profile.containerID = profDiv.eq(0).attr('id');
			gigya.accounts.showScreenSet(Drupal.settings.gigya.raas.profile);
		}

// --------------------------------------------------------------------

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

// --------------------------------------------------------------------

		/**
		 * Gigya's event handlers.
		 */
		// Attach event handlers.
		if (typeof GigyaWp.regEvents === 'undefined') {

			// Raas Login.
			gigya.accounts.addEventHandlers({
				onLogin: GigyaWp.raasLogin
			});

			GigyaWp.regEvents = true;

		}
	});

// --------------------------------------------------------------------

})(jQuery);

