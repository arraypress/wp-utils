<?php
/**
 * HTML Creator Utilities
 *
 * This class provides static methods to create various HTML elements
 * in a WordPress-friendly way. It includes methods for generating links,
 * buttons, divs, spans, lists, images, and other common HTML elements
 * while ensuring attributes are properly escaped and following best practices.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

/**
 * Check if the class `Create` is defined, and if not, define it.
 */
if ( ! class_exists( 'Create' ) ) :

	/**
	 * HTML Creator class for generating HTML elements in WordPress.
	 *
	 * This class provides static methods to create various HTML elements
	 * using WordPress-specific escaping functions and naming conventions.
	 */
	class Create {

		// Existing constant for mdash
		public const MDASH = '&mdash;';

		/**
		 * Create an HTML link element.
		 *
		 * @param string $url   The URL for the link.
		 * @param string $text  The text content of the link.
		 * @param array  $attrs Additional attributes for the link.
		 *
		 * @return string The HTML string for the link.
		 */
		public static function link( string $url, string $text, array $attrs = [] ): string {
			$attrs['href'] = esc_url( $url );

			return self::element( 'a', $attrs, $text );
		}

		/**
		 * Create an HTML button element.
		 *
		 * @param string $text  The text content of the button.
		 * @param array  $attrs Additional attributes for the button.
		 *
		 * @return string The HTML string for the button.
		 */
		public static function button( string $text, array $attrs = [] ): string {
			return self::element( 'button', $attrs, $text );
		}

		/**
		 * Create an HTML div element.
		 *
		 * @param string $content The content of the div.
		 * @param array  $attrs   Additional attributes for the div.
		 *
		 * @return string The HTML string for the div.
		 */
		public static function div( string $content, array $attrs = [] ): string {
			return self::element( 'div', $attrs, $content );
		}

		/**
		 * Create an HTML span element.
		 *
		 * @param string $content The content of the span.
		 * @param array  $attrs   Additional attributes for the span.
		 *
		 * @return string The HTML string for the span.
		 */
		public static function span( string $content, array $attrs = [] ): string {
			return self::element( 'span', $attrs, $content );
		}

		/**
		 * Create an HTML paragraph element.
		 *
		 * @param string $content The content of the paragraph.
		 * @param array  $attrs   Additional attributes for the paragraph.
		 *
		 * @return string The HTML string for the paragraph.
		 */
		public static function p( string $content, array $attrs = [] ): string {
			return self::element( 'p', $attrs, $content );
		}

		/**
		 * Create an HTML image element.
		 *
		 * @param string $src   The source URL of the image.
		 * @param string $alt   The alternative text for the image.
		 * @param array  $attrs Additional attributes for the image.
		 *
		 * @return string The HTML string for the image.
		 */
		public static function img( string $src, string $alt, array $attrs = [] ): string {
			$attrs['src'] = esc_url( $src );
			$attrs['alt'] = esc_attr( $alt );

			return self::void_element( 'img', $attrs );
		}

		/**
		 * Create an HTML unordered list element.
		 *
		 * @param array $items    An array of list items.
		 * @param array $attrs    Additional attributes for the ul element.
		 * @param array $li_attrs Additional attributes for each li element.
		 *
		 * @return string The HTML string for the unordered list.
		 */
		public static function ul( array $items, array $attrs = [], array $li_attrs = [] ): string {
			$content = '';
			foreach ( $items as $item ) {
				$content .= self::element( 'li', $li_attrs, $item );
			}

			return self::element( 'ul', $attrs, $content );
		}

		/**
		 * Create an HTML ordered list element.
		 *
		 * @param array $items    An array of list items.
		 * @param array $attrs    Additional attributes for the ol element.
		 * @param array $li_attrs Additional attributes for each li element.
		 *
		 * @return string The HTML string for the ordered list.
		 */
		public static function ol( array $items, array $attrs = [], array $li_attrs = [] ): string {
			$content = '';
			foreach ( $items as $item ) {
				$content .= self::element( 'li', $li_attrs, $item );
			}

			return self::element( 'ol', $attrs, $content );
		}

		/**
		 * Create an HTML input element.
		 *
		 * @param string $type  The type of the input.
		 * @param array  $attrs Additional attributes for the input.
		 *
		 * @return string The HTML string for the input.
		 */
		public static function input( string $type, array $attrs = [] ): string {
			$attrs['type'] = esc_attr( $type );

			return self::void_element( 'input', $attrs );
		}

		/**
		 * Create an HTML textarea element.
		 *
		 * @param string $content The content of the textarea.
		 * @param array  $attrs   Additional attributes for the textarea.
		 *
		 * @return string The HTML string for the textarea.
		 */
		public static function textarea( string $content = '', array $attrs = [] ): string {
			return self::element( 'textarea', $attrs, $content );
		}

		/**
		 * Create an HTML select element.
		 *
		 * @param array       $options  An associative array of options (value => label).
		 * @param array       $attrs    Additional attributes for the select.
		 * @param string|null $selected The value of the option that should be selected (optional).
		 *
		 * @return string The HTML string for the select.
		 */
		public static function select( array $options, array $attrs = [], string $selected = null ): string {
			$content = '';
			foreach ( $options as $value => $label ) {
				$option_attrs = [ 'value' => esc_attr( $value ) ];
				if ( $value === $selected ) {
					$option_attrs['selected'] = 'selected';
				}
				$content .= sprintf(
					'<option %s>%s</option>',
					self::build_attribute_string( $option_attrs ),
					esc_html( $label )
				);
			}

			return sprintf(
				'<select %s>%s</select>',
				self::build_attribute_string( $attrs ),
				$content
			);
		}

		/**
		 * Create an HTML label element.
		 *
		 * @param string $for     The ID of the form element this label is for.
		 * @param string $content The content of the label.
		 * @param array  $attrs   Additional attributes for the label.
		 *
		 * @return string The HTML string for the label.
		 */
		public static function label( string $for, string $content, array $attrs = [] ): string {
			$attrs['for'] = esc_attr( $for );

			return self::element( 'label', $attrs, $content );
		}

		/**
		 * Generate a color circle div with the actual color in it.
		 *
		 * @param string $color The hex color code.
		 * @param array  $attrs Additional attributes for the div (optional).
		 *
		 * @return string The HTML for the color circle div.
		 */
		public static function circle( string $color, array $attrs = [] ): string {
			$sanitized_color = esc_attr( $color );

			$default_styles = [
				'display'          => 'inline-block',
				'width'            => '20px',
				'height'           => '20px',
				'border-radius'    => '50%',
				'background-color' => $sanitized_color,
				'border'           => '1px solid #ccc'
			];

			// Merge default styles with any existing style attribute
			if ( isset( $attrs['style'] ) ) {
				$existing_styles = self::parse_style_attribute( $attrs['style'] );
				$merged_styles   = array_merge( $default_styles, $existing_styles );
			} else {
				$merged_styles = $default_styles;
			}

			// Convert merged styles back to a string
			$attrs['style'] = self::build_style_string( $merged_styles );

			// Use the div method to create the color circle
			return self::div( '', $attrs );
		}

		/**
		 * Create a square div element.
		 *
		 * @param string $color The background color of the square.
		 * @param string $size  The size of the square (width and height).
		 * @param array  $attrs Additional attributes for the div.
		 *
		 * @return string The HTML for the square div.
		 */
		public static function square( string $color, string $size = '50px', array $attrs = [] ): string {
			$styles = [
				'width'            => $size,
				'height'           => $size,
				'background-color' => esc_attr( $color ),
				'display'          => 'inline-block',
			];

			$attrs['style'] = self::merge_styles( $styles, $attrs['style'] ?? '' );

			return self::div( '', $attrs );
		}

		/**
		 * Create a triangle div element.
		 *
		 * @param string $color     The color of the triangle.
		 * @param string $size      The size of the triangle.
		 * @param string $direction The direction of the triangle (up, down, left, right).
		 * @param array  $attrs     Additional attributes for the div.
		 *
		 * @return string The HTML for the triangle div.
		 */
		public static function triangle( string $color, string $size = '50px', string $direction = 'up', array $attrs = [] ): string {
			$styles = [
				'width'   => '0',
				'height'  => '0',
				'display' => 'inline-block',
				'border'  => $size . ' solid transparent',
			];

			switch ( $direction ) {
				case 'up':
					$styles['border-bottom-color'] = $color;
					break;
				case 'down':
					$styles['border-top-color'] = $color;
					break;
				case 'left':
					$styles['border-right-color'] = $color;
					break;
				case 'right':
					$styles['border-left-color'] = $color;
					break;
			}

			$attrs['style'] = self::merge_styles( $styles, $attrs['style'] ?? '' );

			return self::div( '', $attrs );
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

			$attrs['style'] = self::merge_styles( $outer_styles, $attrs['style'] ?? '' );

			$inner_content = self::div( $percentage . '%', [ 'style' => self::build_style_string( $inner_styles ) ] );

			return self::div( $inner_content, $attrs );
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

			$attrs['style'] = self::merge_styles( $styles, $attrs['style'] ?? '' );

			$tooltip_content = self::span( esc_html( $tooltip_text ), [
				'style' => self::build_style_string( $tooltip_styles ),
				'class' => 'tooltiptext'
			] );

			return self::span( $content . $tooltip_content, $attrs );
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

			$attrs['style'] = self::merge_styles( $styles, $attrs['style'] ?? '' );

			return self::span( esc_html( $content ), $attrs );
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

			$attrs['style'] = self::merge_styles( $styles, $attrs['style'] ?? '' );
			$attrs['src']   = esc_url( $image_url );
			$attrs['alt']   = $attrs['alt'] ?? 'Avatar';

			return self::img( $attrs['src'], $attrs['alt'], $attrs );
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

			$attrs['style'] = self::merge_styles( $styles, $attrs['style'] ?? '' );

			$title_html   = self::element( 'h4', [ 'style' => 'margin-top: 0;' ], esc_html( $title ) );
			$content_html = self::p( wp_kses_post( $content ) );

			return self::div( $title_html . $content_html, $attrs );
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
		public static function date_with_color( string $value, string $past_color = '#ff0000', string $active_color = '#a3b745', string $default = self::MDASH ): string {
			if ( ! empty( $value ) ) {
				$timestamp = strtotime( $value );
				$color     = $timestamp < time() ? $past_color : $active_color;

				$formatted_date = date_i18n( get_option( 'date_format' ), $timestamp );

				return self::span( $formatted_date, [ 'style' => "color: $color;" ] );
			}

			return $default === self::MDASH ? self::MDASH : self::span( $default );
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
			string $fallback = self::MDASH,
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
					$image_html = self::link( $full_url, $image_html, [ 'class' => 'thumbnail-link' ] );
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
			return self::div( $image_html, $container_attr );
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

			return self::img( $src, $alt, $attrs );
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

			$attrs['style'] = self::merge_styles( $default_styles, $attrs['style'] ?? '' );

			$gallery_content = '';
			foreach ( $images as $image ) {
				$figure_content = self::img( $image['src'], $image['alt'] ?? '', [
					'style' => 'width: 100%; height: 100%; object-fit: cover;'
				] );

				if ( ! empty( $image['caption'] ) ) {
					$figure_content .= self::element( 'figcaption', [], esc_html( $image['caption'] ) );
				}

				$gallery_content .= self::element( 'figure', [
					'style' => 'margin: 0; padding: 0;'
				], $figure_content );
			}

			return self::div( $gallery_content, $attrs );
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

			$img = self::img( $thumb_src, $alt, [
				'style' => 'cursor: pointer; max-width: 100%; height: auto;'
			] );

			return self::div( $img, $attrs );
		}

		/**
		 * Create an HTML form element.
		 *
		 * @param string $content The content of the form.
		 * @param string $action  The form action URL.
		 * @param string $method  The form method (get/post).
		 * @param array  $attrs   Additional attributes for the form.
		 *
		 * @return string The HTML string for the form.
		 */
		public static function form( string $content, string $action = '', string $method = 'post', array $attrs = [] ): string {
			$attrs['action'] = esc_url( $action );
			$attrs['method'] = in_array( strtolower( $method ), [ 'get', 'post' ] ) ? $method : 'post';

			if ( ! isset( $attrs['id'] ) ) {
				$attrs['id'] = 'form-' . wp_rand();
			}

			return self::element( 'form', $attrs, $content );
		}

		/**
		 * Create an HTML fieldset element.
		 *
		 * @param string $content The content of the fieldset.
		 * @param string $legend  The legend text (optional).
		 * @param array  $attrs   Additional attributes for the fieldset.
		 *
		 * @return string The HTML string for the fieldset.
		 */
		public static function fieldset( string $content, string $legend = '', array $attrs = [] ): string {
			if ( ! empty( $legend ) ) {
				$content = self::element( 'legend', [], esc_html( $legend ) ) . $content;
			}

			return self::element( 'fieldset', $attrs, $content );
		}

		/**
		 * Create a form group with label and input.
		 *
		 * @param string $label      The label text.
		 * @param string $input_type The type of input.
		 * @param string $name       The input name attribute.
		 * @param array  $attrs      Additional attributes for the input.
		 *
		 * @return string The HTML string for the form group.
		 */
		public static function form_group( string $label, string $input_type, string $name, array $attrs = [] ): string {
			$id            = $attrs['id'] ?? 'field-' . wp_rand();
			$attrs['id']   = $id;
			$attrs['name'] = $name;

			$label_html = self::label( $id, esc_html( $label ) );
			$input_html = self::input( $input_type, $attrs );

			return self::div( $label_html . $input_html, [ 'class' => 'form-group' ] );
		}

		/**
		 * Table-related HTML creation methods.
		 */

		/**
		 * Create an HTML table element.
		 *
		 * @param array $data    Array of row data.
		 * @param array $headers Table headers.
		 * @param array $attrs   Additional attributes for the table.
		 *
		 * @return string The HTML string for the table.
		 */
		public static function table( array $data, array $headers = [], array $attrs = [] ): string {
			$content = '';

			if ( ! empty( $headers ) ) {
				$content .= self::thead( $headers );
			}

			$content .= self::tbody( $data );

			$default_attrs = [ 'class' => 'wp-list-table widefat' ];
			$attrs         = array_merge( $default_attrs, $attrs );

			return self::element( 'table', $attrs, $content );
		}

		/**
		 * Create an HTML table header.
		 *
		 * @param array $headers Array of header cells.
		 * @param array $attrs   Additional attributes for the thead.
		 *
		 * @return string The HTML string for the table header.
		 */
		public static function thead( array $headers, array $attrs = [] ): string {
			$header_cells = '';
			foreach ( $headers as $key => $header ) {
				$cell_attrs   = is_array( $header ) ? ( $header['attrs'] ?? [] ) : [];
				$cell_content = is_array( $header ) ? ( $header['content'] ?? '' ) : $header;
				$header_cells .= self::element( 'th', $cell_attrs, esc_html( $cell_content ) );
			}

			$header_row = self::element( 'tr', [], $header_cells );

			return self::element( 'thead', $attrs, $header_row );
		}

		/**
		 * Create an HTML table body.
		 *
		 * @param array $rows  Array of row data.
		 * @param array $attrs Additional attributes for the tbody.
		 *
		 * @return string The HTML string for the table body.
		 */
		public static function tbody( array $rows, array $attrs = [] ): string {
			$content = '';
			foreach ( $rows as $row ) {
				$cells = '';
				foreach ( $row as $cell ) {
					$cell_attrs   = is_array( $cell ) ? ( $cell['attrs'] ?? [] ) : [];
					$cell_content = is_array( $cell ) ? ( $cell['content'] ?? '' ) : $cell;
					$cells        .= self::element( 'td', $cell_attrs, esc_html( $cell_content ) );
				}
				$content .= self::element( 'tr', [], $cells );
			}

			return self::element( 'tbody', $attrs, $content );
		}

		/**
		 * Create an HTML video element.
		 *
		 * @param string|array $src   Video source URL or array of sources.
		 * @param array        $attrs Additional attributes for the video element.
		 *
		 * @return string The HTML string for the video element.
		 */
		public static function video( $src, array $attrs = [] ): string {
			$content = '';

			// Handle multiple sources
			if ( is_array( $src ) ) {
				foreach ( $src as $source ) {
					$type    = ! empty( $source['type'] ) ? $source['type'] : 'video/mp4';
					$content .= self::void_element( 'source', [
						'src'  => esc_url( $source['url'] ),
						'type' => $type
					] );
				}
			} else {
				$content .= self::void_element( 'source', [
					'src'  => esc_url( $src ),
					'type' => 'video/mp4'
				] );
			}

			$default_attrs = [
				'controls' => true,
				'width'    => '100%',
				'preload'  => 'metadata'
			];

			$attrs = array_merge( $default_attrs, $attrs );

			return self::element( 'video', $attrs, $content );
		}

		/**
		 * Create an HTML audio element.
		 *
		 * @param string|array $src   Audio source URL or array of sources.
		 * @param array        $attrs Additional attributes for the audio element.
		 *
		 * @return string The HTML string for the audio element.
		 */
		public static function audio( $src, array $attrs = [] ): string {
			$content = '';

			// Handle multiple sources
			if ( is_array( $src ) ) {
				foreach ( $src as $source ) {
					$type    = ! empty( $source['type'] ) ? $source['type'] : 'audio/mpeg';
					$content .= self::void_element( 'source', [
						'src'  => esc_url( $source['url'] ),
						'type' => $type
					] );
				}
			} else {
				$content .= self::void_element( 'source', [
					'src'  => esc_url( $src ),
					'type' => 'audio/mpeg'
				] );
			}

			$default_attrs = [
				'controls' => true,
				'preload'  => 'metadata'
			];

			$attrs = array_merge( $default_attrs, $attrs );

			return self::element( 'audio', $attrs, $content );
		}

		/**
		 * Navigation-related HTML creation methods.
		 */

		/**
		 * Create an HTML navigation element.
		 *
		 * @param string $content The content of the nav element.
		 * @param array  $attrs   Additional attributes for the nav element.
		 *
		 * @return string The HTML string for the nav element.
		 */
		public static function nav( string $content, array $attrs = [] ): string {
			return self::element( 'nav', $attrs, $content );
		}

		/**
		 * Create an HTML menu from an array of items.
		 *
		 * @param array $items Menu items array.
		 * @param array $attrs Additional attributes for the menu container.
		 *
		 * @return string The HTML string for the menu.
		 */
		public static function menu( array $items, array $attrs = [] ): string {
			$content = '';

			foreach ( $items as $item ) {
				$item_attrs   = $item['attrs'] ?? [];
				$item_content = $item['content'] ?? '';

				if ( ! empty( $item['url'] ) ) {
					$item_content = self::link( $item['url'], $item_content );
				}

				if ( ! empty( $item['children'] ) ) {
					$item_content .= self::menu( $item['children'] );
				}

				$content .= self::element( 'li', $item_attrs, $item_content );
			}

			$default_attrs = [ 'class' => 'menu' ];
			$attrs         = array_merge( $default_attrs, $attrs );

			return self::element( 'ul', $attrs, $content );
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
					$content .= self::link( $item['url'], esc_html( $item['text'] ) );
				} else {
					$content .= self::span( esc_html( $item['text'] ), [ 'class' => 'current' ] );
				}

				if ( ! $is_last ) {
					$content .= self::span( ' / ', [ 'class' => 'separator' ] );
				}
			}

			$default_attrs = [ 'class' => 'breadcrumbs' ];
			$attrs         = array_merge( $default_attrs, $attrs );

			return self::nav( $content, $attrs );
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
				$content  .= self::link( $prev_url, '← Previous', [ 'class' => 'prev' ] );
			}

			// Page numbers
			for ( $i = 1; $i <= $total_pages; $i ++ ) {
				if ( $i === $current_page ) {
					$content .= self::span( (string) $i, [ 'class' => 'current' ] );
				} else {
					$page_url = add_query_arg( 'page', $i, $base_url );
					$content  .= self::link( $page_url, (string) $i );
				}
			}

			// Next link
			if ( $current_page < $total_pages ) {
				$next_url = add_query_arg( 'page', $current_page + 1, $base_url );
				$content  .= self::link( $next_url, 'Next →', [ 'class' => 'next' ] );
			}

			$default_attrs = [ 'class' => 'pagination' ];
			$attrs         = array_merge( $default_attrs, $attrs );

			return self::nav( $content, $attrs );
		}

		/**
		 * Create an arbitrary HTML element.
		 *
		 * @param string $tag     The tag name of the element.
		 * @param array  $attrs   An associative array of attributes for the element.
		 * @param string $content The content of the element (for non-void elements).
		 *
		 * @return string The HTML string for the element.
		 */
		public static function element( string $tag, array $attrs = [], string $content = '' ): string {
			$attr_string  = self::build_attribute_string( $attrs );
			$allowed_html = wp_kses_allowed_html( 'post' );
			$content      = wp_kses( $content, $allowed_html );

			return "<{$tag}{$attr_string}>{$content}</{$tag}>";
		}

		/**
		 * Create an arbitrary void HTML element (elements without a closing tag).
		 *
		 * @param string $tag   The tag name of the void element.
		 * @param array  $attrs An associative array of attributes for the element.
		 *
		 * @return string The HTML string for the void element.
		 */
		public static function void_element( string $tag, array $attrs = [] ): string {
			$attr_string = self::build_attribute_string( $attrs );

			return "<{$tag}{$attr_string} />";
		}

		/**
		 * Build an attribute string from an associative array of attributes.
		 *
		 * @param array $attrs An associative array of attributes.
		 *
		 * @return string The attribute string.
		 */
		private static function build_attribute_string( array $attrs ): string {
			$attr_pairs = [];
			foreach ( $attrs as $key => $value ) {
				if ( $value === true ) {
					$attr_pairs[] = esc_attr( $key );
				} elseif ( $value !== false && $value !== null ) {
					$attr_pairs[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
				}
			}

			return $attr_pairs ? ' ' . implode( ' ', $attr_pairs ) : '';
		}

		/**
		 * Parse a style attribute string into an associative array.
		 *
		 * @param string $style_string The style attribute string.
		 *
		 * @return array An associative array of style properties.
		 */
		private static function parse_style_attribute( string $style_string ): array {
			$styles = [];
			$parts  = explode( ';', $style_string );
			foreach ( $parts as $part ) {
				$part = trim( $part );
				if ( $part ) {
					list( $property, $value ) = explode( ':', $part, 2 );
					$styles[ trim( $property ) ] = trim( $value );
				}
			}

			return $styles;
		}

		/**
		 * Build a style string from an associative array of style properties.
		 *
		 * @param array $styles An associative array of style properties.
		 *
		 * @return string The built style string.
		 */
		private static function build_style_string( array $styles ): string {
			$style_parts = [];
			foreach ( $styles as $property => $value ) {
				$style_parts[] = $property . ': ' . $value;
			}

			return implode( '; ', $style_parts );
		}

		/**
		 * Merge new styles with existing styles.
		 *
		 * @param array  $new_styles     The new styles to add.
		 * @param string $existing_style The existing style string.
		 *
		 * @return string The merged style string.
		 */
		private static function merge_styles( array $new_styles, string $existing_style ): string {
			$existing_styles = self::parse_style_attribute( $existing_style );
			$merged_styles   = array_merge( $new_styles, $existing_styles );

			return self::build_style_string( $merged_styles );
		}

	}
endif;