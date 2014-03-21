(function ($) {
	$(document).ready(function() {
		// Changing the default look and behavior of the plugin using the 'params' object:
		var params = {

			// The plugin will embed itself inside the "loginDiv" DIV (will not be a popup)
			containerID:"gigya-login",

			// After successful login - the user will be redirected to "https://www.MySite.com/welcome.html" :
			redirectURL: "https://www.MySite.com/welcome.html"
		}


		gigya.socialize.showLoginUI(params);
	});
})(jQuery);s