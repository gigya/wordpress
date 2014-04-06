(function ($) {

	$(document).ready(function () {

// --------------------------------------------------------------------


// --------------------------------------------------------------------

		var showComments = function(id) {
			if (typeof gigyaCommentsParams == 'undefined' || typeof id == 'undefined') {
				return false;
			}

			gigyaCommentsParams.containerID = id;
			gigyaCommentsParams.context = {id: id};
			gigya.comments.showCommentsUI(gigyaCommentsParams);
	}

// --------------------------------------------------------------------

		$('.gigya-comments-widget').each(function (index, value) {
			var id = 'gigya-comments-widget-' + index;
			$(this).attr('id', id);
			showComments(id);
		});

// --------------------------------------------------------------------

	});
})(jQuery);