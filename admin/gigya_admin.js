(function ($) {
	$(function () {

// --------------------------------------------------------------------

		/**
		 * Expose the relevant form element for the login mode selected.
		 * @param $el
		 */
		var userManagementPage = function ($el) {
			if ($el.attr("checked") === 'checked') {
				if ($el.val() === 'wp_only') {
					$('.global-login-wrapper').addClass('hidden');
					$('.social-login-wrapper').addClass('hidden');
					$('.raas-login-wrapper').addClass('hidden');
					$('.raas_enabled').find('.gigya-raas-warn').addClass('hidden');
					$('#tab-gigya_field_mapping_settings').addClass('hidden');
				} else if ($el.val() === 'wp_sl') {
					$('.global-login-wrapper').removeClass('hidden');
					$('.social-login-wrapper').removeClass('hidden');
					$('.raas-login-wrapper').addClass('hidden');
					$('.raas_enabled').find('.gigya-raas-warn').removeClass('hidden');
					$('#tab-gigya_field_mapping_settings').removeClass('hidden');
				} else if ($el.val() === 'raas') {
					$('.global-login-wrapper').removeClass('hidden');
					$('.social-login-wrapper').addClass('hidden');
					$('.raas-login-wrapper').removeClass('hidden');
					$('.raas_enabled').find('.gigya-raas-warn').addClass('hidden');
					$('#tab-gigya_field_mapping_settings').removeClass('hidden');
				}
			}
		};

		// Set user management page at page load.
		$('#gigya_mode').find('input').each(function () {
			userManagementPage($(this));
		});

		// Set user management page at modes manually change.
		$('input:radio[name="gigya_login_settings[mode]"]').on('change', function () {
			userManagementPage($(this));
		});

// --------------------------------------------------------------------

		/* Session settings page */

		var sessionManagementPage = function ($select) {
			var sessionDurationElement = $('#gigya_session_duration');
			var sessionTypeNumericElement = $('#session_type_numeric');
			switch ($select.val()) {
				case 'sliding':
					sessionDurationElement.parent().removeClass('hidden');
					sessionTypeNumericElement.val('-1');
					sessionDurationElement.trigger('focus');
					break;
				case 'fixed':
					sessionDurationElement.parent().removeClass('hidden');
					sessionTypeNumericElement.val('1');
					sessionDurationElement.trigger('focus');
					break;
				case 'forever':
					sessionTypeNumericElement.val('-2');
					sessionDurationElement.parent().addClass('hidden');
					break;
				case 'browser_close':
					sessionTypeNumericElement.val('0');
					sessionDurationElement.parent().addClass('hidden');
					break;
			}
		};
		var sessionTypeElement = $('#gigya_session_type');
		sessionTypeElement.each(function () {
			sessionManagementPage($(this));
		});
		sessionTypeElement.on('change', function () {
			sessionManagementPage($(this));
		});

// --------------------------------------------------------------------

		/* Validation functions */

		/**
		 * JSONLint.
		 */
		var jsonValidate = function (textField, e) {
			var json = textField.val();
			if (json.length > 0) {
				$('.msg').remove();
				try {
					var result = jsonlint.parse(json);
					if (result) {
						textField.after('<div class="msg updated">JSON is valid</div>');
						textField.addClass('valid');
					}
				} catch (err) {
					textField.after('<div class="msg error">Error: the text you have entered is not a valid JSON format. JSON Parser error message: ' + err + '</div>');
					textField.addClass('error');
					e.preventDefault();
					e.stopPropagation();
				}
			}
		};

		var emptyNumericValidate = function (textField, e) {
			if ($(textField).val().length === 0 || isNaN($(textField).val())) {
				textField.parent().after('<div class="msg error">Error: This field\'s value must be non-empty and numeric.</div>');
				textField.addClass('error');
				e.preventDefault();
				e.stopPropagation();
			}
		};

		var validateRequired = function (textField, validationType) {
			if (typeof validationType === 'undefined' || validationType === 'required' || validationType === 'true') /* Simple required validation */
				return (textField.val() !== "");
			else if (validationType === 'empty-selection') { /* Validation for requiring either the entire line to be empty, or this textField to be non-empty */
				return (textField.val() !== null);

			}
		};

		/**
		 * @param form The form element object
		 * @param e    The event object
		 *
		 * @return boolean Whether the validation passed
		 */
		var formValidateRequired = function (form, e) {
			var enableError = function (inputEl) {
				inputEl.addClass('gigya-wp-field-error');

				e.preventDefault();
				e.stopPropagation();
			};

			var isValid = true;

			form.find('input, select').each(function () {
				if ($(this).attr('data-required') && $(this).attr('data-required').length > 0) {
					if (!validateRequired($(this), $(this).attr('data-required'))) {
						enableError($(this));
						isValid = false;

						if ($(this).prop('tagName').toLowerCase() === "select") {
							if ($(this).next('.gigya-no-selected-option-error-message').length === 0)
								$(this).after("<h4 id = \"not-selected-option-row-number \"+ class=\"is-dismissible gigya-no-selected-option-error-message\">Please select option</h4>");


						} else {
							if ($(this).next('.gigya-empty-line-error-message').length === 0)
								$(this).after("<h4 id = \"empty-value-in-input \"+ class=\"is-dismissible gigya-empty-line-error-message\">Please fill-in the line</h4>");

						}
					}
					if ($(this).prop('tagName').toLowerCase() === "select")
						$(this).bind("change", function () {
							$(this).removeClass('gigya-wp-field-error');
							$(this).next('.gigya-no-selected-option-error-message').remove();
							isValid = true;
						});
					else
						$(this).on('keyup', function () {
							if (validateRequired($(this), $(this).attr('data-required'))) {
								$(this).removeClass('gigya-wp-field-error');
								$(this).next('.gigya-empty-line-error-message').remove();
							}
						});
				}
			});
			form.find('table').each(function () {
				$(this).find('tr').each(function () {
					var desktopSelection = $(this).find("select[data-type = 'Desktop Screen-Set']");
					var mobileSelection = $(this).find("select[data-type = 'Mobile Screen-Set']");
					if (!validateRequired(desktopSelection, desktopSelection.attr('data-required'))) {
						if (validateRequired(mobileSelection, mobileSelection.attr('data-required'))) {
							enableError(desktopSelection);
							if (desktopSelection.next('.gigya-no-selected-option-error-message').length === 0) {
								desktopSelection.after("<h4 id = \"not-selected-option-row-number \"+ class=\"is-dismissible gigya-no-selected-option-error-messeg\">Please select option</h4id>");
								isValid = false;
							}
							desktopSelection.bind("change", function () {
								desktopSelection.removeClass('gigya-wp-field-error');
								$(this).next('.gigya-no-selected-option-error-message').remove();
								isValid = true;
							});
						}

					}

				});
			});

			return isValid;
		};

// --------------------------------------------------------------------

		/**
		 * Conditional checkbox for next elements.
		 * @param el
		 * @param parentClass
		 * @param all
		 */
		var overrideToggle = function (el, parentClass, all) {
			parentClass = '.' + parentClass;
			var elementsToToggle = el.parents(parentClass).next();
			if (typeof all !== 'undefined' && all) {
				elementsToToggle = el.parents(parentClass).nextAll();
			}

			el.is(":checked") ? elementsToToggle.show() : elementsToToggle.hide();
		};

		// Conditional admin settings fields.
		$(document).on('change', '.conditional input[type="checkbox"]', function () {
			overrideToggle($(this), 'conditional');
		});
		$('.conditional input[type="checkbox"]').each(function () {
			overrideToggle($(this), 'conditional');
		});

		// Conditional widget overrides fields.
		$(document).on('change', '.gigya-widget-override input[type="checkbox"]', function () {
			overrideToggle($(this), 'gigya-widget-override', true);
		});
		$('.gigya-widget-override input[type="checkbox"]').each(function () {
			overrideToggle($(this), 'gigya-widget-override', true);
		});

// --------------------------------------------------------------------

		/**
		 * Run the clean DB after upgrade script.
		 */
		var cleanDB = function () {
			var options = {
				url: gigyaParams.ajaxurl,
				type: 'POST',
				data: {
					data: '',
					action: 'clean_db'
				}
			};

			var req = $.ajax(options);

			req.done(function (res) {
				if (res.success) {
					alert(res.data.msg);
					location.reload();
				}
			});

			req.fail(function (jqXHR, textStatus, errorThrown) {
				console.log(errorThrown);
			});
		};

		$(document).on('click', '.gigya-settings .clean-db', function () {
			var r = confirm("You're about to run a database cleaner.\n\rOld data from SAP Customer Data Cloud plugin version 4.0 will be deleted permanently from the database.\n\rIt's highly recommended to backup your database before you run this script.\n\rPlease confirm you want to continue.");
			if (r) {
				cleanDB();
			}
		});

// --------------------------------------------------------------------

		/**
		 * Run the clean DB after upgrade script.
		 */
		var debugLog = function () {
			var options = {
				url: gigyaParams.ajaxurl,
				type: 'POST',
				data: {
					data: '',
					action: 'debug_log'
				}
			};

			var req = $.ajax(options);

			req.done(function (res) {
				if (res.success) {
					var pom = document.createElement('a');
					pom.setAttribute('href', 'data:application/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(res.data, null, 4)));
					pom.setAttribute('download', 'gigya-log.json');
					pom.click();
				}
			});

			req.fail(function (jqXHR, textStatus, errorThrown) {
				console.log(errorThrown);
			});
		};

		$(document).on('click', '.gigya-debug-log', function () {
			debugLog();
		});

// --------------------------------------------------------------------

		// Disable the RaaS option when not available.
		$('.raas_disabled').find('input[value="raas"]').attr('disabled', 'disabled').parent('label').css('color', '#ccc');

// --------------------------------------------------------------------

		// JSON example for Additional Parameters (advanced) sections.
		$(document).on('click', '.gigya-json-example', function (e) {
			e.preventDefault();
			var w = window.open("about:blank", "jsonExample", "width=440,height=330");

			$.getJSON(gigyaParams.jsonExampleURL)
				.done(function (data) {
					w.document.write('<p>JSON Example:</p><textarea  rows="8" cols="45">' + JSON.stringify(data, null, 4) + '</textarea><br><small>Tips for valid JSON:<ol><li>{curly brackets} are for objects (key:value).</li><li>[square brackets] are for arrays (value).</li><li>Both keys and values must have double quote ("").</li><li>No trailing commas.</li></ol></small>');
				});
		});

// --------------------------------------------------------------------

		/*
		 * General settings page - data centers select
		 * show/hide 'other' data center text field
		 * update data center value according to input selction
		 */

		// Hide the other data center field by default if other is not selected
		if ($("#gigya_data_center").find("option:selected").val() !== 'other') {
			$('.other_dataCenter').hide();
		}

		// Show other data center input field on 'other' selection
		$('.data_center select').on('change', function () {
			if ($("#gigya_data_center").find("option:selected").text() === 'Other') {
				$('.other_dataCenter').show();
			} else {
				$('.other_dataCenter').hide();
			}
		});

		// on filling other data center set the selected value to the input
		$('#other_ds').on('focusout', function () {
			$("#gigya_data_center").find("option:selected").val($('#other_ds').val());
		});

// --------------------------------------------------------------------

		/*
		 * User management page : Toggle raas admin login roles check all
		 */
		// on page load check if checkall is checked, if yes check all roles.
		//if ( $('#gigya_raas_allowed_admin_checkall').is(':checked') ) {
		//  $('.gigya_raas_allowed_admin_roles input').attr('checked', true);
		//}
		// capture checkall checking event to toggle roles checkboxes.
		$('#gigya_raas_allowed_admin_checkall').on('change', function () {
			if ($(this).is(':checked')) {
				$('.gigya_raas_allowed_admin_roles input').prop('checked', true);
			} else {
				$('.gigya_raas_allowed_admin_roles input').prop('checked', false);
			}
		});

// --------------------------------------------------------------------

		/*
		 * Screen-Set Settings page
		 */
		$('.gigya-add-dynamic-field-line').on('click', function (e) {

			if (formValidateRequired($(this), e)) {
				var count = $(this).find('.gigya-wp-settings-table ').find('tr').length + 1;
				var current_tr = $(this).closest('tr');
				var dynamic_line_row = current_tr.prev().clone(false);
				dynamic_line_row.attr('id', 'row-table-number ' + count);
				dynamic_line_row.find('select, input').each(function () {
					if ($(this).prop('tagName').toLowerCase() === "select") {
						$(this).removeClass('gigya-wp-field-error');
						$(this).next('.gigya-no-selected-option-error-message').remove();
						$(this).val('');
					} else if ($(this).prop('tagName').toLowerCase() === "input") {
						$(this).prop('checked', 0);
					}
					$(this).attr('name',
						$(this).attr('name').replace(/^([a-zA-Z0-9-_]+)(\[[a-zA-Z0-9-_]+\])\[([0-9]+)\]/, function (match, m1, m2, m3) {
							m3++;
							return m1 + m2 + '[' + m3 + ']';
						}));
					$(this).attr('id',
						$(this).attr('id').replace(/^([a-zA-Z0-9-_]+--)([0-9]+)--/, function (match, m1, m2) {
							m2++;
							return m1 + '-' + m2 + '-';
						}));
				});
				current_tr.before(dynamic_line_row);
				$('.gigya-remove-dynamic-field-line').attr('disabled', false);
			}
		});
		$('.gigya-wp-settings-table ').on('click', '.gigya-remove-dynamic-field-line', function () {
			/* At least two lines present, plus Add button table row */
			if ($(this).closest('table').find('tr').length === 3) {
				$('.gigya-remove-dynamic-field-line').attr('disabled', true);
				$(this).closest('tr').remove();
			} else if ($(this).closest('table').find('tr').length > 3) {
				$(this).closest('tr').remove();
			}
		});

// --------------------------------------------------------------------

		/* Form manipulation functions */

		var gigya_depends_on = $('.gigya-depends-on');
		var handleGigyaFormElementDependency = function (depender_obj, dependee_obj, values) {
			if (values.indexOf(dependee_obj.val()) !== -1) {
				depender_obj.show();
			} else {
				depender_obj.hide();
			}
		};
		var handleGigyaFormDependency = function () {
			gigya_depends_on.each(function () {
				var gigya_depends_on_json = JSON.parse($(this).attr('data-depends-on').replace('&quot;', '"'));
				var dependee_obj = $('[name*="[' + gigya_depends_on_json[0] + ']"]');
				var depender_obj = $(this);

				if (dependee_obj.length > 0) {
					dependee_obj.on('change', function () {
						handleGigyaFormElementDependency(depender_obj, dependee_obj, gigya_depends_on_json.slice(1));
					});
				}

			});
		};
		handleGigyaFormDependency();

// --------------------------------------------------------------------

		/* Form validation */

		// Validate form before submit
		$('form.gigya-settings').on('submit', function (e) {
			var sessionDurationObj = $('#gigya_session_duration');
			if (sessionDurationObj.length > 0)
				emptyNumericValidate(sessionDurationObj, e);
			$('form.gigya-settings textarea').each(function () {
				jsonValidate($(this), e);
			})
		});

		// Validate JSON before submit on admin forms.
		var submitEl = $('.textarea.json').parents('form').find('input[type="submit"]');
		submitEl.on('click', function (e) {
			$('.textarea.json textarea').each(function () {
				jsonValidate($(this), e);
			})
		});

		// Validate required fields
		var formEl = $('.gigya-form-field').closest('form');
		formEl.find('input[type="submit"]').on('click', function (e) {
			formValidateRequired($(this).closest('form'), e);
		});
	});
})(jQuery);