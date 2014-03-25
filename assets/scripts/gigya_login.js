(function ($) {
	var GigyaWp = GigyaWp || {};

	$( document ).ready(function() {

// --------------------------------------------------------------------

		// Add an HTML element to attach the Gigya Login UI to.
		$('#registerform, #loginform').append('<div id="gigya-login"></div>');

// --------------------------------------------------------------------

		gigya.socialize.showLoginUI({

			// The plugin will embed itself inside the "loginDiv" DIV (will not be a popup)
			containerID: "gigya-login"

			// After successful login - the user will be redirected to "https://www.MySite.com/welcome.html" :
			//			redirectURL: "https://www.MySite.com/welcome.html"
		});

// --------------------------------------------------------------------

		GigyaWp.login = function (data) {

			var options = {
				url : gigyaLoginParams.ajaxurl,
				type: 'POST',
//			dataType: 'json',
				data: {
					data  : data,
					action: gigyaLoginParams.action
				}
			};

			$.ajax(options)
					.done(function (data) {
						if (data.success == true) {
							if (data.data.type == 'register_form') {
								// The user didn't register, and need more field to fill.
								$('body').append('<div id="dialog-modal"></div>');
								$('#dialog-modal').html(data.data.html);
								$( "#dialog-modal" ).dialog({ modal: true });
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

		GigyaWp.loginCallback = function (response) {
			if (response.provider === 'site') {
				return false;
			}

			if (( response.user.email.length === 0 ) && ( response.user.isSiteUID !== true )) {
				var email = prompt("Please fill-in missing details\nEmail:");

				if (email == null) {
					// User clicked Cancel.
					gigya.socialize.logout();
				}
				else {
					// User clicked OK.
					// TODO add validation: empty string, email chars ...
					response.user.email = email;
				}
			}

			GigyaWp.login(response);
		}

		GigyaWp.logoutCallback = function (response) {
			alert(response.eventName + " event happened");
		}

		gigya.socialize.addEventHandlers({
			onLogin : GigyaWp.loginCallback,
			onLogout: GigyaWp.logoutCallback
		});
	});

})(jQuery);

