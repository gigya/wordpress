(function ($) {

	$(document).ready(function () {

// --------------------------------------------------------------------

		/**
		 * Get image object.
		 * @returns {{type: string, href: *}}
		 */
		var getImageObj = function () {
			var mediaObj = {type: 'image', href: gigyaShareParams.linkBack};

			// Image source entered manually.
			if ((gigyaShareParams.imageBy === 'url') && (gigyaShareParams.imageUrl !== '')) {
				mediaObj.src = gigyaShareParams.imageUrl;
			}
			// Image source taken from og meta tag.
			else if (typeof $('meta[property="og:image"]') !== 'undefined') {
				mediaObj.src = $('meta[property="og:image"]').attr('content');
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
		var getUserAction = function () {
			var ua = new gigya.services.socialize.UserAction();

//			if (typeof gigyaShareParams.userMessage !== 'undefined') {
//				ua.setUserMessage(gigyaShareParams.userMessage);
//			}

			// Set link back.
			var linkBack = typeof $('meta[property="og:url"]').attr('content') !== 'undefined' ? $('meta[property="og:url"]').attr('content') : gigyaShareParams.linkBack;
			if (typeof linkBack !== 'undefined') {
				ua.setLinkBack(linkBack);
			}

			// Set title.
			var postTitle = typeof $('meta[property="og:title"]').attr('content') !== 'undefined' ? $('meta[property="og:title"]').attr('content') : gigyaShareParams.postTitle;
			if (typeof postTitle !== 'undefined') {
				ua.setTitle(postTitle);
			}

			// Set action link.
			if (typeof postTitle !== 'undefined' && typeof linkBack !== 'undefined') {
				ua.addActionLink(postTitle, linkBack);
			}

			// Set subtitle.
//			if (typeof gigyaShareParams.subtitle !== 'undefined') {
//				ua.setSubtitle(gigyaShareParams.subtitle);
//			}

			// Set the description.
			var postDesc = typeof $('meta[property="og:description"]').attr('content') !== 'undefined' ? $('meta[property="og:description"]').attr('content') : gigyaShareParams.postDesc;
			if (typeof postDesc !== 'undefined') {
				ua.setDescription(postDesc);
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
		var check = $('input:checkbox[name="gigya_share_settings[share_image]"]');
		var textfield = $('input#gigya_share_image_url').parent('.text-field ');
		check.is(':checked') ? textfield.show() : textfield.hide();
		check.change(function () {
			check.is(':checked') ? textfield.show() : textfield.hide();
		})

// --------------------------------------------------------------------

	});
})(jQuery);