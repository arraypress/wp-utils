<?php
/**
 * String Helper Utilities
 *
 * This class provides a wide range of utility functions for string manipulation and validation.
 * It includes methods for checking substrings, converting cases, generating random strings,
 * handling names, and performing general string operations like trimming, padding, and truncating.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Str` is defined, and if not, define it.
 */
if ( ! class_exists( 'Str' ) ) :

	/**
	 * String Helper Utilities
	 *
	 * Provides a set of utility functions for working with strings, including validation,
	 * manipulation, and conversion. It offers flexible operations such as checking for
	 * substrings, string transformations, and random string generation.
	 */
	class Str {

		/**
		 * Common irregular plural forms.
		 */
		private const IRREGULAR_PLURALS = [
			'children'  => 'child',
			'men'       => 'man',
			'women'     => 'woman',
			'people'    => 'person',
			'teeth'     => 'tooth',
			'feet'      => 'foot',
			'mice'      => 'mouse',
			'geese'     => 'goose',
			'oxen'      => 'ox',
			'sheep'     => 'sheep',
			'deer'      => 'deer',
			'fish'      => 'fish',
			'species'   => 'species',
			'series'    => 'series',
			'vertices'  => 'vertex',
			'indices'   => 'index',
			'matrices'  => 'matrix',
			'phenomena' => 'phenomenon',
			'criteria'  => 'criterion',
			'data'      => 'datum',
			'analyses'  => 'analysis',
			'theses'    => 'thesis',
			'foci'      => 'focus',
			'cacti'     => 'cactus',
			'fungi'     => 'fungus',
			'nuclei'    => 'nucleus',
			'syllabi'   => 'syllabus',
			'alumni'    => 'alumnus',
			'radii'     => 'radius'
		];

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

		/** String Checking ********************************************************/

		/**
		 * Checks if any of the given needles are in the haystack.
		 *
		 * @param string $haystack   The string to search.
		 * @param mixed  ...$needles The strings to search for.
		 *
		 * @return bool
		 */
		public static function contains_any( string $haystack, ...$needles ): bool {
			foreach ( $needles as $needle ) {
				if ( str_contains( $haystack, $needle ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Checks if all given needles are in the haystack.
		 *
		 * @param string $haystack   The string to search.
		 * @param mixed  ...$needles The strings to search for.
		 *
		 * @return bool
		 */
		public static function contains_all( string $haystack, ...$needles ): bool {
			foreach ( $needles as $needle ) {
				if ( ! str_contains( $haystack, $needle ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Check if a string matches any pattern in an array of patterns.
		 *
		 * @param string $needle   The string to check.
		 * @param array  $haystack Array of patterns to match against.
		 * @param bool   $partial  Whether to allow partial matches.
		 * @param string $wildcard Wildcard character for partial matches.
		 *
		 * @return bool True if a match is found, false otherwise.
		 */
		public static function matches_any( string $needle, array $haystack, bool $partial = false, string $wildcard = '**' ): bool {
			if ( empty( $needle ) || empty( $haystack ) ) {
				return false;
			}

			$needle = strtolower( trim( $needle ) );

			foreach ( $haystack as $pattern ) {
				$pattern          = strtolower( trim( $pattern ) );
				$is_partial_match = $partial && substr( $pattern, - strlen( $wildcard ) ) === $wildcard;

				if ( $is_partial_match ) {
					$pattern = rtrim( $pattern, $wildcard );
					if ( strpos( $needle, $pattern ) !== false ) {
						return true;
					}
				} elseif ( $needle === $pattern ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if a string starts with a given substring or any of the substrings in an array.
		 *
		 * @param string       $haystack The string to search in.
		 * @param string|array $needle   The substring or array of substrings to search for.
		 *
		 * @return bool
		 */
		public static function starts_with( string $haystack, $needle ): bool {
			if ( is_string( $needle ) ) {
				return str_starts_with( $haystack, $needle );
			}

			if ( is_array( $needle ) ) {
				foreach ( $needle as $prefix ) {
					if ( str_starts_with( $haystack, $prefix ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Check if a string ends with a given substring or any of the substrings in an array.
		 *
		 * @param string       $haystack The string to search in.
		 * @param string|array $needle   The substring or array of substrings to search for.
		 *
		 * @return bool
		 */
		public static function ends_with( string $haystack, $needle ): bool {
			if ( is_string( $needle ) ) {
				return str_ends_with( $haystack, $needle );
			}

			if ( is_array( $needle ) ) {
				foreach ( $needle as $suffix ) {
					if ( str_ends_with( $haystack, $suffix ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/** String Manipulation ****************************************************/

		/**
		 * Reduces multiple whitespace characters to a single space.
		 *
		 * @param string $string The input string.
		 *
		 * @return string
		 */
		public static function reduce_whitespace( string $string ): string {
			return preg_replace( '/\s+/', ' ', $string );
		}

		/**
		 * Removes line breaks and invisible characters from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return string
		 */
		public static function remove_line_breaks( string $string ): string {
			$string = preg_replace( '/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string );

			return str_replace( [ "\r", "\n", PHP_EOL ], '', $string );
		}

		/**
		 * Returns parts of a string between two strings.
		 *
		 * @param string $start  Start string.
		 * @param string $end    End string.
		 * @param string $string String content.
		 * @param bool   $omit   Omit start and end strings.
		 * @param bool   $all    Return all occurrences.
		 *
		 * @return string|array
		 */
		public static function between( string $start, string $end, string $string, bool $omit = false, bool $all = false ) {
			$pattern = '/' . preg_quote( $start, '/' ) . '(.*?)' . preg_quote( $end, '/' ) . '/s';
			preg_match_all( $pattern, $string, $matches );

			$selected_matches = $omit ? $matches[1] : $matches[0];
			$first_match      = isset( $selected_matches[0] ) ? trim( $selected_matches[0] ) : '';

			return $all ? array_map( 'trim', $selected_matches ) : $first_match;
		}

		/**
		 * Removes non-alphanumeric characters from string.
		 *
		 * @param string $string String to sanitize.
		 *
		 * @return string
		 */
		public static function remove_non_alphanumeric( string $string ): string {
			return preg_replace( '/[^A-Za-z0-9\-]/', '', $string );
		}

		/**
		 * Replace first occurrence of a string.
		 *
		 * @param string $needle      The string to search for.
		 * @param string $replacement The string to replace with.
		 * @param string $haystack    The string to search.
		 *
		 * @return string
		 */
		public static function replace_first( string $needle, string $replacement, string $haystack ): string {
			if ( ! $needle || ! $haystack ) {
				return $haystack;
			}

			$position = strpos( $haystack, $needle );

			if ( $position !== false ) {
				return substr_replace( $haystack, $replacement, $position, strlen( $needle ) );
			}

			return $haystack;
		}

		/**
		 * Trim all elements in an array recursively.
		 *
		 * @param array $array The array to trim.
		 *
		 * @return array The trimmed array.
		 */
		public static function trim_all( array $array ): array {
			return array_map( function ( $item ) {
				if ( is_string( $item ) ) {
					return trim( $item );
				}
				if ( is_array( $item ) ) {
					return self::trim_all( $item );
				}

				return $item;
			}, $array );
		}

		/**
		 * Truncate a string to a specified length.
		 *
		 * @param string $string The string to truncate.
		 * @param int    $length The maximum length.
		 * @param string $append The string to append if truncated. Default '...'.
		 *
		 * @return string Truncated string.
		 */
		public static function truncate( string $string, int $length, string $append = '...' ): string {
			if ( mb_strlen( $string ) > $length ) {
				$string = mb_substr( $string, 0, $length - mb_strlen( $append ) ) . $append;
			}

			return $string;
		}

		/**
		 * Pad a string to a certain length with another string.
		 *
		 * @param string $string     The input string.
		 * @param int    $length     The length to pad to.
		 * @param string $pad_string The string to pad with.
		 * @param int    $pad_type   Optional. Can be STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH.
		 *
		 * @return string The padded string.
		 */
		public static function pad( string $string, int $length, string $pad_string = ' ', int $pad_type = STR_PAD_RIGHT ): string {
			return str_pad( $string, $length, $pad_string, $pad_type );
		}

		/** String Conversion ******************************************************/

		/**
		 * Convert a string to title case.
		 *
		 * @param string $string The string to convert.
		 *
		 * @return string The string in title case.
		 */
		public static function to_title_case( string $string ): string {
			return ucwords( strtolower( $string ) );
		}

		/**
		 * Convert a string to camel case.
		 *
		 * @param string $string The string to convert.
		 *
		 * @return string The string in camelCase.
		 */
		public static function to_camel_case( string $string ): string {
			$string = str_replace( [ '-', '_' ], ' ', $string );
			$string = ucwords( $string );
			$string = str_replace( ' ', '', $string );

			return lcfirst( $string );
		}

		/**
		 * Convert a string to snake case.
		 *
		 * @param string $string The string to convert.
		 *
		 * @return string The string in snake_case.
		 */
		public static function to_snake_case( string $string ): string {
			return sanitize_key( str_replace( ' ', '_', $string ) );
		}

		/**
		 * Convert a string to kebab case.
		 *
		 * @param string $string The string to convert.
		 *
		 * @return string The string in kebab-case.
		 */
		public static function to_kebab_case( string $string ): string {
			return sanitize_title( $string );
		}

		/**
		 * Convert a value to a slug.
		 *
		 * @param mixed  $value     The value to convert.
		 * @param string $separator The separator to use (default: '-').
		 *
		 * @return string The slugified string.
		 */
		public static function to_slug( $value, string $separator = '-' ): string {
			return sanitize_title( self::to_string( $value ), $separator );
		}

		/**
		 * Convert a string to an acronym.
		 *
		 * @param string $string The string to convert.
		 *
		 * @return string The acronym.
		 */
		public static function to_acronym( string $string ): string {
			$words = preg_split( "/\s+/", $string );

			return implode( '', array_map( function ( $word ) {
				return strtoupper( $word[0] );
			}, $words ) );
		}

		/**
		 * Convert a string to ASCII.
		 *
		 * @param string $string The string to convert.
		 *
		 * @return string The ASCII string.
		 */
		public static function to_ascii( string $string ): string {
			return remove_accents( $string );
		}

		/**
		 * Convert special characters to HTML entities.
		 *
		 * @param string $string The string to convert.
		 *
		 * @return string The converted string.
		 */
		public static function to_html_entities( string $string ): string {
			return esc_html( $string );
		}

		/**
		 * Decode HTML entities in a string
		 *
		 * @param string $string The text to decode
		 *
		 * @return string The decoded text
		 */
		public static function decode_html_entities( string $string ): string {
			return html_entity_decode( $string, ENT_QUOTES, 'UTF-8' );
		}

		/**
		 * Encode HTML entities in a string
		 *
		 * @param string $string The text to encode
		 *
		 * @return string The encoded text
		 */
		public static function encode_html_entities( string $string ): string {
			return htmlentities( $string, ENT_QUOTES, 'UTF-8' );
		}

		/** Utility Methods ********************************************************/

		/**
		 * Convert a value to a string.
		 *
		 * @param mixed $value The value to convert.
		 *
		 * @return string The string representation of the value.
		 */
		public static function to_string( $value ): string {
			if ( is_array( $value ) || is_object( $value ) ) {
				return json_encode( $value );
			}

			return (string) $value;
		}

		/**
		 * Get the length of a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return int The length of the string.
		 */
		public static function length( string $string ): int {
			return mb_strlen( $string );
		}

		/**
		 * Reverse a string.
		 *
		 * @param string $string The string to reverse.
		 *
		 * @return string The reversed string.
		 */
		public static function reverse( string $string ): string {
			return strrev( $string );
		}

		/**
		 * Get a substring of a string.
		 *
		 * @param string   $string The input string.
		 * @param int      $start  The starting position.
		 * @param int|null $length Optional. The length of the substring. If null, returns to the end of the string.
		 *
		 * @return string The substring.
		 */
		public static function substring( string $string, int $start, ?int $length = null ): string {
			return mb_substr( $string, $start, $length );
		}

		/**
		 * Count the occurrences of a substring in a string.
		 *
		 * @param string $haystack The string to search in.
		 * @param string $needle   The substring to search for.
		 *
		 * @return int The number of occurrences.
		 */
		public static function count_occurrences( string $haystack, string $needle ): int {
			return substr_count( $haystack, $needle );
		}

		/**
		 * Check if a string is palindrome.
		 *
		 * @param string $string The string to check.
		 *
		 * @return bool True if the string is a palindrome, false otherwise.
		 */
		public static function is_palindrome( string $string ): bool {
			$string = strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', $string ) );

			return $string === strrev( $string );
		}

		/**
		 * Convert a string to an array of characters.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of characters.
		 */
		public static function to_character_array( string $string ): array {
			return preg_split( '//u', $string, - 1, PREG_SPLIT_NO_EMPTY );
		}

		/**
		 * Convert a comma-separated string to an array.
		 *
		 * @param string $string The comma-separated string.
		 *
		 * @return array The resulting array.
		 */
		public static function to_array( string $string ): array {
			return array_map( 'trim', explode( ',', $string ) );
		}

		/**
		 * Wrap a string to a given number of characters.
		 *
		 * @param string $string The string to wrap.
		 * @param int    $width  The number of characters at which to wrap.
		 * @param string $break  The line break character.
		 * @param bool   $cut    Whether to cut words longer than width.
		 *
		 * @return string The wrapped string.
		 */
		public static function word_wrap( string $string, int $width = 75, string $break = "\n", bool $cut = false ): string {
			return wordwrap( $string, $width, $break, $cut );
		}

		/**
		 * Strip HTML and PHP tags from a string.
		 *
		 * @param string      $string       The input string.
		 * @param string|null $allowed_tags Optional. Tags which should not be stripped.
		 *
		 * @return string The stripped string.
		 */
		public static function strip_tags( string $string, ?string $allowed_tags = null ): string {
			return strip_tags( $string, $allowed_tags );
		}

		/**
		 * Convert line breaks to <br /> tags.
		 *
		 * @param string $string The string to convert.
		 *
		 * @return string The converted string.
		 */
		public static function nl2br( string $string ): string {
			return nl2br( $string );
		}

		/**
		 * Remove all whitespace from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return string The string with all whitespace removed.
		 */
		public static function remove_whitespace( string $string ): string {
			return preg_replace( '/\s+/', '', $string );
		}

		/**
		 * Limit the number of words in a string.
		 *
		 * @param string $string     The input string.
		 * @param int    $word_limit The number of words to limit to.
		 * @param string $end_char   Optional. The end character to append if string is truncated.
		 *
		 * @return string The truncated string.
		 */
		public static function limit_words( string $string, int $word_limit, string $end_char = '...' ): string {
			$words = explode( ' ', $string );
			if ( count( $words ) > $word_limit ) {
				return implode( ' ', array_slice( $words, 0, $word_limit ) ) . $end_char;
			}

			return $string;
		}

		/**
		 * Determine if a given string matches a given pattern.
		 *
		 * @param string|array $pattern The pattern to match against.
		 * @param string       $value   The value to check.
		 *
		 * @return bool Whether the string matches the pattern.
		 */
		public static function is_match( $pattern, string $value ): bool {
			if ( ! is_array( $pattern ) ) {
				$pattern = [ $pattern ];
			}
			foreach ( $pattern as $p ) {
				if ( preg_match( $p, $value ) === 1 ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Checks if a string's length is within a specified range.
		 *
		 * @param string $string     The string to check.
		 * @param int    $min_length The minimum allowed length for the string.
		 * @param int    $max_length The maximum allowed length for the string.
		 *
		 * @return bool Returns true if the string length is within the specified range, false otherwise.
		 */
		public static function is_length_valid( string $string, int $min_length = 1, int $max_length = PHP_INT_MAX ): bool {
			$length = strlen( $string );

			return ( $length >= $min_length && $length <= $max_length );
		}

		/**
		 * Normalize a string by trimming and converting to lowercase.
		 *
		 * @param string $value The string to normalize.
		 *
		 * @return string The normalized string.
		 */
		public static function normalize( string $value ): string {
			return strtolower( trim( $value ) );
		}

		/**
		 * Detect if a word is singular or plural.
		 *
		 * This method uses basic rules and a list of irregular forms to determine if a word is singular or plural.
		 * It's not exhaustive and may not work for all irregular forms.
		 *
		 * @param string $word The word to check.
		 *
		 * @return string Returns 'singular', 'plural', or 'unknown'.
		 */
		public static function detect_plurality( string $word ): string {
			// Normalize the word
			$word = self::normalize( $word );

			// Check for irregular plurals
			if ( array_key_exists( $word, self::IRREGULAR_PLURALS ) ) {
				return 'plural';
			}
			if ( in_array( $word, self::IRREGULAR_PLURALS ) ) {
				return 'singular';
			}

			// Check common plural endings
			$plural_endings = [ 's', 'es', 'ies' ];
			foreach ( $plural_endings as $ending ) {
				if ( self::ends_with( $word, $ending ) ) {
					// Special case for words ending in 'ies'
					if ( $ending === 'ies' && strlen( $word ) > 3 ) {
						$singular = substr( $word, 0, - 3 ) . 'y';
						if ( $singular !== $word ) {
							return 'plural';
						}
					} else {
						return 'plural';
					}
				}
			}

			// If no plural indicator is found, assume it's singular
			return 'singular';
		}

		/**
		 * Check if a word is plural.
		 *
		 * @param string $word The word to check.
		 *
		 * @return bool Returns true if the word is plural, false otherwise.
		 */
		public static function is_plural( string $word ): bool {
			return self::detect_plurality( $word ) === 'plural';
		}

		/**
		 * Check if a word is singular.
		 *
		 * @param string $word The word to check.
		 *
		 * @return bool Returns true if the word is singular, false otherwise.
		 */
		public static function is_singular( string $word ): bool {
			return self::detect_plurality( $word ) === 'singular';
		}

		/**
		 * Convert a singular word to its plural form.
		 *
		 * @param string $word The singular word to pluralize.
		 *
		 * @return string The plural form of the word.
		 */
		public static function pluralize( string $word ): string {
			$word = self::normalize( $word );

			// Check for irregular plurals
			if ( in_array( $word, self::IRREGULAR_PLURALS ) ) {
				return array_search( $word, self::IRREGULAR_PLURALS );
			}

			// Apply common pluralization rules
			if ( self::ends_with( $word, 'y' ) && ! self::ends_with( $word, [ 'ay', 'ey', 'iy', 'oy', 'uy' ] ) ) {
				return substr( $word, 0, - 1 ) . 'ies';
			} elseif ( self::ends_with( $word, [ 's', 'sh', 'ch', 'x', 'z' ] ) ) {
				return $word . 'es';
			} else {
				return $word . 's';
			}
		}

		/**
		 * Convert a plural word to its singular form.
		 *
		 * @param string $word The plural word to singularize.
		 *
		 * @return string The singular form of the word.
		 */
		public static function singularize( string $word ): string {
			$word = self::normalize( $word );

			// Check for irregular plurals
			if ( array_key_exists( $word, self::IRREGULAR_PLURALS ) ) {
				return self::IRREGULAR_PLURALS[ $word ];
			}

			// Apply common singularization rules
			if ( self::ends_with( $word, 'ies' ) && strlen( $word ) > 3 ) {
				return substr( $word, 0, - 3 ) . 'y';
			} elseif ( self::ends_with( $word, 'es' ) && ! self::ends_with( $word, [ 'aes', 'ees', 'oes' ] ) ) {
				return substr( $word, 0, - 2 );
			} elseif ( self::ends_with( $word, 's' ) && ! self::ends_with( $word, [ 'ss', 'us', 'is' ] ) ) {
				return substr( $word, 0, - 1 );
			}

			return $word;
		}

		/**
		 * Estimate the number of syllables in a word.
		 *
		 * @param string $word The word to analyze.
		 *
		 * @return int The estimated number of syllables.
		 */
		public static function count_syllables( string $word ): int {
			$word      = self::normalize( $word );
			$word      = preg_replace( '/(?:[^laeiouy]es|ed|[^laeiouy]e)$/', '', $word );
			$word      = preg_replace( '/^y/', '', $word );
			$syllables = preg_match_all( '/[aeiouy]{1,2}/', $word );

			return max( 1, $syllables );
		}

		/**
		 * Check if two words are anagrams of each other.
		 *
		 * @param string $word1 The first word.
		 * @param string $word2 The second word.
		 *
		 * @return bool True if the words are anagrams, false otherwise.
		 */
		public static function are_anagrams( string $word1, string $word2 ): bool {
			$word1 = self::normalize( $word1 );
			$word2 = self::normalize( $word2 );

			if ( strlen( $word1 ) !== strlen( $word2 ) ) {
				return false;
			}

			$chars1 = count_chars( $word1, 1 );
			$chars2 = count_chars( $word2, 1 );

			return $chars1 === $chars2;
		}

		/**
		 * Calculate the Levenshtein distance between two words.
		 *
		 * @param string $word1 The first word.
		 * @param string $word2 The second word.
		 *
		 * @return int The Levenshtein distance.
		 */
		public static function levenshtein_distance( string $word1, string $word2 ): int {
			return levenshtein( self::normalize( $word1 ), self::normalize( $word2 ) );
		}

		/**
		 * Count word frequency in a given text.
		 *
		 * @param string $text The text to analyze.
		 *
		 * @return array An associative array of words and their frequencies.
		 */
		public static function word_frequency( string $text ): array {
			$words = str_word_count( strtolower( $text ), 1 );

			return array_count_values( $words );
		}

		/**
		 * Guess the part of speech of a word.
		 *
		 * This is a very basic implementation and may not be accurate for all words.
		 *
		 * @param string $word The word to analyze.
		 *
		 * @return string The guessed part of speech ('noun', 'verb', 'adjective', or 'unknown').
		 */
		public static function guess_part_of_speech( string $word ): string {
			$word = self::normalize( $word );

			// Common adjective endings
			if ( preg_match( '/(able|ible|al|ial|ical|ish|ive|less|ous|ful)$/', $word ) ) {
				return 'adjective';
			}

			// Common verb endings
			if ( preg_match( '/(ate|ify|ise|ize|ed|ing)$/', $word ) ) {
				return 'verb';
			}

			// Common noun endings
			if ( preg_match( '/(tion|sion|ism|ity|ness|ment|ship)$/', $word ) ) {
				return 'noun';
			}

			return 'unknown';
		}

		/**
		 * Find words that rhyme with a given word.
		 *
		 * This is a basic implementation that considers words with the same ending to rhyme.
		 *
		 * @param string $word      The word to find rhymes for.
		 * @param array  $word_list An array of words to search for rhymes.
		 *
		 * @return array An array of words that potentially rhyme with the given word.
		 */
		public static function find_rhymes( string $word, array $word_list ): array {
			$word         = self::normalize( $word );
			$rhyme_ending = substr( $word, - 3 );

			return array_filter( $word_list, function ( $potential_rhyme ) use ( $word, $rhyme_ending ) {
				$potential_rhyme = self::normalize( $potential_rhyme );

				return $potential_rhyme !== $word && self::ends_with( $potential_rhyme, $rhyme_ending );
			} );
		}

		/**
		 * Split the number and period from the input string.
		 *
		 * @param string $string The input string in the format '7day', '8minutes', etc.
		 *
		 * @return array An array with 'number' and 'period' keys.
		 */
		public static function to_unit_value( string $string ): array {
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
		 * @param string $full_name The full name to split.
		 *
		 * @return array An array containing 'prefix', 'first_name', 'middle_name', 'last_name', and 'suffix'.
		 */
		public static function split_name( string $full_name ): array {
			$name_parts = preg_split( '/\s+/', trim( $full_name ) );
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

		/** Version ****************************************************************/

		/**
		 * Returns major version from version number.
		 *
		 * @param string $v Version number.
		 *
		 * @return string
		 */
		public static function major_version( string $v ): string {
			$version = explode( '.', $v );
			if ( count( $version ) > 1 ) {
				return $version[0] . '.' . $version[1];
			} else {
				return $v;
			}
		}

		/**
		 * Prettify a version number.
		 *
		 * @param string $version Version number to prettify.
		 *
		 * @return string Prettified version number.
		 */
		public static function prettify_version( string $version ): string {
			return preg_replace( '/(\d+\.\d+)\.0+$/', '$1', $version );
		}




	}

endif;