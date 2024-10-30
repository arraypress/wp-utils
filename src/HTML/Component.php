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
 * Check if the class `Components` is defined, and if not, define it.
 */
if ( ! class_exists( 'Components' ) ):

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
		 * @param array $attrs      Additional attributes for the outer div.
		 *
		 * @return string The HTML for the progress bar.
		 */
		public static function progress_bar( int $percentage, array $attrs = [] ): string {
			$percentage = max( 0, min( 100, $percentage ) ); // Ensure percentage is between 0 and 100

			$outer_styles = [
				'width'            => '100%',
				'background-color' => '#f0f0f0',
				'border-radius'    => '4px',
				'overflow'         => 'hidden',
			];

			$inner_styles = [
				'width'            => $percentage . '%',
				'height'           => '20px',
				'background-color' => '#4CAF50',
				'text-align'       => 'center',
				'line-height'      => '20px',
				'color'            => 'white',
			];

			$attrs['style'] = Element::merge_styles( $outer_styles, $attrs['style'] ?? '' );

			$inner_content = Element::div( $percentage . '%', [ 'style' => Element::build_style_string( $inner_styles ) ] );

			return Element::div( $inner_content, $attrs );
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
			$styles = [
				'position'      => 'relative',
				'display'       => 'inline-block',
				'border-bottom' => '1px dotted black',
				'cursor'        => 'help',
			];

			$tooltip_styles = [
				'visibility'       => 'hidden',
				'width'            => '120px',
				'background-color' => 'black',
				'color'            => '#fff',
				'text-align'       => 'center',
				'border-radius'    => '6px',
				'padding'          => '5px 0',
				'position'         => 'absolute',
				'z-index'          => '1',
				'bottom'           => '125%',
				'left'             => '50%',
				'margin-left'      => '-60px',
				'opacity'          => '0',
				'transition'       => 'opacity 0.3s',
			];

			$attrs['style'] = Element::merge_styles( $styles, $attrs['style'] ?? '' );

			$tooltip_content = Element::span( esc_html( $tooltip_text ), [
				'style' => Element::build_style_string( $tooltip_styles ),
				'class' => 'tooltiptext'
			] );

			return Element::span( $content . $tooltip_content, $attrs );
		}

		/**
		 * Create a badge element.
		 *
		 * @param string $content The content of the badge.
		 * @param string $color   The background color of the badge.
		 * @param array  $attrs   Additional attributes for the span.
		 *
		 * @return string The HTML for the badge.
		 */
		public static function badge( string $content, string $color = '#007bff', array $attrs = [] ): string {
			$styles = [
				'display'          => 'inline-block',
				'padding'          => '.25em .4em',
				'font-size'        => '75%',
				'font-weight'      => '700',
				'line-height'      => '1',
				'text-align'       => 'center',
				'white-space'      => 'nowrap',
				'vertical-align'   => 'baseline',
				'border-radius'    => '.25rem',
				'color'            => '#fff',
				'background-color' => $color,
			];

			$attrs['style'] = Element::merge_styles( $styles, $attrs['style'] ?? '' );

			return Element::span( esc_html( $content ), $attrs );
		}

		/**
		 * Generate HTML status badge.
		 *
		 * @param string $status Status text.
		 * @param string $type   Badge type (success, warning, error, info).
		 *
		 * @return string HTML badge.
		 */
		public static function status_badge( string $status, string $type = 'default' ): string {
			$classes = [
				'success' => 'status-badge-success',
				'warning' => 'status-badge-warning',
				'error'   => 'status-badge-error',
				'info'    => 'status-badge-info',
				'default' => 'status-badge-default'
			];

			$class = 'status-badge ' . ( $classes[ $type ] ?? $classes['default'] );

			return Element::span( $status, [ 'class' => $class ] );
		}

		/**
		 * Create an avatar element.
		 *
		 * @param string $image_url The URL of the avatar image.
		 * @param string $size      The size of the avatar (width and height).
		 * @param array  $attrs     Additional attributes for the img element.
		 *
		 * @return string The HTML for the avatar.
		 */
		public static function avatar( string $image_url, string $size = '50px', array $attrs = [] ): string {
			$styles = [
				'width'         => $size,
				'height'        => $size,
				'border-radius' => '50%',
				'object-fit'    => 'cover',
			];

			$attrs['style'] = Element::merge_styles( $styles, $attrs['style'] ?? '' );
			$attrs['src']   = esc_url( $image_url );
			$attrs['alt']   = $attrs['alt'] ?? 'Avatar';

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
			$styles = [
				'box-shadow'    => '0 4px 8px 0 rgba(0,0,0,0.2)',
				'transition'    => '0.3s',
				'border-radius' => '5px',
				'padding'       => '16px',
				'margin'        => '10px',
			];

			$attrs['style'] = Element::merge_styles( $styles, $attrs['style'] ?? '' );

			$title_html   = Element::create( 'h4', [ 'style' => 'margin-top: 0;' ], esc_html( $title ) );
			$content_html = Element::p( wp_kses_post( $content ) );

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

		/**
		 * Create a pagination element.
		 *
		 * @param int    $current_page Current page number.
		 * @param int    $total_pages  Total number of pages.
		 * @param string $base_url     Base URL for pagination links.
		 * @param array  $attrs        Additional attributes for the pagination container.
		 *
		 * @return string The HTML string for the pagination.
		 */
		public static function pagination( int $current_page, int $total_pages, string $base_url, array $attrs = [] ): string {
			if ( $total_pages <= 1 ) {
				return '';
			}

			$content = '';

			// Previous link
			if ( $current_page > 1 ) {
				$prev_url = add_query_arg( 'page', $current_page - 1, $base_url );
				$content  .= Element::link( $prev_url, '← Previous', [ 'class' => 'prev' ] );
			}

			// Page numbers
			for ( $i = 1; $i <= $total_pages; $i ++ ) {
				if ( $i === $current_page ) {
					$content .= Element::span( (string) $i, [ 'class' => 'current' ] );
				} else {
					$page_url = add_query_arg( 'page', $i, $base_url );
					$content  .= Element::link( $page_url, (string) $i );
				}
			}

			// Next link
			if ( $current_page < $total_pages ) {
				$next_url = add_query_arg( 'page', $current_page + 1, $base_url );
				$content  .= Element::link( $next_url, 'Next →', [ 'class' => 'next' ] );
			}

			$default_attrs = [ 'class' => 'pagination' ];
			$attrs         = array_merge( $default_attrs, $attrs );

			return Element::nav( $content, $attrs );
		}

	}
endif;