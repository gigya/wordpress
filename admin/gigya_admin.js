(function ($) {
	$(document).ready(function () {

		/**
		 * Expose the relevant form element for the login mode selected.
		 * @param $el
		 */
		var userManagementPage = function ($el) {
			if ($el.is(':checked')) {
				if ($el.val() == 'wp_only') {
					$('.social-login-wrapper').addClass('hidden');
					$('.raas-login-wrapper').addClass('hidden');
				}
				else if ($el.val() == 'wp_sl') {
					$('.social-login-wrapper').removeClass('hidden');
					$('.raas-login-wrapper').addClass('hidden');
				}
				else if ($el.val() == 'raas') {
					$('.social-login-wrapper').addClass('hidden');
					$('.raas-login-wrapper').removeClass('hidden');
				}
			}
		}

		// Set user management page at page load.
		$('#gigya_login_mode input').each(function () {
			userManagementPage($(this));
		});

		// Set user management page at modes manually change.
		$('input:radio[name="gigya_login_settings[login_mode]"]').change(function () {
			userManagementPage($(this));
		});

	});
})(jQuery);