<?php
/**
 * Color Utilities
 *
 * This class provides utility functions for working with colors, such as converting
 * hex to RGB/RGBA, adjusting brightness, blending colors, and generating contrasting
 * colors. It also supports conversion to RGBA and sanitization of hex color codes.
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
 * Check if the class `Color` is defined, and if not, define it.
 */
if ( ! class_exists( 'Color' ) ) :

	/**
	 * Color Utilities
	 *
	 * Provides utility functions for working with colors, such as converting
	 * hex to RGB/RGBA, adjusting brightness, blending colors, and generating contrasting
	 * colors. It also supports conversion to RGBA and sanitization of hex color codes.
	 */
	class Color {

		/**
		 * Convert a hex color to RGB.
		 *
		 * @param string $hex       The hex color code.
		 * @param bool   $as_string Whether to return the result as a string.
		 *
		 * @return array|string|null An array with 'r', 'g', and 'b' keys, a string "r,g,b", or null if invalid.
		 */
		public static function hex_to_rgb( string $hex, bool $as_string = false ) {
			$hex = Sanitize::hex( $hex );
			if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) {
				return null;
			}

			$rgb = [
				'r' => hexdec( substr( $hex, 0, 2 ) ),
				'g' => hexdec( substr( $hex, 2, 2 ) ),
				'b' => hexdec( substr( $hex, 4, 2 ) )
			];

			return $as_string ? implode( ',', $rgb ) : $rgb;
		}

		/**
		 * Convert a hex color to RGBA.
		 *
		 * @param string $hex       The hex color code.
		 * @param float  $alpha     Alpha value (0-1).
		 * @param bool   $as_string Whether to return the result as a string.
		 *
		 * @return array|string|null An array with 'r', 'g', 'b', and 'a' keys, a string "r,g,b,a" or "rgba(r,g,b,a)", or null if invalid.
		 */
		public static function hex_to_rgba( string $hex, float $alpha = 1.0, bool $as_string = false ) {
			$rgb = self::hex_to_rgb( $hex );
			if ( $rgb === null ) {
				return null;
			}

			$alpha = max( 0, min( 1, $alpha ) );
			$rgba  = array_merge( $rgb, [ 'a' => $alpha ] );

			if ( $as_string ) {
				return sprintf( 'rgba(%d,%d,%d,%f)', $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a'] );
			}

			return $rgba;
		}

		/**
		 * Convert RGB to hex color.
		 *
		 * @param int $r Red value (0-255)
		 * @param int $g Green value (0-255)
		 * @param int $b Blue value (0-255)
		 *
		 * @return string Hex color code
		 */
		public static function rgb_to_hex( int $r, int $g, int $b ): string {
			$r = max( 0, min( 255, $r ) );
			$g = max( 0, min( 255, $g ) );
			$b = max( 0, min( 255, $b ) );

			return sprintf( "#%02x%02x%02x", $r, $g, $b );
		}

		/**
		 * Convert RGBA to hex color.
		 *
		 * @param int   $r Red value (0-255)
		 * @param int   $g Green value (0-255)
		 * @param int   $b Blue value (0-255)
		 * @param float $a Alpha value (0-1)
		 *
		 * @return string Hex color code with alpha
		 */
		public static function rgba_to_hex( int $r, int $g, int $b, float $a ): string {
			$r = max( 0, min( 255, $r ) );
			$g = max( 0, min( 255, $g ) );
			$b = max( 0, min( 255, $b ) );
			$a = max( 0, min( 1, $a ) );

			$alpha = round( $a * 255 );

			return sprintf( "#%02x%02x%02x%02x", $r, $g, $b, $alpha );
		}

		/**
		 * Get the contrasting color (black or white) for a given hex color.
		 *
		 * @param string $hex   The hex color code.
		 * @param string $dark  Dark color (default: black)
		 * @param string $light Light color (default: white)
		 *
		 * @return string Contrasting color (hex)
		 */
		public static function get_contrast_color( string $hex, string $dark = '#000000', string $light = '#ffffff' ): string {
			$rgb = self::hex_to_rgb( $hex );
			if ( $rgb === null ) {
				return $light;
			}

			$luminance = ( 0.299 * $rgb['r'] + 0.587 * $rgb['g'] + 0.114 * $rgb['b'] ) / 255;

			return $luminance > 0.55 ? $dark : $light;
		}

		/**
		 * Blend multiple hex colors.
		 *
		 * @param array $colors Array of hex color codes
		 *
		 * @return string Blended hex color
		 */
		public static function blend_colors( array $colors ): string {
			$rgb_colors = array_map( [ self::class, 'hex_to_rgb' ], $colors );
			$rgb_colors = array_filter( $rgb_colors );

			if ( empty( $rgb_colors ) ) {
				return '#000000';
			}

			$count = count( $rgb_colors );
			$r     = $g = $b = 0;

			foreach ( $rgb_colors as $color ) {
				$r += pow( $color['r'] / 255, 2.2 );
				$g += pow( $color['g'] / 255, 2.2 );
				$b += pow( $color['b'] / 255, 2.2 );
			}

			$r = round( pow( $r / $count, 1 / 2.2 ) * 255 );
			$g = round( pow( $g / $count, 1 / 2.2 ) * 255 );
			$b = round( pow( $b / $count, 1 / 2.2 ) * 255 );

			return self::rgb_to_hex( (int) $r, (int) $g, (int) $b );
		}

		/**
		 * Adjust the brightness of a color.
		 *
		 * @param string $hex   The hex color code.
		 * @param int    $steps Positive to lighten, negative to darken
		 *
		 * @return string Adjusted hex color
		 */
		public static function adjust_brightness( string $hex, int $steps ): string {
			$rgb = self::hex_to_rgb( $hex );
			if ( $rgb === null ) {
				return $hex;
			}

			foreach ( $rgb as &$color ) {
				$color = max( 0, min( 255, $color + $steps ) );
			}

			return self::rgb_to_hex( $rgb['r'], $rgb['g'], $rgb['b'] );
		}

		/**
		 * Calculate the relative luminance of a color.
		 *
		 * @param string $hex The hex color code.
		 *
		 * @return float The relative luminance value (0-1)
		 */
		public static function get_relative_luminance( string $hex ): float {
			$rgb = self::hex_to_rgb( $hex );
			if ( $rgb === null ) {
				return 0;
			}

			$rgb = array_map( function ( $val ) {
				$val = $val / 255;

				return $val <= 0.03928 ? $val / 12.92 : pow( ( $val + 0.055 ) / 1.055, 2.4 );
			}, $rgb );

			return 0.2126 * $rgb['r'] + 0.7152 * $rgb['g'] + 0.0722 * $rgb['b'];
		}

		/**
		 * Calculate the color contrast ratio between two colors.
		 *
		 * @param string $hex1 The first hex color code.
		 * @param string $hex2 The second hex color code.
		 *
		 * @return float The contrast ratio (1-21)
		 */
		public static function get_contrast_ratio( string $hex1, string $hex2 ): float {
			$lum1 = self::get_relative_luminance( $hex1 );
			$lum2 = self::get_relative_luminance( $hex2 );

			$brightest = max( $lum1, $lum2 );
			$darkest   = min( $lum1, $lum2 );

			return ( $brightest + 0.05 ) / ( $darkest + 0.05 );
		}

		/**
		 * Determine if a color is considered "dark".
		 *
		 * @param string $hex       The hex color code.
		 * @param float  $threshold The threshold for considering a color dark (0-1, default 0.5).
		 *
		 * @return bool True if the color is considered dark, false otherwise.
		 */
		public static function is_dark( string $hex, float $threshold = 0.5 ): bool {
			$luminance = self::get_relative_luminance( $hex );

			return $luminance < $threshold;
		}

		/**
		 * Determine if a color is considered "light".
		 *
		 * @param string $hex       The hex color code.
		 * @param float  $threshold The threshold for considering a color light (0-1, default 0.5).
		 *
		 * @return bool True if the color is considered light, false otherwise.
		 */
		public static function is_light( string $hex, float $threshold = 0.5 ): bool {
			return ! self::is_dark( $hex, $threshold );
		}

		/**
		 * Get appropriate text color (black or white) based on background color.
		 *
		 * @param string $background_hex The background color in hex format.
		 * @param string $dark_color     The color to use on light backgrounds (default: black).
		 * @param string $light_color    The color to use on dark backgrounds (default: white).
		 * @param float  $threshold      The threshold for considering a color dark/light (0-1, default 0.5).
		 *
		 * @return string The appropriate text color (hex code).
		 */
		public static function get_readable_text_color( string $background_hex, string $dark_color = '#000000', string $light_color = '#ffffff', float $threshold = 0.5 ): string {
			return self::is_dark( $background_hex, $threshold ) ? $light_color : $dark_color;
		}

	}
endif;