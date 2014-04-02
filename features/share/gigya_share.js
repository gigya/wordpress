(function ($) {

	$(document).ready(function () {

// --------------------------------------------------------------------

		/**
		 * Get image object.
		 * @returns {{type: string, href: *}}
		 */
		var getImageObj = function() {
			var mediaObj = {type: 'image', href: gigyaShareParams.linkBack};

			// Image source taken from og meta tag.
			if (gigyaShareParams.imageBy === 'default' && $('meta[property=og:image]').length > 0) {
				mediaObj.src = $('meta[property=og:image]').attr('content');
			}
			// Image source entered manually.
			else if ((gigyaShareParams.imageBy === 'url') && (gigyaShareParams.imageUrl !== '')) {
				mediaObj.src = gigyaShareParams.imageUrl;
			}
			// Image source taken from the first image in post.
			else {
				mediaObj.src = $('#content .entry-content img').eq(0).attr('src') || $('img').eq(0).attr('src');
			}

			return mediaObj;
		}

// --------------------------------------------------------------------

		/**
		 * Get user action.
		 * @returns {gigya.services.socialize.UserAction}
		 */
		var getUserAction = function() {
			var ua = new gigya.services.socialize.UserAction();

//			if (typeof gigyaShareParams.userMessage !== 'undefined') {
//				ua.setUserMessage(gigyaShareParams.userMessage);
//			}

			// Set link back.
			if (typeof gigyaShareParams.linkBack !== 'undefined') {
				ua.setLinkBack(gigyaShareParams.linkBack);
			}

			// Set title.
			if (typeof gigyaShareParams.title !== 'undefined') {
				ua.setTitle(gigyaShareParams.title);
			}

			// Set action link.
			if (typeof gigyaShareParams.title !== 'undefined' && typeof gigyaShareParams.linkBack !== 'undefined') {
				ua.addActionLink(gigyaShareParams.postTitle, gigyaShareParams.linkBack);
			}

			// Set subtitle.
//			if (typeof gigyaShareParams.subtitle !== 'undefined') {
//				ua.setSubtitle(gigyaShareParams.subtitle);
//			}

			// Set the description.
			if (typeof gigyaShareParams.postDesc !== 'undefined') {
				ua.setDescription(gigyaShareParams.postDesc);
			}

			// Set the image.
			ua.addMediaItem(getImageObj());

			return ua;
		}

// --------------------------------------------------------------------

		/**
		 * Show the Gigya's share bar.
		 * @param settings
		 */
		var showShareBar = function () {

			//Define the Share Bar Plugin params object.
			var params = jQuery.extend(true, {}, gigyaShareParams);
			params.userAction = getUserAction();

			//Load the Share Bar Plugin.
			gigya.services.socialize.showShareBarUI(params);

		};

// --------------------------------------------------------------------

		showShareBar();

// --------------------------------------------------------------------

		// Conditional settings image url field.
		var img_url = $('input#gigya_share_image_url').parent('.text-field ');
		img_url.hide();
		$('input:radio[name="gigya_share_settings[share_image]"]').change(function () {
			if ($(this).is(':checked')) {
				$(this).val() == 'url' ? img_url.show() : img_url.hide();
			}
		})

// --------------------------------------------------------------------

	});
})(jQuery);