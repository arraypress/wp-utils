(function ($) {
    'use strict';

    const FormFields = {
        init: function () {
            this.initPasswordToggles();
            this.initMediaUploads();
            this.initRangeSliders();
        },

        // Password toggle functionality
        initPasswordToggles: function () {
            document.querySelectorAll('.password-toggle').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const passwordField = document.getElementById(
                        this.getAttribute('name').replace('_toggle', '')
                    );
                    if (passwordField) {
                        passwordField.type = this.checked ? 'text' : 'password';
                    }
                });
            });
        },

        // Media upload functionality
        initMediaUploads: function () {
            $('.wp-media-select').on('click', function (e) {
                e.preventDefault();

                const button = $(this);
                const name = button.data('name');
                const isMultiple = button.data('multiple') === '1';
                const mediaType = button.data('type');

                // Create the media frame
                const frame = wp.media({
                    title: 'Select File',
                    multiple: isMultiple,
                    library: {
                        type: mediaType !== 'any' ? mediaType : null
                    },
                    button: {
                        text: 'Select'
                    }
                });

                // When an image is selected in the media frame...
                frame.on('select', function () {
                    const selection = frame.state().get('selection');
                    const attachment = selection.first().toJSON();

                    // Update hidden input
                    $('#' + name).val(attachment.id);

                    // Update preview
                    const preview = $('#' + name + '_preview');
                    if (mediaType === 'image') {
                        if (attachment.sizes && attachment.sizes.thumbnail) {
                            preview.html(`<img src="${attachment.sizes.thumbnail.url}" alt="${attachment.title}" />`);
                        } else {
                            preview.html(`<img src="${attachment.url}" alt="${attachment.title}" />`);
                        }
                    } else {
                        preview.html(`<div class="file-preview"><span class="dashicons dashicons-media-default"></span> ${attachment.filename}</div>`);
                    }

                    preview.removeClass('hidden');
                    button.siblings('.wp-media-remove').removeClass('hidden');
                });

                frame.open();
            });

            // Handle remove button click
            $('.wp-media-remove').on('click', function (e) {
                e.preventDefault();

                const button = $(this);
                const name = button.data('name');

                // Clear hidden input
                $('#' + name).val('');

                // Clear preview
                $('#' + name + '_preview').html('').addClass('hidden');

                // Hide remove button
                button.addClass('hidden');
            });
        },

        // Range slider functionality
        initRangeSliders: function () {
            document.querySelectorAll('.wp-range-wrapper').forEach(function (wrapper) {
                const range = wrapper.querySelector('input[type="range"]');
                const number = wrapper.querySelector('input[type="number"]');

                if (!range || !number) return;

                // Update number when range changes
                range.addEventListener('input', function () {
                    number.value = this.value;
                });

                // Update range when number changes
                number.addEventListener('input', function () {
                    range.value = this.value;
                });

                // Ensure number stays within bounds
                number.addEventListener('change', function () {
                    const value = parseFloat(this.value);
                    const min = parseFloat(this.min);
                    const max = parseFloat(this.max);
                    const step = parseFloat(this.step) || 1;

                    if (value < min) this.value = min;
                    if (value > max) this.value = max;

                    // Adjust to nearest step
                    const steps = Math.round((value - min) / step);
                    this.value = min + (steps * step);

                    range.value = this.value;
                });
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        FormFields.init();
    });

})(jQuery);