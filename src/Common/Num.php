<?php
/**
 * Number Utility Class
 *
 * This class provides utility methods for working with numbers in various contexts.
 * It includes functions for formatting and manipulating numbers in ways that are
 * commonly needed in application development, focusing on non-mathematical operations.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

class Num {

	/**
	 * Check if a number is within a range.
	 *
	 * @param float $min    The minimum value of the range.
	 * @param float $max    The maximum value of the range.
	 * @param float $number The number to check.
	 *
	 * @return bool
	 */
	public static function in_range( float $min, float $max, float $number ): bool {
		return $number >= $min && $number <= $max;
	}

	/**
	 * Validate if a value is a positive integer.
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return bool True if the value is a positive integer, false otherwise.
	 */
	public static function is_positive_int( $value ): bool {
		return is_int( $value ) && $value > 0;
	}

	/**
	 * Validate if a value is a negative integer.
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return bool True if the value is a negative integer, false otherwise.
	 */
	public static function is_negative_int( $value ): bool {
		return is_int( $value ) && $value < 0;
	}

	/**
	 * Validate if a value is a positive float.
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return bool True if the value is a positive float, false otherwise.
	 */
	public static function is_positive_float( $value ): bool {
		return is_float( $value ) && $value > 0;
	}

	/**
	 * Validate if a value is a negative float.
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return bool True if the value is a negative float, false otherwise.
	 */
	public static function is_negative_float( $value ): bool {
		return is_float( $value ) && $value < 0;
	}

	/**
	 * Get the ordinal suffix for a number (st, nd, rd, th).
	 *
	 * @param int $number The number to get the suffix for.
	 *
	 * @return string The ordinal suffix.
	 */
	public static function ordinal_suffix( int $number ): string {
		$suffixes = [ 'th', 'st', 'nd', 'rd' ];
		$mod100   = $number % 100;

		return $number . ( $mod100 >= 11 && $mod100 <= 13 ? 'th' : $suffixes[ $number % 10 ] ?? 'th' );
	}

	/**
	 * Format a number with grouped thousands.
	 *
	 * @param float  $number        The number to format.
	 * @param int    $decimals      The number of decimal points.
	 * @param string $dec_point     Decimal point separator.
	 * @param string $thousands_sep Thousands separator.
	 *
	 * @return string Formatted number.
	 */
	public static function format_number( float $number, int $decimals = 2, string $dec_point = '.', string $thousands_sep = ',' ): string {
		return number_format( $number, $decimals, $dec_point, $thousands_sep );
	}

	/**
	 * Convert a number to its word representation.
	 *
	 * @param int $number The number to convert (up to 999,999,999).
	 *
	 * @return string The word representation of the number.
	 */
	public static function to_words( int $number ): string {
		$units  = [ '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine' ];
		$teens  = [
			'ten',
			'eleven',
			'twelve',
			'thirteen',
			'fourteen',
			'fifteen',
			'sixteen',
			'seventeen',
			'eighteen',
			'nineteen'
		];
		$tens   = [ '', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety' ];
		$scales = [ '', 'thousand', 'million' ];

		if ( $number === 0 ) {
			return 'zero';
		}

		$words      = [];
		$scaleIndex = 0;

		while ( $number > 0 ) {
			$groupOfThree = $number % 1000;
			if ( $groupOfThree > 0 ) {
				$groupWords = [];

				$hundreds = floor( $groupOfThree / 100 );
				if ( $hundreds > 0 ) {
					$groupWords[] = $units[ $hundreds ] . ' hundred';
				}

				$tensAndUnits = $groupOfThree % 100;
				if ( $tensAndUnits >= 20 ) {
					$tensDigit    = floor( $tensAndUnits / 10 );
					$unitsDigit   = $tensAndUnits % 10;
					$groupWords[] = $tens[ $tensDigit ] . ( $unitsDigit > 0 ? '-' . $units[ $unitsDigit ] : '' );
				} elseif ( $tensAndUnits >= 10 ) {
					$groupWords[] = $teens[ $tensAndUnits - 10 ];
				} elseif ( $tensAndUnits > 0 ) {
					$groupWords[] = $units[ $tensAndUnits ];
				}

				$words[] = implode( ' ', $groupWords ) . ( $scaleIndex > 0 ? ' ' . $scales[ $scaleIndex ] : '' );
			}

			$number = floor( $number / 1000 );
			$scaleIndex ++;
		}

		return implode( ' ', array_reverse( $words ) );
	}

	/**
	 * Determine if a number is even.
	 *
	 * @param int $number The number to check.
	 *
	 * @return bool True if even, false if odd.
	 */
	public static function is_even( int $number ): bool {
		return $number % 2 === 0;
	}

	/**
	 * Pad a number with leading zeros.
	 *
	 * @param int $number The number to pad.
	 * @param int $length The desired length of the resulting string.
	 *
	 * @return string The padded number.
	 */
	public static function zero_pad( int $number, int $length ): string {
		return str_pad( (string) $number, $length, '0', STR_PAD_LEFT );
	}

	/**
	 * Convert a number to Roman numerals.
	 *
	 * @param int $number The number to convert (1-3999).
	 *
	 * @return string The Roman numeral representation.
	 */
	public static function to_roman( int $number ): string {
		if ( $number < 1 || $number > 3999 ) {
			return 'Number out of range (1-3999)';
		}

		$romanNumerals = [
			'M'  => 1000,
			'CM' => 900,
			'D'  => 500,
			'CD' => 400,
			'C'  => 100,
			'XC' => 90,
			'L'  => 50,
			'XL' => 40,
			'X'  => 10,
			'IX' => 9,
			'V'  => 5,
			'IV' => 4,
			'I'  => 1
		];

		$result = '';
		foreach ( $romanNumerals as $roman => $value ) {
			while ( $number >= $value ) {
				$result .= $roman;
				$number -= $value;
			}
		}

		return $result;
	}

	/**
	 * Check if a number is prime.
	 *
	 * @param int $number The number to check.
	 *
	 * @return bool
	 */
	public static function is_prime( int $number ): bool {
		if ( $number <= 1 ) {
			return false;
		}
		for ( $i = 2; $i <= sqrt( $number ); $i ++ ) {
			if ( $number % $i == 0 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Round a number to the nearest multiple of a given step.
	 *
	 * @param float $number The number to round.
	 * @param float $step   The step to round to.
	 *
	 * @return float
	 */
	public static function round_to_nearest( float $number, float $step = 1.0 ): float {
		return round( $number / $step ) * $step;
	}

	/**
	 * Clamp a number between a minimum and maximum value.
	 *
	 * @param float $number The number to clamp.
	 * @param float $min    The minimum value.
	 * @param float $max    The maximum value.
	 *
	 * @return float
	 */
	public static function clamp( float $number, float $min, float $max ): float {
		return max( $min, min( $max, $number ) );
	}

}

// Add class aliases
class_alias( Num::class, 'ArrayPress\Utils\Common\Number' );
class_alias( Num::class, 'ArrayPress\Utils\Common\Numeric' );