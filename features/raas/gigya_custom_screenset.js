(function ($) {
	$(function () {
		/**
		 * @var array gigyaScreenSetParams
		 * @property gigyaScreenSetParams.link_id
		 * @property gigyaScreenSetParams.screenset_id
		 * @property gigyaScreenSetParams.mobile_screenset_id
		 * @property gigyaScreenSetParams.container_id
		 */
		if (typeof(gigyaScreenSetParams) !== 'undefined') {
			if (gigyaScreenSetParams.mobile_screenset_id === undefined)
				gigyaScreenSetParams.mobile_screenset_id = gigyaScreenSetParams.screenset_id;

			$('#' + gigyaScreenSetParams.link_id).on('click', function (e) {
				e.preventDefault();

				gigya.accounts.showScreenSet({
					screenSet: gigyaScreenSetParams.screenset_id,
					mobileScreenSet: gigyaScreenSetParams.mobile_screenset_id
				});
			});

			if (gigyaScreenSetParams.type === 'embed') {
				gigya.accounts.showScreenSet({
					screenSet: gigyaScreenSetParams.screenset_id,
					mobileScreenSet: gigyaScreenSetParams.mobile_screenset_id,
					containerID: gigyaScreenSetParams.container_id
				});
			}
		}
	});
})(jQuery);