<?php
/**
 * HTML Creator Utilities
 *
 * This class provides static methods to create various HTML elements
 * in a WordPress-friendly way. It includes methods for generating links,
 * buttons, divs, spans, lists, images, and other common HTML elements
 * while ensuring attributes are properly escaped and following best practices.
 *
 * @package       ArrayPress/Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

namespace ArrayPress\Utils;

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