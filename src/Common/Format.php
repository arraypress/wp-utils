<?php
/**
 * Format Utilities for WordPress
 *
 * This file contains the Format class, which provides a set of utility functions
 * for formatting various types of data in WordPress applications. It offers methods
 * for formatting numbers, dates, percentages, phone numbers, and text for different
 * contexts such as HTML, URLs, and JavaScript.
 *
 * @package       ArrayPress\Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

/**
 * Check if the class `Format` is defined, and if not, define it.
 */
if ( ! class_exists( 'Format' ) ) :

	/**
	 * Format Utility Functions
	 *
	 * Provides static utility functions for formatting various types of data.
	 * This class offers methods for formatting numbers, dates, percentages,
	 * phone numbers, and text for use in different contexts such as HTML,
	 * URLs, and JavaScript. It's designed to ensure consistent and safe
	 * formatting across WordPress themes and plugins.
	 */
	class Format {

		/**
		 * The HTML entity for an em dash.
		 */
		const MDASH = '&mdash;';

		/**
		 * Outputs or returns a value, using em dash if the value is empty.
		 *
		 * @param string $value The text string.
		 * @param bool   $echo  Whether to echo the value. Default true.
		 *
		 * @return string|null The processed value if $echo is false, otherwise null.
		 */
		public static function mdash( string $value, bool $echo = true ): ?string {
			$value = ! empty( $value ) ? $value : self::MDASH;
			if ( $echo ) {
				echo $value;

				return null;
			}

			return $value;
		}

		/**
		 * Format numeric values.
		 *
		 * @param mixed $value    The value to be formatted.
		 * @param int   $decimals The number of decimal points.
		 *
		 * @return string The formatted numeric value or em dash.
		 */
		public static function numeric( $value, int $decimals = 0 ): string {
			if ( is_numeric( $value ) ) {
				return number_format_i18n( (float) $value, $decimals );
			}

			return self::MDASH;
		}

		/**
		 * Format a number with grouped thousands.
		 *
		 * @param float  $number        The number to format.
		 * @param int    $decimals      Number of decimal points. Default 2.
		 * @param string $dec_point     Decimal point character. Default '.'.
		 * @param string $thousands_sep Thousands separator character. Default ','.
		 *
		 * @return string Formatted number.
		 */
		public static function number( float $number, int $decimals = 2, string $dec_point = '.', string $thousands_sep = ',' ): string {
			return number_format( $number, $decimals, $dec_point, $thousands_sep );
		}

		/**
		 * Format the rating as a string.
		 *
		 * @param int $rating The rating value.
		 *
		 * @return string Formatted rating string.
		 */
		public static function rating( int $rating ): string {
			if ( empty( $rating ) ) {
				return __( 'No Rating', 'arraypress' );
			}

			/* translators: %s: rating value */
			return sprintf( _n( '%s Star', '%s Stars', $rating, 'arraypress' ), $rating );
		}

		/**
		 * Format a date.
		 *
		 * @param string $date      The date string.
		 * @param string $format    The format for the date. Default 'F j, Y'.
		 * @param bool   $translate Whether to translate the date string. Default true.
		 *
		 * @return string Formatted date string.
		 */
		public static function date( string $date, string $format = 'F j, Y', bool $translate = true ): string {
			$timestamp = strtotime( $date );

			return $translate ? date_i18n( $format, $timestamp ) : date( $format, $timestamp );
		}

		/**
		 * Format a phone number.
		 *
		 * @param string $phone The phone number to format.
		 *
		 * @return string Formatted phone number.
		 */
		public static function phone( string $phone ): string {
			$phone = preg_replace( '/[^0-9]/', '', $phone );
			if ( strlen( $phone ) === 10 ) {
				return preg_replace( '/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $phone );
			}

			return $phone;
		}

		/**
		 * Format text as HTML.
		 *
		 * @param string $text The text to format.
		 *
		 * @return string Formatted HTML.
		 */
		public static function html( string $text ): string {
			return wpautop( wptexturize( $text ) );
		}

	}
endif;