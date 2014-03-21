( function ( $ ) {
	var GigyaWp = GigyaWp || {};

// --------------------------------------------------------------------

	gigya.socialize.showLoginUI({

		// The plugin will embed itself inside the "loginDiv" DIV (will not be a popup)
		containerID: "gigya-login",

		// After successful login - the user will be redirected to "https://www.MySite.com/welcome.html" :
		//			redirectURL: "https://www.MySite.com/welcome.html"
	});

// --------------------------------------------------------------------

	GigyaWp.login = function (data) {

		var options = {
			url: gigyaLoginParams.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data:  {
				data: data,
				nonce: gigyaLoginParams.nonce
			}
		};

		$.ajax(options)
				.done(function(data) {
					console.log(data);
				});
	}

	GigyaWp.loginCallback = function ( response ) {
		if ( response.provider === 'site' ) {
			return false;
		}

		if ( ( response.user.email.length === 0 ) && ( response.user.isSiteUID !== true ) ) {
			var email = prompt( "Please fill-in missing details" );

			if ( email == null ) {
				// User clicked Cancel.
				gigya.socialize.logout();
			}
			else {
				// User clicked OK.
				// TODO add validation: empty string, email chars ...
				response.user.email = email;
			}
		}

		GigyaWp.login( response );
	}

	GigyaWp.logoutCallback = function (response) {
		alert(response.eventName + " event happened");
	}

	gigya.socialize.addEventHandlers({
		onLogin : GigyaWp.loginCallback,
		onLogout: GigyaWp.logoutCallback
	});
})(jQuery);

