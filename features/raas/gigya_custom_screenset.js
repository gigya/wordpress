(function ($) {
	$(function () {

		var insertGigContainerIntoWPContainer = function (container) {
			$('#' + container).append($('<div id= ' + screenSetContainerPrefix + container + '>'));
		}

		var getGigContainer = function (container) {
			return screenSetContainerPrefix + container;
		}

		var removeElementFromScreenSetContainer = function (container) {

			var gigFormJQ = $('#' + screenSetContainerPrefix + container);
			if (gigFormJQ.text().indexOf("error has occurred") > -1) {
				gigFormJQ.remove();

			} else {
				$('#' + container).children().each(function () {
					if ($(this).attr('id') === undefined || $(this).attr('id') !== screenSetContainerPrefix + container) {
						$(this).remove();
					}
				})
			}
			return null;
		}

		var processFieldMapping = function () {
			var options = {
				url: gigyaParams.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					data: {},
					action: 'gigya_process_field_mapping'
				}
			};

			var req = $.ajax(options);
			req.done(function (data) {
				if (data.success) {
					/* Insert field mapping success behavior here */
				}
			});
		};

		$('.gigya-screenset-widget-outer-div').each(function () {
			if ($(this).attr('data-machine-name') !== undefined) {
				var varName = '_gig_' + $(this).attr('data-machine-name');
				var gigyaScreenSetParams = window[varName];

				/**
				 * @var array gigyaScreenSetParams
				 * @property gigyaScreenSetParams.link_id
				 * @property gigyaScreenSetParams.screenset_id
				 * @property gigyaScreenSetParams.mobile_screenset_id
				 * @property gigyaScreenSetParams.container_id
				 * @property gigyaScreenSetParams.is_sync_data
				 */
				if (typeof (gigyaScreenSetParams) !== 'undefined') {
					if (gigyaScreenSetParams.mobile_screenset_id === undefined)
						gigyaScreenSetParams.mobile_screenset_id = gigyaScreenSetParams.screenset_id;

					var screenSetParams = {
						screenSet: gigyaScreenSetParams.screenset_id,
						mobileScreenSet: gigyaScreenSetParams.mobile_screenset_id,
						onerror: function (e) {
							return onScreenSetErrorHandler(e, false)
						},
						include: 'id_token'
					};

					if (gigyaScreenSetParams.is_sync_data) {
						screenSetParams['onAfterSubmit'] = processFieldMapping;
					}

					$('#' + gigyaScreenSetParams.link_id).on('click', function (e) {
						e.preventDefault();
						gigya.accounts.showScreenSet(screenSetParams);
					});

					if (gigyaScreenSetParams.type === 'embed') {
						insertGigContainerIntoWPContainer(gigyaScreenSetParams.container_id);
						screenSetParams['containerID'] = getGigContainer(gigyaScreenSetParams.container_id);
						screenSetParams['onerror'] = function (e) {
							return onScreenSetErrorHandler(e, true)
						};

						gigya.accounts.showScreenSet(screenSetParams);
						$('#' + getGigContainer(gigyaScreenSetParams.container_id)).bind('DOMSubtreeModified', removeElementFromScreenSetContainer(gigyaScreenSetParams.container_id));
					}
				}
			}
		});

		var onScreenSetErrorHandler = function (eventObj, isEmbedCase) {
			if (isEmbedCase) {
				$('#' + eventObj.response.requestParams.containerID).remove();
			}

			var screen = eventObj.response.info.screen || 'unknown';
			var errorMessage = 'Error returned by screen-set: screen ' + screen + ': ' + eventObj.errorCode + " – " + eventObj.errorMessage;
			console.log('Error when loading SAP Customer Data Cloud screenset: ');
			console.log(eventObj.errorCode + " – " + eventObj.errorMessage);

			var options = {
				url: gigyaParams.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					data: errorMessage,
					action: 'screen_set_error'
				}
			};
			$.ajax(options);
		};
	});
})(jQuery);