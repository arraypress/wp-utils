<?php
/**
 * Sanitization Utilities for WordPress
 *
 * This class provides comprehensive utility functions for sanitizing various data types in WordPress.
 * It includes methods for cleaning text, emails, URLs, numbers, and other input types, ensuring safe
 * and valid data handling across a variety of scenarios. Each function is designed to sanitize its
 * input based on context, such as text fields, filenames, slugs, and more.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       0.3.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

/**
 * Check if the class `Sanitize` is defined, and if not, define it.
 */
if ( ! class_exists( 'Sanitize' ) ) :

	/**
	 * Sanitization Utilities
	 *
	 * This class provides utility functions for sanitizing various data types,
	 * including text, numbers, emails, URLs, slugs, files, and more. It ensures
	 * that input is properly sanitized and validated according to context, providing
	 * safe and secure data handling within WordPress.
	 */
	class Sanitize {

		/**
		 * Sanitize value based on type.
		 *
		 * @param mixed  $value The value to sanitize.
		 * @param string $type  The type of sanitization.
		 *
		 * @return mixed Sanitized value.
		 */
		public static function value( $value, string $type ) {
			if ( method_exists( __CLASS__, $type ) ) {
				return self::$type( $value );
			}

			return $type === 'text' ? wp_kses_post( $value ) : self::text( $value );
		}

		/**
		 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
		 *
		 * @param mixed $value Data to sanitize.
		 *
		 * @return array|string Sanitized data.
		 */
		public static function clean( $value ) {
			if ( is_array( $value ) ) {
				return array_map( [ __CLASS__, 'clean' ], $value );
			}

			return sanitize_text_field( $value );
		}

		/**
		 * Sanitize, validate, and deduplicate an array of object IDs.
		 *
		 * @param array $object_ids An array of object IDs.
		 *
		 * @return array An array of unique, sanitized, and positive object IDs.
		 */
		public static function object_ids( array $object_ids ): array {
			$sanitized_ids = array_map( 'absint', $object_ids );
			$filtered_ids  = array_filter( $sanitized_ids );
			$unique_ids    = array_unique( $filtered_ids );

			return array_values( $unique_ids );
		}

		/**
		 * Sanitize a numeric value.
		 *
		 * @param mixed $value The value to sanitize.
		 *
		 * @return float A sanitized numeric value.
		 */
		public static function number( $value ): float {
			return is_numeric( $value ) ? (float) $value : 0.0;
		}

		/**
		 * Sanitize an integer value.
		 *
		 * @param mixed $value The value to sanitize.
		 *
		 * @return int A sanitized integer value.
		 */
		public static function int( $value ): int {
			return (int) self::number( $value );
		}

		/**
		 * Sanitize a float value.
		 *
		 * @param mixed $value The value to sanitize.
		 *
		 * @return float A sanitized float value.
		 */
		public static function float( $value ): float {
			return self::number( $value );
		}

		/**
		 * Sanitize a boolean value.
		 *
		 * @param mixed $value The value to sanitize.
		 *
		 * @return bool A sanitized boolean value.
		 */
		public static function bool( $value ): bool {
			if ( is_string( $value ) ) {
				$value = strtolower( $value );

				return in_array( $value, [ 'true', '1', 'on', 'yes' ] );
			}

			return (bool) $value;
		}

		/**
		 * Sanitize a file extension.
		 *
		 * @param string $extension The file extension to be sanitized.
		 *
		 * @return string The sanitized file extension.
		 */
		public static function file_extension( string $extension ): string {
			$trimmed_extension   = trim( $extension );
			$lowercase_extension = strtolower( $trimmed_extension );

			return sanitize_key( $lowercase_extension );
		}

		/**
		 * Clean a textarea input, maintaining line breaks.
		 *
		 * @param string $var Data to sanitize.
		 *
		 * @return string Sanitized data.
		 */
		public static function textarea( string $var ): string {
			return implode( "\n", array_map( [ __CLASS__, 'text' ], explode( "\n", $var ) ) );
		}

		/**
		 * Clean a string destined to be a tooltip.
		 *
		 * @param string $var Data to sanitize.
		 *
		 * @return string Sanitized tooltip.
		 */
		public static function tooltip( string $var ): string {
			return wp_kses( html_entity_decode( $var ), [
				'br'     => [],
				'em'     => [],
				'strong' => [],
				'small'  => [],
				'span'   => [],
				'ul'     => [],
				'li'     => [],
				'ol'     => [],
				'p'      => [],
			] );
		}

		/**
		 * Sanitize a URL.
		 *
		 * @param string $url The URL to sanitize.
		 *
		 * @return string The sanitized URL.
		 */
		public static function url( string $url ): string {
			return esc_url( $url );
		}

		/**
		 * Sanitize HTML content.
		 *
		 * @param string $html The HTML content to sanitize.
		 *
		 * @return string The sanitized HTML.
		 */
		public static function html( string $html ): string {
			return wp_kses_post( $html );
		}

		/**
		 * Sanitize a file path.
		 *
		 * @param string $path The file path to sanitize.
		 *
		 * @return string The sanitized file path.
		 */
		public static function path( string $path ): string {
			return str_replace( ' ', '-', preg_replace( '/[^A-Za-z0-9_\-\/\. ]/', '', $path ) );
		}

		/**
		 * Sanitize a phone number.
		 *
		 * @param string $phone The phone number to sanitize.
		 *
		 * @return string The sanitized phone number.
		 */
		public static function phone( string $phone ): string {
			return preg_replace( '/[^0-9\+\-\(\) ]/', '', $phone );
		}

		/**
		 * Sanitize an IP address.
		 *
		 * @param string $ip The IP address to sanitize.
		 *
		 * @return string The sanitized IP address, or an empty string if invalid.
		 */
		public static function ip( string $ip ): string {
			// Trim the IP address
			$ip = trim( $ip );

			// Sanitize the IP address
			$sanitized_ip = filter_var( $ip, FILTER_SANITIZE_IP );

			// If sanitization succeeds, return the sanitized IP; otherwise, return an empty string
			return $sanitized_ip !== false ? $sanitized_ip : '';
		}

		/**
		 * Sanitize a JSON string.
		 *
		 * @param string $json The JSON string to sanitize.
		 *
		 * @return string The sanitized JSON string.
		 */
		public static function json( string $json ): string {
			$decoded = json_decode( $json, true );

			return $decoded ? wp_json_encode( self::clean( $decoded ) ) : '';
		}

		/**
		 * Sanitize a query string.
		 *
		 * @param string $query The query string to sanitize.
		 *
		 * @return string The sanitized query string.
		 */
		public static function query_string( string $query ): string {
			parse_str( $query, $params );

			return http_build_query( self::clean( $params ) );
		}

		/**
		 * Sanitize the search string.
		 *
		 * @param string $value The search string to sanitize.
		 *
		 * @return string The sanitized search string.
		 */
		public static function search( string $value ): string {
			return esc_sql( self::text( $value ) );
		}

		/**
		 * Sanitize a text field.
		 *
		 * @param string $text The text to sanitize.
		 *
		 * @return string The sanitized text.
		 */
		public static function text( string $text ): string {
			return sanitize_text_field( $text );
		}

		/**
		 * Sanitize an email address.
		 *
		 * @param string $email The email to sanitize.
		 *
		 * @return string The sanitized email.
		 */
		public static function email( string $email ): string {
			$sanitized = sanitize_email( $email );

			return $sanitized ?: $email;
		}

		/**
		 * Sanitize a filename.
		 *
		 * @param string $filename The filename to sanitize.
		 *
		 * @return string The sanitized filename.
		 */
		public static function filename( string $filename ): string {
			return sanitize_file_name( $filename );
		}

		/**
		 * Sanitize CSS.
		 *
		 * @param string $css The CSS to sanitize.
		 *
		 * @return string The sanitized CSS.
		 */
		public static function css( string $css ): string {
			return wp_strip_all_tags( $css );
		}

		/**
		 * Sanitize a hex color.
		 *
		 * @param string $color The hex color code.
		 *
		 * @return string|null Sanitized hex color code, or null if invalid.
		 */
		public static function hex( string $color ): string {
			if ( str_starts_with( $color, '#' ) ) {
				return sanitize_hex_color( $color );
			} else {
				$sanitized = sanitize_hex_color_no_hash( $color );
				return $sanitized !== null ? '#' . $sanitized : '';
			}
		}

		/**
		 * Sanitize and validate a date.
		 *
		 * @param string $date   The date to sanitize.
		 * @param string $format The format to return the date in (default: 'Y-m-d H:i:s').
		 *
		 * @return string|null The sanitized date or null if invalid.
		 */
		public static function date_time( string $date, string $format = 'Y-m-d H:i:s' ): ?string {
			$sanitized_date = self::text( $date );
			$timestamp      = strtotime( $sanitized_date );

			return $timestamp ? date( $format, $timestamp ) : null;
		}

		/**
		 * Sanitize and validate an option from a predefined set.
		 *
		 * @param string $option          The option to sanitize.
		 * @param array  $allowed_options The allowed options.
		 * @param string $default         The default option if invalid.
		 *
		 * @return string The sanitized and validated option.
		 */
		public static function option( string $option, array $allowed_options, string $default = '' ): string {
			$sanitized_option = strtolower( self::text( $option ) );

			return in_array( $sanitized_option, $allowed_options ) ? $sanitized_option : $default;
		}

		/**
		 * Sanitize a code by removing invalid characters.
		 *
		 * @param string $code          The code to sanitize.
		 * @param string $allowed_chars Regex pattern for allowed characters (default: 'a-zA-Z0-9-_').
		 *
		 * @return string The sanitized code.
		 */
		public static function code( string $code, string $allowed_chars = 'a-zA-Z0-9-_' ): string {
			$sanitized = preg_replace( '/[^' . $allowed_chars . ']+/', '', $code );

			return ( strtoupper( $code ) !== strtoupper( $sanitized ) ) ? $sanitized : $code;
		}

		/**
		 * Sanitize a key.
		 *
		 * @param string $key The key to sanitize.
		 *
		 * @return string The sanitized key.
		 */
		public static function key( string $key ): string {
			return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
		}

		/**
		 * Sanitize a list of items.
		 *
		 * @param string|array  $input      The input to sanitize (string or array).
		 * @param callable|null $validator  Optional custom validator function.
		 * @param string        $delimiter  Delimiter for string input (default: "\n").
		 * @param bool          $trim_items Whether to trim each item (default: true).
		 * @param bool          $unique     Whether to remove duplicate items (default: true).
		 *
		 * @return array Sanitized array of items.
		 */
		public static function list( $input, callable $validator = null, string $delimiter = "\n", bool $trim_items = true, bool $unique = true ): array {
			// Convert string input to array
			$items = is_array( $input ) ? $input : explode( $delimiter, $input );

			// Trim items if required
			if ( $trim_items ) {
				$items = array_map( 'trim', $items );
			}

			// Remove empty items
			$items = array_filter( $items );

			// Remove duplicates if required
			if ( $unique ) {
				$items = array_unique( $items );
			}

			// Apply text sanitization
			$items = array_map( [ __CLASS__, 'text' ], $items );

			// Apply custom validator if provided
			if ( $validator ) {
				$items = array_filter( $items, $validator );
			}

			// Reset array keys
			return array_values( $items );
		}

		/**
		 * Sanitize a list of emails.
		 *
		 * @param string|array $input The input to sanitize (string with line breaks or array).
		 *
		 * @return array Sanitized array of emails.
		 */
		public static function emails( $input ): array {
			return self::list( $input, function ( $email ) {
				return is_email( $email ) || $email[0] === '@';
			} );
		}

		/**
		 * Sanitize a list of IP addresses.
		 *
		 * @param string|array $input The input to sanitize (string with line breaks or array).
		 *
		 * @return array Sanitized array of IP addresses.
		 */
		public static function ips( $input ): array {
			return self::list( $input, function ( $ip ) {
				$sanitized_ip = self::ip( $ip );

				return $sanitized_ip !== '';
			} );
		}

		/**
		 * Sanitize a comma-separated list.
		 *
		 * @param string $input The comma-separated list to sanitize.
		 *
		 * @return array Sanitized array of items.
		 */
		public static function comma_separated_list( string $input ): array {
			return self::list( $input, null, ',' );
		}

		/**
		 * Sanitize a username.
		 *
		 * @param string $username The username to sanitize.
		 *
		 * @return string The sanitized username.
		 */
		public static function username( string $username ): string {
			return str_replace( '@', '', sanitize_user( $username, true ) );
		}

		/**
		 * Sanitize a timezone string.
		 *
		 * @param string $timezone The timezone string to sanitize.
		 *
		 * @return string The sanitized timezone string.
		 */
		public static function timezone( string $timezone ): string {
			return in_array( $timezone, timezone_identifiers_list() ) ? $timezone : 'UTC';
		}

		/**
		 * Sanitize a MIME type.
		 *
		 * @param string $mime_type The MIME type to sanitize.
		 *
		 * @return string The sanitized MIME type.
		 */
		public static function mime_type( string $mime_type ): string {
			return sanitize_mime_type( $mime_type );
		}

		/**
		 * Sanitize a table or column name.
		 *
		 * @param string $name The name to sanitize.
		 *
		 * @return string The sanitized name.
		 */
		public static function sql_name( string $name ): string {
			return preg_replace( '/[^a-zA-Z0-9_]/', '', $name );
		}

		/**
		 * Sanitize block attributes.
		 *
		 * @param array $attributes The block attributes to sanitize.
		 *
		 * @return array The sanitized attributes.
		 */
		public static function block_attributes( array $attributes ): array {
			$sanitized = [];
			foreach ( $attributes as $key => $value ) {
				if ( is_string( $value ) ) {
					$sanitized[ $key ] = sanitize_text_field( $value );
				} elseif ( is_array( $value ) ) {
					$sanitized[ $key ] = self::block_attributes( $value );
				} else {
					$sanitized[ $key ] = $value;
				}
			}

			return $sanitized;
		}

	}
endif;