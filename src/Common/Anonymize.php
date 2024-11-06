<?php
/**
 * Array Helper Utilities
 *
 * This class provides utility functions for working with arrays in PHP. It includes
 * methods for sorting, shuffling, selecting random elements, checking conditions across
 * elements, managing keys, and converting arrays into other formats such as JSON, XML,
 * or delimited strings. Additionally, it offers advanced operations for flattening, filtering,
 * normalizing, and recursive array manipulations.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

class Anonymize {

	/**
	 * Anonymize an email address.
	 *
	 * @param string $email    The email address to anonymize.
	 * @param bool   $validate Optional. Whether to validate the email first. Default true.
	 *
	 * @return string|null The anonymized email or null if validation fails.
	 */
	public static function email( string $email, bool $validate = true ): ?string {
		if ( $validate && ! Email::is_valid( $email ) ) {
			return null;
		}

		return Email::anonymize( $email );
	}

	/**
	 * Anonymize an IP address.
	 *
	 * @param string $ip       The IP address to anonymize.
	 * @param bool   $validate Optional. Whether to validate the IP first. Default true.
	 *
	 * @return string|null The anonymized IP or null if validation fails.
	 */
	public static function ip( string $ip, bool $validate = true ): ?string {
		if ( $validate && ! IP::is_valid( $ip ) ) {
			return null;
		}

		return IP::anonymize( $ip );
	}

	/**
	 * Anonymize a phone number by keeping only the last N digits.
	 *
	 * @param string $phone     The phone number to anonymize.
	 * @param int    $keep_last Number of digits to keep at the end. Default 4.
	 *
	 * @return string The anonymized phone number.
	 */
	public static function phone( string $phone, int $keep_last = 4 ): string {
		// Remove all non-digit characters
		$digits = preg_replace( '/[^0-9]/', '', $phone );
		$length = strlen( $digits );

		if ( $length <= $keep_last ) {
			return str_repeat( '*', $length );
		}

		return str_repeat( '*', $length - $keep_last ) . substr( $digits, - $keep_last );
	}

	/**
	 * Anonymize a name while preserving its structure.
	 *
	 * @param string $name The name to anonymize.
	 *
	 * @return string The anonymized name.
	 */
	public static function name( string $name ): string {
		$words = explode( ' ', $name );

		return implode( ' ', array_map( function ( $word ) {
			$len = mb_strlen( $word );
			if ( $len <= 2 ) {
				return str_repeat( '*', $len );
			}

			return mb_substr( $word, 0, 1 ) . str_repeat( '*', $len - 2 ) . mb_substr( $word, - 1 );
		}, $words ) );
	}

	/**
	 * Anonymize a credit card number.
	 *
	 * @param string $card_number The credit card number to anonymize.
	 * @param int    $keep_last   Number of digits to keep at the end. Default 4.
	 *
	 * @return string The anonymized credit card number.
	 */
	public static function credit_card( string $card_number, int $keep_last = 4 ): string {
		$digits = preg_replace( '/[^0-9]/', '', $card_number );

		return str_repeat( '*', strlen( $digits ) - $keep_last ) . substr( $digits, - $keep_last );
	}

	/**
	 * Anonymize an address while preserving its structure.
	 *
	 * @param string $address    The address to anonymize.
	 * @param array  $keep_parts Optional. Array of address parts to keep (e.g., ['city', 'country']).
	 *
	 * @return string The anonymized address.
	 */
	public static function address( string $address, array $keep_parts = [] ): string {
		// Split address into lines
		$lines = explode( "\n", $address );

		return implode( "\n", array_map( function ( $line ) use ( $keep_parts ) {
			// Check if line contains any parts to keep
			foreach ( $keep_parts as $part ) {
				if ( stripos( $line, $part ) !== false ) {
					return $line;
				}
			}

			// Anonymize numbers but keep their length
			$line = preg_replace_callback( '/\d+/', function ( $matches ) {
				return str_repeat( '*', strlen( $matches[0] ) );
			}, $line );

			// Replace letters with asterisks but keep spaces and punctuation
			return preg_replace( '/[a-zA-Z]/', '*', $line );
		}, $lines ) );
	}

	/**
	 * Anonymize a date while preserving the month and year.
	 *
	 * @param string $date The date to anonymize (any standard format).
	 *
	 * @return string|null The anonymized date (YYYY-MM-**) or null on failure.
	 */
	public static function date( string $date ): ?string {
		$timestamp = strtotime( $date );
		if ( $timestamp === false ) {
			return null;
		}

		return date( 'Y-m-**', $timestamp );
	}

	/**
	 * Anonymize arbitrary text by replacing characters with asterisks while preserving length and structure.
	 *
	 * @param string $text           The text to anonymize.
	 * @param array  $preserve_chars Optional. Characters to preserve. Default [' ', '.', '@', '-'].
	 *
	 * @return string The anonymized text.
	 */
	public static function text( string $text, array $preserve_chars = [ ' ', '.', '@', '-' ] ): string {
		return preg_replace_callback( '/[^' . preg_quote( implode( '', $preserve_chars ), '/' ) . ']/', function ( $matches ) {
			return '*';
		}, $text );
	}

	/**
	 * Anonymize a URL while preserving the domain structure.
	 *
	 * @param string $url      The URL to anonymize.
	 * @param bool   $validate Optional. Whether to validate the URL first. Default true.
	 *
	 * @return string|null The anonymized URL or null if validation fails.
	 */
	public static function url( string $url, bool $validate = true ): ?string {
		if ( $validate && ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return null;
		}

		$parts = parse_url( $url );
		if ( $parts === false ) {
			return null;
		}

		// Anonymize path segments but keep structure
		if ( isset( $parts['path'] ) ) {
			$path_segments       = explode( '/', trim( $parts['path'], '/' ) );
			$anonymized_segments = array_map( function ( $segment ) {
				return preg_replace( '/[a-zA-Z0-9]/', '*', $segment );
			}, $path_segments );
			$parts['path']       = '/' . implode( '/', $anonymized_segments );
		}

		// Anonymize query parameters but keep keys
		if ( isset( $parts['query'] ) ) {
			parse_str( $parts['query'], $query_params );
			array_walk( $query_params, function ( &$value ) {
				$value = str_repeat( '*', strlen( $value ) );
			} );
			$parts['query'] = http_build_query( $query_params );
		}

		// Rebuild URL
		return
			( isset( $parts['scheme'] ) ? "{$parts['scheme']}://" : '' ) .
			( isset( $parts['host'] ) ? "{$parts['host']}" : '' ) .
			( isset( $parts['port'] ) ? ":{$parts['port']}" : '' ) .
			( isset( $parts['path'] ) ? "{$parts['path']}" : '' ) .
			( isset( $parts['query'] ) ? "?{$parts['query']}" : '' );
	}

	/**
	 * Anonymize a zip or postal code.
	 *
	 * @param string $zipcode   The zip or postal code to anonymize.
	 * @param int    $keep_last Number of characters to keep at the end. Default 3.
	 *
	 * @return string The anonymized zip code.
	 */
	public static function zipcode( string $zipcode, int $keep_last = 3 ): string {
		$zipcode = preg_replace( '/[^a-zA-Z0-9]/', '', $zipcode );

		$length = strlen( $zipcode );

		if ( $length <= $keep_last ) {
			return str_repeat( '*', $length );
		}

		// Mask the beginning characters, preserving the last few
		return str_repeat( '*', $length - $keep_last ) . substr( $zipcode, - $keep_last );
	}

	/**
	 * Anonymize a Social Security Number (SSN).
	 *
	 * @param string $ssn       The SSN to anonymize.
	 * @param int    $keep_last Number of digits to keep at the end. Default 4.
	 *
	 * @return string The anonymized SSN.
	 */
	public static function ssn( string $ssn, int $keep_last = 4 ): string {
		$digits = preg_replace( '/[^0-9]/', '', $ssn );

		$length = strlen( $digits );

		if ( $length <= $keep_last ) {
			return str_repeat( '*', $length );
		}

		return str_repeat( '*', $length - $keep_last ) . substr( $digits, - $keep_last );
	}

	/**
	 * Anonymize a bank account number.
	 *
	 * @param string $account_number The bank account number to anonymize.
	 * @param int    $keep_last      Number of digits to keep at the end. Default 4.
	 *
	 * @return string The anonymized bank account number.
	 */
	public static function bank_account( string $account_number, int $keep_last = 4 ): string {
		$digits = preg_replace( '/[^0-9]/', '', $account_number );

		return str_repeat( '*', strlen( $digits ) - $keep_last ) . substr( $digits, - $keep_last );
	}

	/**
	 * Anonymize a license plate number.
	 *
	 * @param string $license_plate The license plate to anonymize.
	 * @param int    $keep_last     Number of characters to keep at the end. Default 3.
	 *
	 * @return string The anonymized license plate.
	 */
	public static function license_plate( string $license_plate, int $keep_last = 3 ): string {
		$license_plate = preg_replace( '/[^a-zA-Z0-9]/', '', $license_plate );

		$length = strlen( $license_plate );

		if ( $length <= $keep_last ) {
			return str_repeat( '*', $length );
		}

		return str_repeat( '*', $length - $keep_last ) . substr( $license_plate, - $keep_last );
	}

}