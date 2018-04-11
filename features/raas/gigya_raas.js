(function ($) {

	$(document).ready(function () {

// --------------------------------------------------------------------

		/**
		 * Override default WP links to use Gigya's RaaS behavior.
		 */

		/**
		 * @class    gigya.accounts
		 * @function    gigya.accounts.showScreenSet
		 * @function    gigya.accounts.addEventHandlers
		 */
		/**
		 * @class    gigyaParams
		 * @property    {String}    ajaxurl
		 */
		/**
		 * @class    gigyaRaasParams
		 * @property    actionRaas
		 * @property    canEditUsers
		 * @property    raasLoginDiv
		 * @property    raasLoginScreen
		 * @property    raasMobileScreen
		 * @property    raasOverrideLinks
		 * @property    raasProfileDiv
		 * @property    raasProfileMobileScreen
		 * @property    raasProfileWebScreen
		 * @property    raasRegisterDiv
		 * @property    raasRegisterScreen
		 * @property    raasWebScreen
		 */

		var raasLogout = function () {
			gigya.accounts.logout({
				callback: function (e) {
					location.replace(gigyaParams.logoutUrl)
				}
			});
		};

		var overrideLinks = function () {
			$(document).on('click', 'a[href]', function (e) {
				/** @function    gigya.accounts.showScreenSet */
				var path = $(this)[0].pathname;
				var search = $(this)[0].search;
				if (path.indexOf('wp-login.php') !== -1) {

					switch (true) {

						case (search === ''):
							// Login page
							gigya.accounts.showScreenSet({
								screenSet: gigyaRaasParams.raasWebScreen,
								mobileScreenSet: gigyaRaasParams.raasMobileScreen,
								startScreen: gigyaRaasParams.raasLoginScreen
							});
							e.preventDefault();
							break;

						case (search === '?action=register'):
							// Register page
							gigya.accounts.showScreenSet({
								screenSet: gigyaRaasParams.raasWebScreen,
								mobileScreenSet: gigyaRaasParams.raasMobileScreen,
								startScreen: gigyaRaasParams.raasRegisterScreen
							});
							e.preventDefault();
							break;

						case (search === '?action=lostpassword'):
							// Lost Password page
							e.preventDefault();
							break;

						case (search.indexOf('?action=logout') !== -1):
							//Logout
							raasLogout();
							break;
					}
				}
				else if (path.indexOf('profile.php') !== -1 && gigyaRaasParams.canEditUsers !== 1) {
					/* Profile page */
					gigya.accounts.showScreenSet({
						screenSet: gigyaRaasParams.raasProfileWebScreen,
						mobileScreenSet: gigyaRaasParams.raasProfileMobileScreen,
						onAfterSubmit: raasUpdatedProfile
					});
					e.preventDefault();
				}
			});

			/* Hide the WP login screens navigation */
			$('#login').find('#nav').hide();
		};

// --------------------------------------------------------------------

		var raasInit = function () {
			/* Override default WP links to use Gigya's RaaS behavior */
			if (gigyaRaasParams.raasOverrideLinks > 0) {
				overrideLinks();
			}

			/* Get admin=true cookie */
			var admin = false;
			var name = "gigya_admin=true";
			var ca = document.cookie.split(';');
			for (var i = 0; i < ca.length; i++) {
				var c = ca[i].trim();
				if (c.indexOf(name) === 0 && location.pathname.indexOf('wp-login.php') !== -1) {
					admin = true;
				}
			}

			/* Embed Screens */
			/* Note:
			 * If there is a reason to access the default WordPress profile page for the administrator, replace the following line with this one:
			 * if (location.search.indexOf('admin=true') === -1 && !admin) {
			 */
			if (location.search.indexOf('admin=true') === -1) {
				gigya.accounts.showScreenSet({
					screenSet: gigyaRaasParams.raasWebScreen,
					mobileScreenSet: gigyaRaasParams.raasMobileScreen,
					startScreen: gigyaRaasParams.raasLoginScreen,
					containerID: gigyaRaasParams.raasLoginDiv
				});
				gigya.accounts.showScreenSet({
					screenSet: gigyaRaasParams.raasWebScreen,
					mobileScreenSet: gigyaRaasParams.raasMobileScreen,
					startScreen: gigyaRaasParams.raasRegisterScreen,
					containerID: gigyaRaasParams.raasRegisterDiv
				});

				if (gigyaRaasParams.canEditUsers !== 1) {
					gigya.accounts.showScreenSet({
						screenSet: gigyaRaasParams.raasProfileWebScreen,
						mobileScreenSet: gigyaRaasParams.raasProfileMobileScreen,
						containerID: gigyaRaasParams.raasProfileDiv,
						onAfterSubmit: raasUpdatedProfile
					});
				}
			}
			else {
				/* Set admin=true cookie */
				var d = new Date();
				d.setTime(d.getTime() + (60 * 60 * 1000));
				var expires = "; expires=" + d.toUTCString();
				document.cookie = "gigya_admin=true" + expires;
			}

			/* Attach event handlers */
			if (typeof GigyaWp.regEvents === 'undefined') {
				/* Raas Login */
				gigya.accounts.addEventHandlers({
					onLogin: raasLogin,
					onLogout: GigyaWp.logout
				});

				GigyaWp.regEvents = true;
			}
		};

		var raasUpdatedProfile = function (res) {
			var esData = GigyaWp.getEssentialParams(res);
			var options = {
				url: gigyaParams.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					data: esData,
					action: 'raas_update_profile'
				}
			};
			var req = $.ajax(options);
		};
// --------------------------------------------------------------------

		/**
		 * On RaaS login with Gigya behavior.
		 * @param    response                object
		 * @param    response.provider        string    Login service provider, such as "googleplus" etc., or native RaaS ("")
		 * @param    response.UID            string    User's UID
		 * @param    response.UIDSignature    string    User's API signature which is calculated using the secret key and other parameters
		 */
		var raasLogin = function (response) {
			var exp_timestamp = 0;
			if (typeof response.expires_in !== 'undefined')
			{
				exp_timestamp = Date.now() + (response.expires_in * 1000);
			}

			if (response.provider === 'site') {
				return false;
			}

			/* Gigya temp user */
			if (typeof response.UID === 'undefined' || response.UID.indexOf('_temp_') === 0) {
				return false;
			}

			var options = {
				url: gigyaParams.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					data: response,
					action: gigyaRaasParams.actionRaas
				}
			};

			var req = $.ajax(options);
			$('body').prepend('<span class="spinner"></span>');
			$('.spinner').show();

			req.done(function (res) {
				if (res.success) {
					if (typeof response.expires_in !== 'undefined') {
                        var reqFixedSession = $.ajax({
                            url: gigyaParams.ajaxurl,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'fixed_session_cookie',
                                expiration: exp_timestamp
                            }
                        });
                        reqFixedSession.done(function(responseFixedSession) {
                            GigyaWp.redirect();
						})
					} else {
                        GigyaWp.redirect();
					}
				}
				else {
					if (typeof res.data !== 'undefined') {
						/* The user didn't log in */
						var dialog_modal = $('#dialog-modal');
						dialog_modal.html(res.data.msg);
						dialog_modal.dialog({modal: true});
					}
					gigya.accounts.logout({
						callback: function (e) {
							location.replace(gigyaParams.logoutUrl)
						}
					});
				}
			});

			req.fail(function (jqXHR, textStatus, errorThrown) {
				console.log(errorThrown);
			});

			$("#dialog-modal").on("dialogclose", function () {
				location.reload();
			});
		};
// --------------------------------------------------------------------

		raasInit();

	});
})(jQuery);

