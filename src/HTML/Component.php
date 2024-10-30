<?php
/**
 * HTML Component Generator
 *
 * A comprehensive collection of reusable HTML components built on top of the Element
 * generator. Provides pre-styled, customizable UI components like badges, cards,
 * progress bars, tooltips, and various image/gallery layouts. Each component
 * maintains consistency with WordPress standards while offering flexible
 * customization options.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.2.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\HTML;

/**
 * Check if the class `Component` is defined, and if not, define it.
 */
if ( ! class_exists( 'Component' ) ):

	/**
	 * UI component generation and management.
	 *
	 * Combines basic HTML elements into more complex, reusable components
	 * with pre-defined styling and behavior. Includes navigation elements,
	 * interactive components, status indicators, and media displays.
	 */
	class Component {

		/**
		 * Creates an unordered list of links.
		 *
		 * @param array $links      Array of links where key is the label and value is the URL
		 * @param array $args       {
		 *                          Optional. Arguments for customizing the link list.
		 *
		 * @type string $ul_class   Class for the UL element
		 * @type string $li_class   Class for the LI elements
		 * @type string $a_class    Class for anchor elements
		 * @type array  $properties Additional properties for anchor tags
		 *                          }
		 * @return string The HTML for the list of links
		 */
		public static function link_list( array $links, array $args = [] ): string {
			if ( empty( $links ) ) {
				return '';
			}

			$defaults = [
				'ul_class'   => 'link-list',
				'li_class'   => 'link-item',
				'a_class'    => 'link',
				'properties' => [],
			];

			$args  = wp_parse_args( $args, $defaults );
			$items = [];

			foreach ( $links as $label => $url ) {
				$link_attrs = array_merge(
					[ 'class' => $args['a_class'] ],
					$args['properties']
				);

				$items[] = Element::link( $url, $label, $link_attrs );
			}

			return Element::ul( $items, [
				'class' => $args['ul_class']
			], [
				'class' => $args['li_class']
			] );
		}

		/**
		 * Create a progress bar element.
		 *
		 * @param int   $percentage The percentage of progress (0-100).
		 * @param array $attrs      Optional. Additional attributes for the outer div.
		 * @param bool  $show_label Optional. Whether to show percentage label. Default true.
		 *
		 * @return string The HTML for the progress bar.
		 * @since  1.0.0
		 *
		 */
		public static function progress_bar( int $percentage, array $attrs = [], bool $show_label = true ): string {
			$percentage = max( 0, min( 100, $percentage ) );

			$default_attrs = [
				'class' => 'wp-progress' . ( $show_label ? ' wp-progress--with-label' : '' )
			];
			$attrs         = array_merge( $default_attrs, $attrs );

			$bar = Element::div( '', [
				'class' => 'wp-progress-bar',
				'style' => "width: {$percentage}%"
			] );

			if ( $show_label ) {
				$bar .= Element::span( "{$percentage}%", [ 'class' => 'wp-progress-label' ] );
			}

			Field::ensure_styles();

			return Element::div( $bar, $attrs );
		}

		/**
		 * Create a tooltip element.
		 *
		 * @param string $content      The content to display.
		 * @param string $tooltip_text The text to show in the tooltip.
		 * @param array  $attrs        Additional attributes for the outer span.
		 *
		 * @return string The HTML for the tooltip.
		 */
		public static function tooltip( string $content, string $tooltip_text, array $attrs = [] ): string {
			$attrs['class'] = isset( $attrs['class'] ) ? $attrs['class'] . ' wp-tooltip' : 'wp-tooltip';

			$tooltip_content = Element::span(
				esc_html( $tooltip_text ),
				[ 'class' => 'tooltiptext' ]
			);

			Field::ensure_styles();

			return Element::span( $content . $tooltip_content, $attrs );
		}

		/**
		 * Generate HTML status badge with styling.
		 *
		 * @param string $status The status text to display.
		 * @param string $type   Optional. Badge type (success, warning, error, info). Default 'default'.
		 *
		 * @return string HTML badge element.
		 * @since  1.0.0
		 *
		 */
		public static function status_badge( string $status, string $type = 'default' ): string {
			$class = 'wp-status-badge wp-status-badge--' . $type;

			Field::ensure_styles();

			return Element::span( $status, [ 'class' => $class ] );
		}

		/**
		 * Create an avatar element.
		 *
		 * @param string $image_url The URL of the avatar image.
		 * @param string $size      The size of the avatar (xs, sm, md, lg, xl, or pixel value)
		 * @param array  $attrs     Additional attributes for the img element.
		 *
		 * @return string The HTML for the avatar.
		 */
		public static function avatar( string $image_url, string $size = 'md', array $attrs = [] ): string {
			// Base class
			$attrs['class'] = isset( $attrs['class'] ) ? $attrs['class'] . ' wp-avatar' : 'wp-avatar';

			// Handle size
			if ( in_array( $size, [ 'xs', 'sm', 'md', 'lg', 'xl' ] ) ) {
				$attrs['class'] .= " wp-avatar-{$size}";
			} else {
				// Custom size - use inline style
				$size           = is_numeric( $size ) ? $size . 'px' : $size;
				$attrs['style'] = isset( $attrs['style'] ) ?
					$attrs['style'] . ";width:{$size};height:{$size}" :
					"width:{$size};height:{$size}";
			}

			$attrs['src'] = esc_url( $image_url );
			$attrs['alt'] = $attrs['alt'] ?? 'Avatar';

			return Element::img( $attrs['src'], $attrs['alt'], $attrs );
		}

		/**
		 * Create a card element.
		 *
		 * @param string $title   The title of the card.
		 * @param string $content The content of the card.
		 * @param array  $attrs   Additional attributes for the outer div.
		 *
		 * @return string The HTML for the card.
		 */
		public static function card( string $title, string $content, array $attrs = [] ): string {
			$attrs['class'] = isset( $attrs['class'] ) ? $attrs['class'] . ' wp-card' : 'wp-card';

			$title_html   = Element::create( 'h4', [ 'class' => 'wp-card-title' ], esc_html( $title ) );
			$content_html = Element::create( 'div', [ 'class' => 'wp-card-content' ], wp_kses_post( $content ) );

			Field::ensure_styles();

			return Element::div( $title_html . $content_html, $attrs );
		}

		/**
		 * Format date values with color based on past or active status.
		 *
		 * @param string $value        The date value to be formatted.
		 * @param string $past_color   The hex color for past dates.
		 * @param string $active_color The hex color for active dates.
		 * @param string $default      The default value to display if the date is not available.
		 *
		 * @return string The formatted date with color or the default value.
		 */
		public static function date_with_color( string $value, string $past_color = '#ff0000', string $active_color = '#a3b745', string $default = Element::MDASH ): string {
			if ( ! empty( $value ) ) {
				$timestamp = strtotime( $value );
				$color     = $timestamp < time() ? $past_color : $active_color;

				$formatted_date = date_i18n( get_option( 'date_format' ), $timestamp );

				return Element::span( $formatted_date, [ 'style' => "color: $color;" ] );
			}

			return $default === Element::MDASH ? Element::MDASH : Element::span( $default );
		}

		/**
		 * Generate an image thumbnail HTML with enhanced flexibility and features.
		 *
		 * @param int          $attachment_id     The attachment ID.
		 * @param mixed        $size              The image size. Default is 'thumbnail'.
		 *                                        Can be a string (registered size) or array [width, height].
		 * @param array        $attr              Optional. Additional attributes for the image tag.
		 * @param array|string $container_classes Classes for the container div. Can be array or space-separated string.
		 * @param array        $container_attr    Optional. Additional attributes for the container div.
		 * @param string       $fallback          HTML/text to display if image doesn't exist. Default is em dash.
		 * @param bool         $lazy_load         Whether to enable lazy loading. Default true.
		 * @param bool         $link_to_full      Whether to link to full-size image. Default false.
		 *
		 * @return string The HTML for the image thumbnail.
		 */
		public static function attachment_thumbnail(
			int $attachment_id,
			$size = 'thumbnail',
			array $attr = [],
			$container_classes = [ 'thumbnail' ],
			array $container_attr = [],
			string $fallback = Element::MDASH,
			bool $lazy_load = true,
			bool $link_to_full = false
		): string {
			// Validate attachment exists
			if ( ! wp_attachment_is_image( $attachment_id ) ) {
				return $fallback;
			}

			// Prepare image attributes
			$default_attr = [
				'class'   => 'image-thumbnail',
				'loading' => $lazy_load ? 'lazy' : 'eager',
			];
			$attr         = wp_parse_args( $attr, $default_attr );

			// Get image HTML
			$image_html = wp_get_attachment_image( $attachment_id, $size, false, $attr );
			if ( ! $image_html ) {
				return $fallback;
			}

			// Wrap image in link if requested
			if ( $link_to_full ) {
				$full_url = wp_get_attachment_image_url( $attachment_id, 'full' );
				if ( $full_url ) {
					$image_html = Element::link( $full_url, $image_html, [ 'class' => 'thumbnail-link' ] );
				}
			}

			// Process container classes if they're in string format
			if ( is_string( $container_classes ) ) {
				$container_classes = explode( ' ', $container_classes );
			}

			// Ensure container classes are in the attributes
			$container_attr['class'] = isset( $container_attr['class'] )
				? $container_attr['class'] . ' ' . implode( ' ', $container_classes )
				: implode( ' ', $container_classes );

			// Create the container div
			return Element::div( $image_html, $container_attr );
		}

		/**
		 * Create a responsive image element with srcset support.
		 *
		 * @param string $src   The source URL of the image.
		 * @param string $alt   The alternative text for the image.
		 * @param array  $sizes Array of image sizes (width => url).
		 * @param array  $attrs Additional attributes for the image.
		 *
		 * @return string The HTML for the responsive image.
		 */
		public static function responsive_image( string $src, string $alt, array $sizes = [], array $attrs = [] ): string {
			if ( ! empty( $sizes ) ) {
				$srcset = [];
				foreach ( $sizes as $width => $url ) {
					$srcset[] = esc_url( $url ) . ' ' . $width . 'w';
				}
				$attrs['srcset'] = implode( ', ', $srcset );
			}

			return Element::img( $src, $alt, $attrs );
		}

		/**
		 * Create an image gallery.
		 *
		 * @param array $images Array of image data (src, alt, caption).
		 * @param array $attrs  Additional attributes for the gallery container.
		 *
		 * @return string The HTML for the image gallery.
		 */
		public static function gallery( array $images, array $attrs = [] ): string {
			$default_styles = [
				'display'               => 'grid',
				'grid-template-columns' => 'repeat(auto-fill, minmax(200px, 1fr))',
				'gap'                   => '1rem',
				'padding'               => '1rem'
			];

			$attrs['style'] = Element::merge_styles( $default_styles, $attrs['style'] ?? '' );

			$gallery_content = '';
			foreach ( $images as $image ) {
				$figure_content = Element::img( $image['src'], $image['alt'] ?? '', [
					'style' => 'width: 100%; height: 100%; object-fit: cover;'
				] );

				if ( ! empty( $image['caption'] ) ) {
					$figure_content .= Element::create( 'figcaption', [], esc_html( $image['caption'] ) );
				}

				$gallery_content .= Element::create( 'figure', [
					'style' => 'margin: 0; padding: 0;'
				], $figure_content );
			}

			return Element::div( $gallery_content, $attrs );
		}

		/**
		 * Create a responsive WordPress gallery with lightbox support.
		 *
		 * @param array $attachments     Array of attachment IDs
		 * @param array $args            {
		 *                               Optional. Array of gallery arguments.
		 *
		 * @type string $size            Image size to use. Default 'medium'.
		 * @type int    $columns         Number of columns. Default 3.
		 * @type bool   $show_captions   Whether to show captions. Default true.
		 * @type bool   $lightbox        Whether to enable lightbox. Default true.
		 * @type string $gap             Gap between items. Default '1rem'.
		 * @type array  $container_class Additional classes for container.
		 * @type array  $container_attrs Additional attributes for container.
		 *                               }
		 * @return string HTML gallery output
		 */
		public static function wp_gallery( array $attachments, array $args = [] ): string {
			if ( empty( $attachments ) ) {
				return '';
			}

			Field::ensure_assets();

			$defaults = [
				'size'            => 'medium',
				'columns'         => 3,
				'show_captions'   => true,
				'lightbox'        => true,
				'gap'             => '1rem',
				'container_class' => [],
				'container_attrs' => []
			];

			$args = wp_parse_args( $args, $defaults );

			// Ensure container classes is an array
			if ( is_string( $args['container_class'] ) ) {
				$args['container_class'] = explode( ' ', $args['container_class'] );
			}

			// Base container classes
			$container_classes = array_merge(
				[ 'wp-gallery' ],
				$args['container_class']
			);

			// Container styles
			$styles = [
				'display'               => 'grid',
				'grid-template-columns' => "repeat({$args['columns']}, 1fr)",
				'gap'                   => $args['gap'],
			];

			// Merge container attributes
			$container_attrs = array_merge(
				[
					'class' => implode( ' ', $container_classes ),
					'style' => Element::build_style_string( $styles )
				],
				$args['container_attrs']
			);

			$gallery_content = '';

			foreach ( $attachments as $attachment_id ) {
				$figure_content = '';

				// Create image content
				if ( $args['lightbox'] ) {
					$figure_content .= self::attachment_lightbox(
						$attachment_id,
						$args['size'],
						[ 'class' => 'wp-gallery-item-image' ]
					);
				} else {
					$figure_content .= wp_get_attachment_image(
						$attachment_id,
						$args['size'],
						false,
						[ 'class' => 'wp-gallery-item-image' ]
					);
				}

				// Add caption if enabled
				if ( $args['show_captions'] ) {
					$caption = wp_get_attachment_caption( $attachment_id );
					if ( $caption ) {
						$figure_content .= Element::create(
							'figcaption',
							[ 'class' => 'wp-gallery-item-caption' ],
							esc_html( $caption )
						);
					}
				}

				// Wrap in figure element
				$gallery_content .= Element::create(
					'figure',
					[ 'class' => 'wp-gallery-item' ],
					$figure_content
				);
			}

			Field::ensure_assets();

			// Build the final gallery
			return Element::div( $gallery_content, $container_attrs );
		}

		/**
		 * Create a lightbox-ready image.
		 *
		 * @param string $thumb_src The thumbnail image source URL.
		 * @param string $full_src  The full-size image source URL.
		 * @param string $alt       The alternative text for the image.
		 * @param array  $attrs     Additional attributes for the container.
		 *
		 * @return string The HTML for the lightbox image.
		 */
		public static function lightbox_image(
			string $thumb_src,
			string $full_src,
			string $alt = '',
			array $attrs = []
		): string {
			$default_attrs = [
				'class'         => 'lightbox-image',
				'data-full-src' => esc_url( $full_src )
			];

			$attrs = array_merge( $default_attrs, $attrs );

			$img = Element::img( $thumb_src, $alt, [
				'style' => 'cursor: pointer; max-width: 100%; height: auto;'
			] );

			Field::ensure_assets();

			return Element::div( $img, $attrs );
		}

		/**
		 * Create a lightbox-ready image from a WordPress attachment.
		 *
		 * @param int    $attachment_id The WordPress attachment ID.
		 * @param string $size          Optional. Image size to use for thumbnail. Default 'thumbnail'.
		 * @param array  $attrs         Optional. Additional attributes for the container.
		 *
		 * @return string The HTML for the lightbox image container.
		 */
		public static function attachment_lightbox(
			int $attachment_id,
			string $size = 'thumbnail',
			array $attrs = []
		): string {
			$thumb_src = wp_get_attachment_image_url( $attachment_id, $size );
			$full_src  = wp_get_attachment_image_url( $attachment_id, 'full' );
			$alt       = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			$caption   = wp_get_attachment_caption( $attachment_id );
			$title     = get_the_title( $attachment_id );

			if ( ! $thumb_src || ! $full_src ) {
				return '';
			}

			$default_attrs = [
				'class'         => 'wp-lightbox',
				'data-full-src' => $full_src,
				'data-caption'  => $caption ?: $title,
				'title'         => $title
			];
			$attrs         = array_merge( $default_attrs, $attrs );

			$img = wp_get_attachment_image( $attachment_id, $size, false, [
				'class' => 'wp-lightbox-thumb',
				'style' => 'cursor: pointer; max-width: 100%; height: auto;'
			] );

			Field::ensure_assets();

			return Element::div( $img, $attrs );
		}

		/**
		 * Create a breadcrumb navigation.
		 *
		 * @param array $items Array of breadcrumb items.
		 * @param array $attrs Additional attributes for the breadcrumb container.
		 *
		 * @return string The HTML string for the breadcrumbs.
		 */
		public static function breadcrumbs( array $items, array $attrs = [] ): string {
			$content    = '';
			$last_index = count( $items ) - 1;

			foreach ( $items as $index => $item ) {
				$is_last = ( $index === $last_index );

				if ( ! empty( $item['url'] ) && ! $is_last ) {
					$content .= Element::link( $item['url'], esc_html( $item['text'] ) );
				} else {
					$content .= Element::span( esc_html( $item['text'] ), [ 'class' => 'current' ] );
				}

				if ( ! $is_last ) {
					$content .= Element::span( ' / ', [ 'class' => 'separator' ] );
				}
			}

			$default_attrs = [ 'class' => 'breadcrumbs' ];
			$attrs         = array_merge( $default_attrs, $attrs );

			return Element::nav( $content, $attrs );
		}

	}
endif;