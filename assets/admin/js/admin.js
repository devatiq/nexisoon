(function ($) {
	'use strict';

	var $form = $('#nexisoon-settings-form');
	var $toast = $('#nexisoon-toast');
	var $saveButton = $('#nexisoon-save-button');
	var $spinner = $('#nexisoon-save-spinner');

	function showToast(message, type) {
		$toast
			.removeClass('is-success is-error')
			.addClass('is-visible is-' + type)
			.text(message);
	}

	function setPreviewImage(url) {
		var $logo = $('.nexisoon-preview-logo');

		if (url) {
			$logo.html($('<img />', {
				src: url,
				alt: NexiSoonAdmin.logo
			}));
			return;
		}

		$logo.html($('<span />').text(NexiSoonAdmin.logo));
	}

	function updateStatus() {
		var enabled = $('[name="nexisoon_settings[enabled]"]').is(':checked');
		var label = enabled ? NexiSoonAdmin.active : NexiSoonAdmin.disabled;
		var activeClass = enabled ? 'is-active' : 'is-disabled';
		var inactiveClass = enabled ? 'is-disabled' : 'is-active';

		$('#nexisoon-status-badge, [data-nexisoon-status-preview]')
			.removeClass(inactiveClass)
			.addClass(activeClass)
			.text(label);
	}

	function updateTemplateCards() {
		var template = $('[name="nexisoon_settings[template]"]:checked').val() || 'classic-centered';

		$('[data-template-card]')
			.removeClass('is-selected')
			.attr('aria-checked', 'false')
			.find('.nexisoon-template-choose')
			.text(NexiSoonAdmin.selectTemplate);

		$('[data-template-card="' + template + '"]')
			.addClass('is-selected')
			.attr('aria-checked', 'true')
			.find('.nexisoon-template-choose')
			.text(NexiSoonAdmin.selected);

		$('.nexisoon-admin-preview').attr('data-preview-template', template);
	}

	function updatePreview() {
		var background = $('#nexisoon_background_color').val() || '#111827';
		var text = $('#nexisoon_text_color').val() || '#ffffff';
		var button = $('#nexisoon_button_color').val() || '#2563eb';
		var heading = $('#nexisoon_heading').val();
		var subheading = $('#nexisoon_subheading').val();
		var buttonText = $('#nexisoon_button_text').val();
		var footerText = $('#nexisoon_footer_text').val();
		var buttonEnabled = $('[name="nexisoon_settings[button_enabled]"]').is(':checked');
		var countdownStyle = $('[name="nexisoon_settings[countdown_display_style]"]:checked').val() || 'boxes';

		$('.nexisoon-admin-preview')
			.css('background-color', background)
			.css('color', text);
		$('[data-preview-heading]').text(heading);
		$('[data-preview-subheading]').text(subheading);
		$('[data-preview-button]')
			.toggleClass('is-hidden', !buttonEnabled)
			.css('background-color', button)
			.text(buttonText);
		$('[data-preview-footer]').text(footerText);
		$('[data-preview-countdown]').toggleClass('is-inline', countdownStyle === 'inline');

		updateStatus();
		updateTemplateCards();
	}

	function openMediaFrame($button) {
		var target = $button.data('target');
		var preview = $button.data('preview');
		var kind = $button.data('kind');
		var isFavicon = kind === 'favicon_id';
		var frame = wp.media({
			title: isFavicon ? NexiSoonAdmin.selectFavicon : NexiSoonAdmin.selectLogo,
			button: {
				text: isFavicon ? NexiSoonAdmin.useFavicon : NexiSoonAdmin.useLogo
			},
			library: {
				type: 'image'
			},
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

			$('#' + target).val(attachment.id);
			$('#' + preview).html($('<img />', {
				src: imageUrl,
				alt: attachment.alt || attachment.title || ''
			}));

			if (!isFavicon) {
				setPreviewImage(imageUrl);
			}
		});

		frame.open();
	}

	function activateTab(tab, url) {
		$('[data-nexisoon-tab-link]').removeClass('nav-tab-active');
		$('[data-nexisoon-tab-link="' + tab + '"]').addClass('nav-tab-active');
		$('[data-nexisoon-tab-panel]').attr('hidden', true).removeClass('is-active');
		$('[data-nexisoon-tab-panel="' + tab + '"]').removeAttr('hidden').addClass('is-active');

		if (window.history && window.history.pushState && url) {
			window.history.pushState({ nexisoonTab: tab }, '', url);
		}
	}

	$('.nexisoon-color-field').wpColorPicker({
		change: function () {
			window.setTimeout(updatePreview, 0);
		},
		clear: function () {
			window.setTimeout(updatePreview, 0);
		}
	});

	$form.on('input change', 'input, textarea, select', updatePreview);

	$(document).on('click', '[data-nexisoon-tab-link]', function (event) {
		var tab = $(this).data('nexisoon-tab-link');

		event.preventDefault();
		activateTab(tab, this.href);
	});

	$form.on('click keydown', '[data-template-card]', function (event) {
		var template = $(this).data('template-card');

		if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
			return;
		}

		event.preventDefault();
		$('#nexisoon_template_' + template).prop('checked', true).trigger('change');
	});

	$form.on('click', '.nexisoon-media-upload', function (event) {
		event.preventDefault();
		openMediaFrame($(this));
	});

	$form.on('click', '.nexisoon-media-remove', function (event) {
		var target = $(this).data('target');
		var preview = $(this).data('preview');

		event.preventDefault();
		$('#' + target).val('0');
		$('#' + preview).html($('<span />').text(NexiSoonAdmin.empty));

		if (target === 'nexisoon_logo_id') {
			setPreviewImage('');
		}
	});

	$form.on('submit', function (event) {
		var data = $form.serializeArray();

		event.preventDefault();
		data.push({
			name: 'action',
			value: 'nexisoon_save_settings'
		});

		$saveButton.prop('disabled', true).text(NexiSoonAdmin.saving);
		$spinner.addClass('is-active');

		$.post(NexiSoonAdmin.ajaxUrl, data)
			.done(function (response) {
				if (response && response.success) {
					showToast(response.data.message || NexiSoonAdmin.saved, 'success');
					return;
				}

				showToast(response && response.data && response.data.message ? response.data.message : NexiSoonAdmin.error, 'error');
			})
			.fail(function (xhr) {
				var message = NexiSoonAdmin.error;

				if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
					message = xhr.responseJSON.data.message;
				}

				showToast(message, 'error');
			})
			.always(function () {
				$saveButton.prop('disabled', false).text($saveButton.data('label') || NexiSoonAdmin.saveSettings);
				$spinner.removeClass('is-active');
			});
	});

	$saveButton.data('label', $saveButton.text());
	updatePreview();
})(jQuery);
