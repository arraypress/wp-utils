<?php
/**
 * Split Utility Class for WordPress
 *
 * Provides a comprehensive set of string splitting utilities for WordPress applications.
 * Includes methods for handling names, meta data, units, paths, URLs, and other common
 * string splitting operations.
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
 * Check if the class `Split` is defined, and if not, define it.
 */
if ( ! class_exists( 'Split' ) ) :

	/**
	 * Split Utility Class
	 */
	class Split {

		/**
		 * Array of common name prefixes.
		 *
		 * @var array
		 */
		private static array $prefixes = [ 'mr', 'mrs', 'ms', 'miss', 'dr', 'prof', 'rev' ];

		/**
		 * Array of common name suffixes.
		 *
		 * @var array
		 */
		private static array $suffixes = [ 'jr', 'sr', 'i', 'ii', 'iii', 'iv' ];

		/** Path and URL Handling ************************************************/

		/**
		 * Split a file path into its components.
		 *
		 * @param string $path The file path to split.
		 *
		 * @return array{dirname: string, basename: string, extension: string, filename: string}
		 */
		public static function path( string $path ): array {
			$path_info = pathinfo( $path );

			return [
				'dirname'   => $path_info['dirname'] ?? '',
				'basename'  => $path_info['basename'] ?? '',
				'extension' => $path_info['extension'] ?? '',
				'filename'  => $path_info['filename'] ?? '',
			];
		}

		/**
		 * Split a URL into its components.
		 *
		 * @param string $url The URL to split.
		 *
		 * @return array{scheme: string, host: string, path: string, query: string, fragment: string}
		 */
		public static function url( string $url ): array {
			$components = parse_url( $url );

			return [
				'scheme'   => $components['scheme'] ?? '',
				'host'     => $components['host'] ?? '',
				'path'     => $components['path'] ?? '',
				'query'    => $components['query'] ?? '',
				'fragment' => $components['fragment'] ?? '',
			];
		}

		/** String Splitting ****************************************************/

		/**
		 * Split a camelCase or PascalCase string into words.
		 *
		 * @param string $string    The string to split.
		 * @param string $separator The separator to use between words (default: space).
		 *
		 * @return string The split string.
		 */
		public static function camel_case( string $string, string $separator = ' ' ): string {
			return trim( preg_replace( '/(?<!^)[A-Z]/', $separator . '$0', $string ) );
		}

		/**
		 * Split a string into chunks of specified length.
		 *
		 * @param string $string The string to split.
		 * @param int    $length The length of each chunk.
		 *
		 * @return array Array of string chunks.
		 */
		public static function chunks( string $string, int $length ): array {
			return str_split( $string, $length );
		}

		/** CSV and List Handling **********************************************/

		/**
		 * Split a CSV string into an array, handling quoted values.
		 *
		 * @param string $string    The CSV string to split.
		 * @param string $delimiter The delimiter (default: comma).
		 *
		 * @return array The split CSV data.
		 */
		public static function csv( string $string, string $delimiter = ',' ): array {
			$results = [];
			$handle  = fopen( "php://temp", "r+" );
			fwrite( $handle, $string );
			rewind( $handle );

			while ( ( $data = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
				$results[] = $data;
			}

			fclose( $handle );

			return $results;
		}

		/**
		 * Split a comma-separated list into an array, with trimming.
		 *
		 * @param string $string    The string to split.
		 * @param string $delimiter The delimiter (default: comma).
		 *
		 * @return array The split and trimmed array.
		 */
		public static function list( string $string, string $delimiter = ',' ): array {
			return array_map( 'trim', explode( $delimiter, $string ) );
		}

		/** Key-Value Handling *****************************************************/

		/**
		 * Split meta key and expected value from the input string.
		 *
		 * @param string $string The input string in the format meta_key:value.
		 *
		 * @return array|null An array with meta_key and expected_value or null if the format is invalid.
		 */
		public static function meta_key_value( string $string ): ?array {
			$parts = explode( ':', $string, 2 );
			if ( count( $parts ) !== 2 ) {
				return null;
			}

			$meta_key   = trim( $parts[0] );
			$meta_value = strtolower( trim( $parts[1] ) );

			return [
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value,
			];
		}

		/**
		 * Split the number and period from the input string.
		 *
		 * @param string $string The input string in the format '7day', '8minutes', etc.
		 *
		 * @return array An array with 'number' and 'period' keys.
		 */
		public static function unit_value( string $string ): array {
			$matches = [];
			preg_match( '/^(\d+)(\D+)$/', $string, $matches );

			if ( count( $matches ) !== 3 ) {
				return [
					'number' => 0,
					'period' => '',
				];
			}

			return [
				'number' => (int) $matches[1],
				'period' => strtolower( trim( $matches[2] ) ),
			];
		}

		/** Name Handling **********************************************************/

		/**
		 * Split a full name into its components.
		 *
		 * @param string $string The full name to split.
		 *
		 * @return array An array containing 'prefix', 'first_name', 'middle_name', 'last_name', and 'suffix'.
		 */
		public static function full_name( string $string ): array {
			$name_parts = preg_split( '/\s+/', trim( $string ) );
			$result     = [
				'prefix'      => '',
				'first_name'  => '',
				'middle_name' => '',
				'last_name'   => '',
				'suffix'      => ''
			];

			// Check for prefix
			$first_part = strtolower( str_replace( '.', '', $name_parts[0] ?? '' ) );
			if ( in_array( $first_part, self::$prefixes, true ) ) {
				$result['prefix'] = array_shift( $name_parts );
			}

			// Check for suffix
			$last_part = strtolower( str_replace( '.', '', end( $name_parts ) ?: '' ) );
			if ( in_array( $last_part, self::$suffixes, true ) ) {
				$result['suffix'] = array_pop( $name_parts );
			}

			// Assign remaining parts
			$count = count( $name_parts );
			if ( $count > 0 ) {
				$result['first_name'] = $name_parts[0];
			}
			if ( $count > 2 ) {
				$result['last_name']   = array_pop( $name_parts );
				$result['middle_name'] = implode( ' ', array_slice( $name_parts, 1 ) );
			} elseif ( $count == 2 ) {
				$result['last_name'] = $name_parts[1];
			}

			return $result;
		}

		/**
		 * Add a custom prefix to the list of recognized prefixes.
		 *
		 * @param string $prefix The prefix to add.
		 */
		public static function add_prefix( string $prefix ): void {
			$prefix = strtolower( trim( $prefix ) );
			if ( ! in_array( $prefix, self::$prefixes, true ) ) {
				self::$prefixes[] = $prefix;
			}
		}

		/**
		 * Add a custom suffix to the list of recognized suffixes.
		 *
		 * @param string $suffix The suffix to add.
		 */
		public static function add_suffix( string $suffix ): void {
			$suffix = strtolower( trim( $suffix ) );
			if ( ! in_array( $suffix, self::$suffixes, true ) ) {
				self::$suffixes[] = $suffix;
			}
		}

	}

endif;