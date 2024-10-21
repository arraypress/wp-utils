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
		 * @param string $input The textarea content to sanitize.
		 *
		 * @return string Sanitized textarea content with preserved line breaks.
		 */
		public static function textarea( string $input ): string {
			// Split the input into an array of lines
			$lines = explode( "\n", $input );

			// Sanitize each line individually
			$sanitized_lines = array_map( function ( $line ) {
				return self::text( $line );
			}, $lines );

			// Rejoin the sanitized lines, preserving line breaks
			return implode( "\n", $sanitized_lines );
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
		 * Sanitize a file path using PHP's built-in functions.
		 *
		 * @param string $path      The file path to sanitize.
		 * @param bool   $allow_rel Whether to allow relative paths. Default is false.
		 *
		 * @return string The sanitized file path.
		 */
		public static function path( string $path, bool $allow_rel = false ): string {
			// Normalize directory separators and remove any null bytes
			$path = str_replace( [ '\\', "\0" ], '/', $path );

			// Use realpath to resolve the absolute path, removing .. and . components
			$realpath = realpath( $path );

			if ( $realpath !== false ) {
				// If realpath succeeds, use the resolved path
				$sanitized = $realpath;
			} else {
				// If realpath fails (e.g., for non-existent paths), manually remove .. and . components
				$parts      = explode( '/', $path );
				$safe_parts = [];
				foreach ( $parts as $part ) {
					if ( $part == '..' && ! empty( $safe_parts ) && end( $safe_parts ) != '..' ) {
						array_pop( $safe_parts );
					} elseif ( $part != '.' && $part != '' ) {
						$safe_parts[] = $part;
					}
				}
				$sanitized = implode( '/', $safe_parts );
			}

			// Convert back to relative path if allowed and originally relative
			if ( $allow_rel && ! path_is_absolute( $path ) ) {
				$sanitized = ltrim( str_replace( getcwd(), '', $sanitized ), '/' );
			}

			return $sanitized;
		}

		/**
		 * Check if a path is absolute.
		 *
		 * @param string $path The path to check.
		 *
		 * @return bool True if the path is absolute, false otherwise.
		 */
		private static function path_is_absolute( string $path ): bool {
			if ( PHP_OS_FAMILY === 'Windows' ) {
				return (bool) preg_match( '/^[A-Z]:\//i', $path );
			}

			return $path[0] === '/';
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
		 * Sanitize and validate an IP address.
		 *
		 * @param string $ip The IP address to sanitize and validate.
		 *
		 * @return string The validated IP address, or an empty string if invalid.
		 */
		public static function ip( string $ip ): string {
			// Trim the IP address
			$ip = trim( $ip );

			// Validate the IP address (both IPv4 and IPv6)
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) ) {
				return $ip;
			}

			// If validation fails, return an empty string
			return '';
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

		/**
		 * Sanitize a discount type.
		 *
		 * @param string $type The discount type to sanitize.
		 *
		 * @return string The sanitized discount type ('percentage' or 'flat').
		 */
		public static function discount_type( string $type ): string {
			$sanitized = strtolower( self::text( $type ) );

			return in_array( $sanitized, [ 'percentage', 'flat' ] ) ? $sanitized : 'percentage';
		}

		/**
		 * Sanitize a percentage value.
		 *
		 * @param mixed $value The percentage value to sanitize.
		 *
		 * @return float The sanitized percentage value (0-100).
		 */
		public static function percentage( $value ): float {
			$sanitized = self::float( $value );

			return max( 0, min( 100, $sanitized ) );
		}

		/**
		 * Sanitize a status value.
		 *
		 * @param string $status         The status to sanitize.
		 * @param array  $valid_statuses Optional array of valid statuses. Defaults to ['active', 'inactive'].
		 * @param string $default        Optional default status if the input is invalid. Defaults to 'active'.
		 *
		 * @return string The sanitized status.
		 */
		public static function status(
			string $status, array $valid_statuses = [
			'active',
			'inactive'
		], string $default = 'active'
		): string {
			$sanitized = strtolower( self::text( $status ) );

			// If the sanitized status is in the list of valid statuses, return it
			if ( in_array( $sanitized, $valid_statuses, true ) ) {
				return $sanitized;
			}

			// If the default status is in the list of valid statuses, return it
			if ( in_array( $default, $valid_statuses, true ) ) {
				return $default;
			}

			// If neither the input nor the default is valid, return the first valid status
			return reset( $valid_statuses ) ?: 'active';
		}

		/**
		 * Sanitize a slug.
		 *
		 * @param string $slug The slug to sanitize.
		 *
		 * @return string The sanitized slug.
		 */
		public static function slug( string $slug ): string {
			return sanitize_title( $slug );
		}

		/**
		 * Sanitize a currency code.
		 *
		 * @param string $code        The currency code to sanitize.
		 * @param array  $valid_codes Optional array of valid currency codes. If not provided, uses all supported currencies from the Currency class.
		 * @param string $default     Optional default currency code if the input is invalid. Defaults to Currency::USD.
		 *
		 * @return string The sanitized currency code.
		 */
		public static function currency_code( string $code, array $valid_codes = [], string $default = Currency::USD ): string {
			$sanitized = strtoupper( self::text( $code ) );

			// If no valid codes are provided, use all supported currencies from the Currency class
			if ( empty( $valid_codes ) ) {
				$valid_codes = Currency::get_supported_currencies();
			}

			// If the sanitized code is in the list of valid codes, return it
			if ( in_array( $sanitized, $valid_codes, true ) ) {
				return $sanitized;
			}

			// If the default code is in the list of valid codes, return it
			if ( in_array( $default, $valid_codes, true ) ) {
				return $default;
			}

			// If neither the input nor the default is valid, return the first valid code
			return reset( $valid_codes ) ?: Currency::USD;
		}

		/**
		 * Sanitize a range value.
		 *
		 * @param mixed $value The value to sanitize.
		 * @param float $min   The minimum allowed value.
		 * @param float $max   The maximum allowed value.
		 *
		 * @return float The sanitized value within the specified range.
		 */
		public static function range( $value, float $min, float $max ): float {
			$sanitized = self::float( $value );

			return max( $min, min( $max, $sanitized ) );
		}

		/**
		 * Sanitize one or more CSS classes.
		 *
		 * @param string|array $classes One or more CSS classes to sanitize.
		 *
		 * @return string Sanitized CSS classes as a space-separated string.
		 */
		public static function html_class( $classes ): string {
			$classes = is_array( $classes ) ? $classes : [ $classes ];
			$classes = array_map( 'sanitize_html_class', $classes );
			$classes = array_filter( $classes );
			$classes = array_unique( $classes );

			return implode( ' ', $classes );
		}

		/**
		 * Sanitize file downloads.
		 *
		 * This method ensures files are correctly mapped to an array starting with an index of 0,
		 * removes blank rows, and sanitizes file names and paths.
		 *
		 * @param array $files Array of all the file downloads.
		 *
		 * @return array Sanitized array of file downloads.
		 */
		public static function files( array $files ): array {
			$sanitized_files = [];

			foreach ( $files as $file ) {
				// Skip empty rows
				if ( empty( $file['name'] ) && empty( $file['file'] ) ) {
					continue;
				}

				$sanitized_file = [
					'name' => isset( $file['name'] ) ? self::text( $file['name'] ) : '',
					'file' => isset( $file['file'] ) ? trim( $file['file'] ) : '',
				];

				// Only add non-empty files
				if ( ! empty( $sanitized_file['name'] ) || ! empty( $sanitized_file['file'] ) ) {
					$sanitized_files[] = $sanitized_file;
				}
			}

			return $sanitized_files;
		}

		/**
		 * Cast and sanitize a value based on the database column field type.
		 *
		 * @param mixed  $value   The value to be casted and sanitized.
		 * @param string $type    The database column field type.
		 * @param array  $options Additional options for casting and sanitization.
		 *
		 * @return mixed The casted and sanitized value.
		 */
		public static function db_cast( $value, string $type, array $options = [] ) {
			$type       = strtolower( $type );
			$allow_null = $options['allow_null'] ?? false;

			if ( $allow_null && is_null( $value ) ) {
				return null;
			}

			switch ( $type ) {
				case 'tinyint':
					return self::int_range( $value, $options['min'] ?? - 128, $options['max'] ?? 127 );
				case 'smallint':
					return self::int_range( $value, $options['min'] ?? - 32768, $options['max'] ?? 32767 );
				case 'mediumint':
					return self::int_range( $value, $options['min'] ?? - 8388608, $options['max'] ?? 8388607 );
				case 'int':
					return self::int_range( $value, $options['min'] ?? PHP_INT_MIN, $options['max'] ?? PHP_INT_MAX );
				case 'bigint':
					// For bigint, we'll use string representation to avoid integer overflow
					return self::string_length( (string) $value, $options['max_length'] ?? 20 );
				case 'float':
				case 'double':
				case 'decimal':
					return self::float_precision( $value, $options['precision'] ?? 10, $options['scale'] ?? 2 );
				case 'char':
				case 'varchar':
					return self::string_length( $value, $options['max_length'] ?? 255 );
				case 'text':
					return self::string_length( $value, $options['max_length'] ?? 65535 );
				case 'mediumtext':
					return self::string_length( $value, $options['max_length'] ?? 16777215 );
				case 'longtext':
					return self::string_length( $value, $options['max_length'] ?? 4294967295 );
				case 'date':
					return self::date_time( $value, $options['format'] ?? 'Y-m-d' );
				case 'datetime':
				case 'timestamp':
					return self::date_time( $value, $options['format'] ?? 'Y-m-d H:i:s' );
				case 'time':
					return self::time( $value );
				case 'year':
					return self::year( $value );
				case 'enum':
				case 'set':
					return self::enum( $value, $options['allowed_values'] ?? [] );
				case 'binary':
				case 'varbinary':
					return self::binary( $value, $options['max_length'] ?? 255 );
				case 'blob':
					return self::binary( $value, $options['max_length'] ?? 65535 );
				case 'mediumblob':
					return self::binary( $value, $options['max_length'] ?? 16777215 );
				case 'longblob':
					return self::binary( $value, $options['max_length'] ?? 4294967295 );
				case 'bit':
					return self::bit( $value );
				case 'bool':
				case 'boolean':
					return self::bool( $value );
				default:
					return self::clean( $value );
			}
		}

		/**
		 * Sanitize an integer value within a specific range.
		 *
		 * @param mixed $value The value to sanitize.
		 * @param int   $min   The minimum allowed value.
		 * @param int   $max   The maximum allowed value.
		 *
		 * @return int The sanitized integer value.
		 */
		public static function int_range( $value, int $min, int $max ): int {
			return max( $min, min( $max, self::int( $value ) ) );
		}

		/**
		 * Sanitize a float value with precision and scale.
		 *
		 * @param mixed $value     The value to sanitize.
		 * @param int   $precision The total number of digits.
		 * @param int   $scale     The number of digits after the decimal point.
		 *
		 * @return float The sanitized float value.
		 */
		public static function float_precision( $value, int $precision, int $scale ): float {
			$float_value = self::float( $value );
			$factor      = pow( 10, $precision - $scale );

			return round( $float_value * $factor ) / $factor;
		}

		/**
		 * Sanitize a string value with an optional maximum length.
		 *
		 * @param mixed    $value      The value to sanitize.
		 * @param int|null $max_length The maximum allowed length of the string.
		 *
		 * @return string The sanitized string value.
		 */
		public static function string_length( $value, ?int $max_length ): string {
			$sanitized = self::text( $value );
			if ( $max_length !== null ) {
				$sanitized = mb_substr( $sanitized, 0, $max_length );
			}

			return $sanitized;
		}

		/**
		 * Sanitize a time value.
		 *
		 * @param mixed $value The value to sanitize.
		 *
		 * @return string|null The sanitized time value or null if invalid.
		 */
		public static function time( $value ): ?string {
			if ( preg_match( '/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $value ) ) {
				return $value;
			}

			return null;
		}

		/**
		 * Sanitize a year value.
		 *
		 * @param mixed $value The value to sanitize.
		 *
		 * @return int|null The sanitized year value or null if invalid.
		 */
		public static function year( $value ): ?int {
			$year = self::int( $value );

			return ( $year >= 1901 && $year <= 2155 ) ? $year : null;
		}

		/**
		 * Sanitize an enum value.
		 *
		 * @param mixed $value          The value to sanitize.
		 * @param array $allowed_values The list of allowed values.
		 *
		 * @return string|null The sanitized enum value or null if invalid.
		 */
		public static function enum( $value, array $allowed_values ): ?string {
			$sanitized = self::text( $value );

			return in_array( $sanitized, $allowed_values, true ) ? $sanitized : null;
		}

		/**
		 * Sanitize a binary value.
		 *
		 * @param mixed    $value      The value to sanitize.
		 * @param int|null $max_length The maximum allowed length of the binary data.
		 *
		 * @return string The sanitized binary value.
		 */
		public static function binary( $value, ?int $max_length ): string {
			if ( $max_length !== null ) {
				return substr( $value, 0, $max_length );
			}

			return $value;
		}

		/**
		 * Sanitize a bit value.
		 *
		 * @param mixed $value The value to sanitize.
		 *
		 * @return string The sanitized bit value.
		 */
		public static function bit( $value ): string {
			return $value ? '1' : '0';
		}

		/**
		 * Sanitize a string by removing non-alphanumeric characters and converting to lowercase.
		 *
		 * @param string $string The string to sanitize.
		 *
		 * @return string The sanitized string.
		 */
		public static function alphanumeric_lowercase( string $string ): string {
			// Remove non-alphanumeric characters
			$string = preg_replace( '/[^a-zA-Z0-9]+/', '', $string );

			// Convert to lowercase
			return strtolower( $string );
		}

	}
endif;