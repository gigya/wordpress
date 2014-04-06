(function ($) {

	$(document).ready(function () {

// --------------------------------------------------------------------


// --------------------------------------------------------------------

		if (typeof gigyaCommentsParams !== 'undefined') {
//			gigyaCommentsParams.commentsUIparams.onCommentSubmitted = gigyaCommentsParams;
			gigya.comments.showCommentsUI(gigyaCommentsParams);
		}
		else {
			return false;
		}

// --------------------------------------------------------------------

	});
})(jQuery);