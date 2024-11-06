<?php
/**
 * Field Class: WordPress Form Element Generator
 *
 * A comprehensive utilities class for generating HTML form elements in WordPress. This class provides
 * a collection of static methods designed to create standardized, accessible, and secure form fields
 * and form structures. It integrates seamlessly with WordPress's core functionality while maintaining
 * proper escaping and security practices.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.2.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\HTML;

class Shape {

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
			$existing_styles = Element::parse_style_attribute( $attrs['style'] );
			$merged_styles   = array_merge( $default_styles, $existing_styles );
		} else {
			$merged_styles = $default_styles;
		}

		// Convert merged styles back to a string
		$attrs['style'] = Element::build_style_string( $merged_styles );

		// Use the div method to create the color circle
		return Element::div( '', $attrs );
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

		$attrs['style'] = Element::merge_styles( $styles, $attrs['style'] ?? '' );

		return Element::div( '', $attrs );
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

		$attrs['style'] = Element::merge_styles( $styles, $attrs['style'] ?? '' );

		return Element::div( '', $attrs );
	}

}