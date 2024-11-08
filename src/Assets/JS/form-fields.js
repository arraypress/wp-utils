(function ($) {
    'use strict';

    const FormFields = {
        lightboxState: {
            gallery: [],
            currentIndex: 0
        },

        init: function () {
            this.initLightbox();
            this.initPasswordToggles();
            this.initMediaUploads();
            this.initRangeSliders();
        },

        updateLightboxImage: function (index) {
            const overlay = document.getElementById('wp-lightbox-overlay');
            const img = overlay.querySelector('img');
            const caption = overlay.querySelector('.wp-lightbox-caption');
            const item = this.lightboxState.gallery[index];

            img.src = item.fullSrc;
            img.alt = item.alt || '';
            caption.innerHTML = item.caption || '';
            caption.style.display = item.caption ? 'block' : 'none';
            this.lightboxState.currentIndex = index;

            overlay.querySelector('.wp-lightbox-prev').style.display =
                this.lightboxState.gallery.length > 1 ? 'block' : 'none';
            overlay.querySelector('.wp-lightbox-next').style.display =
                this.lightboxState.gallery.length > 1 ? 'block' : 'none';
        },

        closeLightbox: function () {
            const overlay = document.getElementById('wp-lightbox-overlay');
            overlay.style.display = 'none';
            this.lightboxState.gallery = [];
            this.lightboxState.currentIndex = 0;
        },

        initLightbox: function () {
            if (!document.getElementById('wp-lightbox-overlay')) {
                const overlay = document.createElement('div');
                overlay.id = 'wp-lightbox-overlay';
                overlay.className = 'wp-lightbox-overlay';
                overlay.innerHTML = `
                    <div class="wp-lightbox-content">
                        <img src="" alt="" />
                        <div class="wp-lightbox-caption"></div>
                        <button class="wp-lightbox-close">&times;</button>
                        <button class="wp-lightbox-prev" style="display: none;">&larr;</button>
                        <button class="wp-lightbox-next" style="display: none;">&rarr;</button>
                    </div>
                `;
                document.body.appendChild(overlay);

                const self = this;
                overlay.addEventListener('click', function (e) {
                    if (e.target === overlay || e.target.classList.contains('wp-lightbox-close')) {
                        self.closeLightbox();
                    } else if (e.target.classList.contains('wp-lightbox-prev') && self.lightboxState.gallery.length > 1) {
                        const newIndex = self.lightboxState.currentIndex > 0 ?
                            self.lightboxState.currentIndex - 1 : self.lightboxState.gallery.length - 1;
                        self.updateLightboxImage(newIndex);
                    } else if (e.target.classList.contains('wp-lightbox-next') && self.lightboxState.gallery.length > 1) {
                        const newIndex = self.lightboxState.currentIndex < self.lightboxState.gallery.length - 1 ?
                            self.lightboxState.currentIndex + 1 : 0;
                        self.updateLightboxImage(newIndex);
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (overlay.style.display !== 'flex') return;

                    switch (e.key) {
                        case 'Escape':
                            self.closeLightbox();
                            break;
                        case 'ArrowLeft':
                            if (self.lightboxState.gallery.length > 1) {
                                const newIndex = self.lightboxState.currentIndex > 0 ?
                                    self.lightboxState.currentIndex - 1 : self.lightboxState.gallery.length - 1;
                                self.updateLightboxImage(newIndex);
                            }
                            break;
                        case 'ArrowRight':
                            if (self.lightboxState.gallery.length > 1) {
                                const newIndex = self.lightboxState.currentIndex < self.lightboxState.gallery.length - 1 ?
                                    self.lightboxState.currentIndex + 1 : 0;
                                self.updateLightboxImage(newIndex);
                            }
                            break;
                    }
                });
            }

            const self = this;
            document.querySelectorAll('.wp-lightbox, .wp-gallery-item').forEach(function (item) {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    const overlay = document.getElementById('wp-lightbox-overlay');

                    const gallery = this.closest('.wp-gallery');
                    if (gallery) {
                        self.lightboxState.gallery = Array.from(gallery.querySelectorAll('.wp-gallery-item')).map(item => {
                            const img = item.querySelector('img');
                            return {
                                fullSrc: img.dataset.fullSrc || img.src,
                                alt: img.alt || '',
                                caption: item.querySelector('.wp-gallery-caption')?.textContent || ''
                            };
                        });
                        self.lightboxState.currentIndex = Array.from(gallery.children).indexOf(this);
                    } else {
                        const img = this.querySelector('img');
                        self.lightboxState.gallery = [{
                            fullSrc: this.getAttribute('data-full-src'),
                            alt: img?.alt || '',
                            caption: this.getAttribute('data-caption') ||
                                this.querySelector('.wp-caption-text')?.textContent ||
                                this.getAttribute('title') || ''
                        }];
                        self.lightboxState.currentIndex = 0;
                    }

                    self.updateLightboxImage(self.lightboxState.currentIndex);
                    overlay.style.display = 'flex';
                });
            });
        },

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

        initMediaUploads: function () {
            $('.wp-media-select').on('click', function (e) {
                e.preventDefault();

                const button = $(this);
                const name = button.data('name');
                const isMultiple = button.data('multiple') === '1';
                const mediaType = button.data('type');

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

                frame.on('select', function () {
                    const selection = frame.state().get('selection');
                    const attachment = selection.first().toJSON();

                    $('#' + name).val(attachment.id);

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

            $('.wp-media-remove').on('click', function (e) {
                e.preventDefault();

                const button = $(this);
                const name = button.data('name');

                $('#' + name).val('');
                $('#' + name + '_preview').html('').addClass('hidden');
                button.addClass('hidden');
            });
        },

        initRangeSliders: function () {
            document.querySelectorAll('.wp-range-wrapper').forEach(function (wrapper) {
                const range = wrapper.querySelector('input[type="range"]');
                const number = wrapper.querySelector('input[type="number"]');

                if (!range || !number) return;

                range.addEventListener('input', function () {
                    number.value = this.value;
                });

                number.addEventListener('input', function () {
                    range.value = this.value;
                });

                number.addEventListener('change', function () {
                    const value = parseFloat(this.value);
                    const min = parseFloat(this.min);
                    const max = parseFloat(this.max);
                    const step = parseFloat(this.step) || 1;

                    if (value < min) this.value = min;
                    if (value > max) this.value = max;

                    const steps = Math.round((value - min) / step);
                    this.value = min + (steps * step);

                    range.value = this.value;
                });
            });
        }
    };

    $(document).ready(function () {
        FormFields.init();
    });

})(jQuery);