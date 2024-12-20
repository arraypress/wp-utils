<?php
/**
 * Validation Utilities for WordPress
 *
 * This class provides a comprehensive set of utility functions for validating
 * different types of data, including emails, URLs, IP addresses, usernames,
 * passwords, UUIDs, hex colors, and more. It also includes comparison operator
 * validation and pattern matching.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

use ArrayPress\Utils\I18n\TimeUnits;
use DateTime;

class Validate {

	/**
	 * Regular expression for validating UUIDs.
	 *
	 * Matches standard UUID format.
	 */
	private const REGEX_UUID = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';


	/**
	 * Basic comparison operators.
	 *
	 * These operators are used for equality and inequality comparisons across various types.
	 */
	private const BASIC_OPERATORS = [ '==', '!=', '===', '!==' ];

	/**
	 * Relational comparison operators.
	 *
	 * These operators are used for comparing numeric, date, or version values.
	 */
	private const RELATIONAL_OPERATORS = [ '>', '>=', '<', '<=' ];

	/**
	 * String-specific comparison operators.
	 *
	 * These operators are used for string-specific comparisons such as containment or pattern matching.
	 */
	private const STRING_OPERATORS = [ 'contains', 'not_contains', 'starts_with', 'ends_with' ];

	/**
	 * Extended operators for boolean comparisons.
	 *
	 * These operators are used specifically for boolean values.
	 */
	private const BOOLEAN_OPERATORS = [ 'is', 'is_not', 'equal_to', 'not_equal_to' ];

	/**
	 * All valid operators combined.
	 *
	 * This combines all operator groups for validation.
	 */
	private const VALID_OPERATORS = [
		...self::BASIC_OPERATORS,
		...self::RELATIONAL_OPERATORS,
		...self::STRING_OPERATORS,
		...self::BOOLEAN_OPERATORS
	];

	/**
	 * Validate a comparison operator.
	 *
	 * @param string $operator The comparison operator to validate.
	 *
	 * @return bool True if the operator is valid, false otherwise.
	 */
	public static function is_operator( string $operator ): bool {
		return in_array( $operator, self::VALID_OPERATORS, true );
	}

	/**
	 * Validate an email address.
	 *
	 * @param string $email The email address to validate.
	 *
	 * @return bool True if the email is valid, false otherwise.
	 */
	public static function is_email( string $email ): bool {
		return (bool) is_email( $email );
	}

	/**
	 * Validate if a value is a valid discount type (flat or percentage).
	 *
	 * @param string $type The discount type to validate.
	 *
	 * @return bool True if the discount type is valid, false otherwise.
	 */
	public static function is_discount_type( string $type ): bool {
		return in_array( strtolower( $type ), [ 'flat', 'percentage' ], true );
	}

	/**
	 * Validate if a value is a valid percentage (0-100).
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return bool True if the value is a valid percentage, false otherwise.
	 */
	public static function is_percentage( $value ): bool {
		return is_numeric( $value ) && $value >= 0 && $value <= 100;
	}

	/**
	 * Validate if a string is a valid slug.
	 *
	 * @param string $slug The slug to validate.
	 *
	 * @return bool True if the slug is valid, false otherwise.
	 */
	public static function is_slug( string $slug ): bool {
		return (bool) preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug );
	}

	/**
	 * Validate if a value is within a specified range.
	 *
	 * @param mixed $value The value to validate.
	 * @param mixed $min   The minimum allowed value.
	 * @param mixed $max   The maximum allowed value.
	 *
	 * @return bool True if the value is within the specified range, false otherwise.
	 */
	public static function is_within_range( $value, $min, $max ): bool {
		return $value >= $min && $value <= $max;
	}

	/**
	 * Validate if a value represents an active or inactive status.
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return bool True if the value is a valid active/inactive status, false otherwise.
	 */
	public static function is_status( $value ): bool {
		$valid_statuses = [ 'active', 'inactive', '1', '0', 1, 0, true, false ];

		return in_array( $value, $valid_statuses, true );
	}

	/**
	 * Validate a URL.
	 *
	 * @param string $url The URL to validate.
	 *
	 * @return bool True if the URL is valid, false otherwise.
	 */
	public static function is_url( string $url ): bool {
		return filter_var( $url, FILTER_VALIDATE_URL ) === false;
	}

	/**
	 * Validate an IP address (IPv4 or IPv6).
	 *
	 * @param string $ip The IP address to validate.
	 *
	 * @return bool True if the IP is valid, false otherwise.
	 */
	public static function is_ip( string $ip ): bool {
		return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) !== false;
	}

	/**
	 * Validate a strong password.
	 *
	 * @param string $password          The password to validate.
	 * @param int    $min_length        Minimum length of the password (default: 8)
	 * @param bool   $require_uppercase Require at least one uppercase letter (default: true)
	 * @param bool   $require_lowercase Require at least one lowercase letter (default: true)
	 * @param bool   $require_number    Require at least one number (default: true)
	 * @param bool   $require_special   Require at least one special character (default: true)
	 *
	 * @return bool True if the password meets the specified strength criteria, false otherwise.
	 */
	public static function is_strong_password(
		string $password,
		int $min_length = 8,
		bool $require_uppercase = true,
		bool $require_lowercase = true,
		bool $require_number = true,
		bool $require_special = true
	): bool {
		if ( strlen( $password ) < $min_length ) {
			return false;
		}

		if ( $require_uppercase && ! preg_match( '/[A-Z]/', $password ) ) {
			return false;
		}

		if ( $require_lowercase && ! preg_match( '/[a-z]/', $password ) ) {
			return false;
		}

		if ( $require_number && ! preg_match( '/\d/', $password ) ) {
			return false;
		}

		if ( $require_special && ! preg_match( '/[^A-Za-z0-9]/', $password ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate a date string.
	 *
	 * @param string $date   The date string to validate.
	 * @param string $format The expected date format (default: 'Y-m-d').
	 *
	 * @return bool True if the date is valid, false otherwise.
	 */
	public static function is_date( string $date, string $format = 'Y-m-d' ): bool {
		$d = DateTime::createFromFormat( $format, $date );

		return $d && $d->format( $format ) === $date;
	}

	/**
	 * Validate a time string.
	 *
	 * @param string $time   The time string to validate.
	 * @param string $format The expected time format (default: 'H:i').
	 *
	 * @return bool True if the time is valid, false otherwise.
	 */
	public static function is_time( string $time, string $format = 'H:i' ): bool {
		$d = DateTime::createFromFormat( $format, $time );

		return $d && $d->format( $format ) === $time;
	}

	/**
	 * Validate a username.
	 *
	 * @param string $username The username to validate.
	 *
	 * @return bool True if the username is valid, false otherwise.
	 */
	public static function is_username( string $username ): bool {
		return validate_username( $username );
	}

	/**
	 * Validate if a value is a valid hex color.
	 *
	 * @param string $color The color to validate.
	 *
	 * @return bool True if the color is a valid hex color, false otherwise.
	 */
	public static function is_hex_color( string $color ): bool {
		$color = ltrim( $color, '#' );

		return ctype_xdigit( $color ) && ( strlen( $color ) === 3 || strlen( $color ) === 6 );
	}

	/**
	 * Validate if a value is a valid JSON string.
	 *
	 * @param string $string The string to validate.
	 *
	 * @return bool True if the string is valid JSON, false otherwise.
	 */
	public static function is_json( string $string ): bool {
		json_decode( $string );

		return ( json_last_error() === JSON_ERROR_NONE );
	}

	/**
	 * Validate if a file exists and is readable.
	 *
	 * @param string $filepath The file path to validate.
	 *
	 * @return bool True if the file exists and is readable, false otherwise.
	 */
	public static function is_readable_file( string $filepath ): bool {
		return is_readable( $filepath );
	}

	/**
	 * Validate if a directory exists and is writable.
	 *
	 * @param string $dirpath The directory path to validate.
	 *
	 * @return bool True if the directory exists and is writable, false otherwise.
	 */
	public static function is_writable_directory( string $dirpath ): bool {
		return is_dir( $dirpath ) && is_writable( $dirpath );
	}

	/**
	 * Validate if a value is a valid timezone.
	 *
	 * @param string $timezone The timezone to validate.
	 *
	 * @return bool True if the timezone is valid, false otherwise.
	 */
	public static function is_valid_timezone( string $timezone ): bool {
		return in_array( $timezone, \DateTimeZone::listIdentifiers() );
	}

	/**
	 * Validate if a string is a valid UUID.
	 *
	 * @param string $uuid The UUID to validate.
	 *
	 * @return bool True if the UUID is valid, false otherwise.
	 */
	public static function is_uuid( string $uuid ): bool {
		return (bool) preg_match( self::REGEX_UUID, $uuid );
	}

	/**
	 * Validate if a value is a valid credit card number using the Luhn algorithm.
	 *
	 * @param string $number The credit card number to validate.
	 *
	 * @return bool True if the number is a valid credit card number, false otherwise.
	 */
	public static function is_credit_card_number( string $number ): bool {
		$number = preg_replace( '/\D/', '', $number );
		$length = strlen( $number );
		$parity = $length % 2;
		$sum    = 0;

		for ( $i = $length - 1; $i >= 0; $i -- ) {
			$digit = (int) $number[ $i ];
			if ( $i % 2 === $parity ) {
				$digit *= 2;
				if ( $digit > 9 ) {
					$digit -= 9;
				}
			}
			$sum += $digit;
		}

		return ( $sum % 10 === 0 );
	}

	/**
	 * Checks whether a candidate string is a valid regular expression.
	 *
	 * @param string $candidate The candidate string to check.
	 *
	 * @return bool Whether the candidate string is a valid regular expression.
	 */
	public static function is_valid_regex( string $candidate ): bool {
		if ( strlen( $candidate ) < 3 ) {
			return false;
		}

		$delimiter = $candidate[0];
		if ( $delimiter !== substr( $candidate, - 1 ) ) {
			return false;
		}

		// Use set_error_handler to catch warnings
		set_error_handler( function () {
		}, E_WARNING );
		$is_valid = preg_match( $candidate, '' ) !== false;
		restore_error_handler();

		return $is_valid;
	}

	/**
	 * Validate ISBN-10 number.
	 *
	 * @param string $isbn The ISBN-10 number to validate.
	 *
	 * @return bool Whether the ISBN-10 is valid.
	 */
	public static function is_isbn10( string $isbn ): bool {
		if ( strlen( $isbn ) != 10 ) {
			return false;
		}

		$check = 0;
		for ( $i = 0; $i < 9; $i ++ ) {
			if ( $isbn[ $i ] == 'X' ) {
				return false;
			}
			$check += ( 10 - $i ) * intval( $isbn[ $i ] );
		}

		$check += ( $isbn[9] == 'X' ) ? 10 : intval( $isbn[9] );

		return ( $check % 11 == 0 );
	}

	/**
	 * Validate ISBN-13 number.
	 *
	 * @param string $isbn The ISBN-13 number to validate.
	 *
	 * @return bool Whether the ISBN-13 is valid.
	 */
	public static function is_isbn13( string $isbn ): bool {
		if ( strlen( $isbn ) != 13 ) {
			return false;
		}

		$check = 0;
		for ( $i = 0; $i < 12; $i ++ ) {
			$check += ( $i % 2 == 0 ) ? intval( $isbn[ $i ] ) : intval( $isbn[ $i ] ) * 3;
		}

		return ( 10 - ( $check % 10 ) ) % 10 == intval( $isbn[12] );
	}

	/**
	 * Check if a value is in a list of allowed values.
	 *
	 * @param mixed $value          The value to check.
	 * @param array $allowed_values The list of allowed values.
	 * @param bool  $case_sensitive Whether the comparison should be case-sensitive (default: false).
	 *
	 * @return bool Whether the value is in the list of allowed values.
	 */
	public static function is_valid_option( $value, array $allowed_values, bool $case_sensitive = false ): bool {
		$value = Sanitize::clean( $value );

		if ( ! $case_sensitive ) {
			$value          = strtolower( $value );
			$allowed_values = array_map( 'strtolower', $allowed_values );
		}

		return in_array( $value, $allowed_values );
	}

	/**
	 * Validate a time unit value.
	 *
	 * Wrapper method that validates a string contains a number with an optional time unit.
	 * Uses is_valid_unit() with time-specific configuration.
	 *
	 * @param string $value        The value to validate (e.g., "10days", "5months", "100")
	 * @param bool   $require_unit Whether to require a unit (default: false)
	 *
	 * @return bool True if the value contains valid number and time unit, false otherwise
	 */
	public static function is_time_unit( string $value, bool $require_unit = false ): bool {
		$allowed_units = array_keys( TimeUnits::get_date_ranges() );

		return self::is_valid_unit(
			$value,
			$allowed_units,
			$require_unit,
			'all_time'
		);
	}

	/**
	 * Validate a value with unit components against allowed units.
	 *
	 * @param string $value         The value to validate (e.g., "10days", "5px", "100percentage")
	 * @param array  $allowed_units Array of allowed unit values
	 * @param bool   $require_unit  Whether to require a unit (default: false)
	 * @param string $default_unit  Default unit to use if none provided (default: '')
	 *
	 * @return bool True if the value contains valid number and unit, false otherwise
	 */
	public static function is_valid_unit(
		string $value,
		array $allowed_units,
		bool $require_unit = false,
		string $default_unit = ''
	): bool {
		if ( empty( $value ) ) {
			return false;
		}

		// Extract components using the existing utility
		$components = Extract::unit_components( $value );

		// If unit is required and none provided, return false
		if ( $require_unit && empty( $components['unit'] ) ) {
			return false;
		}

		// Use default unit if none specified
		$unit = empty( $components['unit'] ) ? $default_unit : $components['unit'];

		// If no unit is provided and no default, check if that's acceptable
		if ( empty( $unit ) && ! $require_unit ) {
			return true;
		}

		// Validate that the provided unit is valid
		return in_array( $unit, $allowed_units, true );
	}

	/**
	 * Validate if a string matches a given regular expression pattern.
	 *
	 * @param string $string  The string to validate.
	 * @param string $pattern The regular expression pattern.
	 *
	 * @return bool True if the string matches the pattern, false otherwise.
	 */
	public static function matches_pattern( string $string, string $pattern ): bool {
		return (bool) preg_match( $pattern, $string );
	}

}