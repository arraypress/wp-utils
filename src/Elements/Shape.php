<?php
/**
 * Field Class: WordPress Form Element Generator
 *
 * A comprehensive utilities class for generating HTML form elements in WordPress. This class provides
 * methods for creating shape elements with consistent styling and structure.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.2.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Elements;

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
		// Add the base class
		$attrs['class'] = isset( $attrs['class'] )
			? $attrs['class'] . ' wp-shape wp-shape-circle'
			: 'wp-shape wp-shape-circle';

		// Keep only the dynamic background-color style
		$styles = [ 'background-color' => esc_attr( $color ) ];

		// Merge with any existing styles
		if ( isset( $attrs['style'] ) ) {
			$existing_styles = Element::parse_style_attribute( $attrs['style'] );
			$styles          = array_merge( $styles, $existing_styles );
		}

		$attrs['style'] = Element::build_style_string( $styles );

		Field::ensure_styles();

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
		// Add the base class
		$attrs['class'] = isset( $attrs['class'] )
			? $attrs['class'] . ' wp-shape wp-shape-square'
			: 'wp-shape wp-shape-square';

		// Keep only the dynamic styles
		$styles = [
			'background-color' => esc_attr( $color ),
			'width'            => $size,
			'height'           => $size
		];

		// Merge with any existing styles
		if ( isset( $attrs['style'] ) ) {
			$existing_styles = Element::parse_style_attribute( $attrs['style'] );
			$styles          = array_merge( $styles, $existing_styles );
		}

		$attrs['style'] = Element::build_style_string( $styles );

		Field::ensure_styles();

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
		// Add the base classes
		$attrs['class'] = isset( $attrs['class'] )
			? $attrs['class'] . ' wp-shape wp-shape-triangle wp-shape-triangle-' . $direction
			: 'wp-shape wp-shape-triangle wp-shape-triangle-' . $direction;

		// Calculate border size for equal sides
		$border_size = $size;

		// Set up the styles for the triangle
		$styles = [
			'border-style' => 'solid',
			'border-width' => $border_size,
			'border-color' => 'transparent'
		];

		// Add direction-specific color
		switch ( $direction ) {
			case 'up':
				$styles['border-bottom-color'] = $color;
				$styles['border-top-width']    = '0';
				break;
			case 'down':
				$styles['border-top-color']    = $color;
				$styles['border-bottom-width'] = '0';
				break;
			case 'left':
				$styles['border-right-color'] = $color;
				$styles['border-left-width']  = '0';
				break;
			case 'right':
				$styles['border-left-color']  = $color;
				$styles['border-right-width'] = '0';
				break;
		}

		// Merge with any existing styles
		if ( isset( $attrs['style'] ) ) {
			$existing_styles = Element::parse_style_attribute( $attrs['style'] );
			$styles          = array_merge( $styles, $existing_styles );
		}

		$attrs['style'] = Element::build_style_string( $styles );

		Field::ensure_styles();

		return Element::div( '', $attrs );
	}

}