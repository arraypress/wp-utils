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

class Arr {

	/** Sorting Operations ********************************************************/

	/**
	 * Sort an array of numeric values and filter out any invalid values.
	 *
	 * @param array $array      The array to sort and filter.
	 * @param bool  $descending Whether to sort in descending order. Default false.
	 *
	 * @return array The sorted and filtered array.
	 */
	public static function sort_numeric( array $array, bool $descending = false ): array {
		$array = array_filter( array_map( 'absint', $array ) );

		if ( $descending ) {
			arsort( $array );
		} else {
			asort( $array );
		}

		return array_values( $array );
	}

	/**
	 * Sort an array alphabetically.
	 *
	 * @param array $array      The array to sort.
	 * @param bool  $descending Whether to sort in descending order. Default false.
	 * @param int   $flags      Sorting flags. Default is SORT_REGULAR.
	 *
	 * @return array The sorted array.
	 */
	public static function sort_alphabetic( array $array, bool $descending = false, int $flags = SORT_REGULAR ): array {
		if ( $descending ) {
			arsort( $array, $flags );
		} else {
			asort( $array, $flags );
		}

		return array_values( $array );
	}

	/**
	 * Sort an array by key.
	 *
	 * @param array $array      The array to sort.
	 * @param bool  $descending Whether to sort in descending order. Default false.
	 * @param int   $flags      Sorting flags. Default is SORT_REGULAR.
	 *
	 * @return array The sorted array.
	 */
	public static function sort_by_key( array $array, bool $descending = false, int $flags = SORT_REGULAR ): array {
		if ( $descending ) {
			krsort( $array, $flags );
		} else {
			ksort( $array, $flags );
		}

		return $array;
	}

	/**
	 * Sort a multidimensional array by a specific key.
	 *
	 * @param array  $array      The array to sort.
	 * @param string $key        The key to sort by.
	 * @param bool   $descending Whether to sort in descending order. Default false.
	 *
	 * @return array The sorted array.
	 */
	public static function sort_by_column( array $array, string $key, bool $descending = false ): array {
		$sort_flag = $descending ? SORT_DESC : SORT_ASC;
		array_multisort( array_column( $array, $key ), $sort_flag, $array );

		return $array;
	}

	/** Random and Selection Operations *******************************************/

	/**
	 * Shuffle an array.
	 *
	 * @param array $array The array to shuffle.
	 *
	 * @return array The shuffled array.
	 */
	public static function shuffle( array $array ): array {
		shuffle( $array );

		return $array;
	}

	/**
	 * Get a random element from an array.
	 *
	 * @param array $array The input array.
	 *
	 * @return mixed|null A random element from the array or null if the array is empty.
	 */
	public static function random( array $array ) {
		if ( empty( $array ) ) {
			return null;
		}

		return $array[ array_rand( $array ) ];
	}

	/** Callback Validation *******************************************************/

	/**
	 * Check if all elements in the array satisfy the given condition.
	 *
	 * @param array    $array    The input array.
	 * @param callable $callback The condition to check for each element.
	 *
	 * @return bool True if all elements satisfy the condition, false otherwise.
	 */
	public static function all_by_callback( array $array, callable $callback ): bool {
		foreach ( $array as $value ) {
			if ( ! $callback( $value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if any element in the array satisfies the given condition.
	 *
	 * @param array    $array    The input array.
	 * @param callable $callback The condition to check for each element.
	 *
	 * @return bool True if any element satisfies the condition, false otherwise.
	 */
	public static function any_by_callback( array $array, callable $callback ): bool {
		foreach ( $array as $value ) {
			if ( $callback( $value ) ) {
				return true;
			}
		}

		return false;
	}

	/** Key Operations ************************************************************/

	/**
	 * Remove specified keys from an array.
	 *
	 * @param array $array          The original array.
	 * @param array $keys_to_remove The keys to remove.
	 *
	 * @return array The array with specified keys removed.
	 */
	public static function remove_by_keys( array $array, array $keys_to_remove ): array {
		foreach ( $keys_to_remove as $key ) {
			if ( array_key_exists( $key, $array ) ) {
				unset( $array[ $key ] );
			}
		}

		return $array;
	}

	/**
	 * Insert an element after a specific key in an array.
	 *
	 * @param array  $array The original array.
	 * @param string $key   The key to insert after.
	 * @param array  $new   The new element to insert.
	 *
	 * @return array The updated array.
	 */
	public static function insert_after_key( array $array, string $key, array $new ): array {
		$position = array_search( $key, array_keys( $array ) );

		if ( $position === false ) {
			$position = count( $array ); // Insert at the end if the key is not found
		} else {
			$position += 1; // Insert after the found key
		}

		return array_slice( $array, 0, $position, true ) +
		       $new +
		       array_slice( $array, $position, null, true );
	}

	/**
	 * Insert an element before a specific key in an array.
	 *
	 * @param array  $array The original array.
	 * @param string $key   The key to insert before.
	 * @param array  $new   The new element to insert.
	 *
	 * @return array The updated array.
	 */
	public static function insert_before_key( array $array, string $key, array $new ): array {
		$position = array_search( $key, array_keys( $array ) );

		if ( $position === false ) {
			$position = 0; // Insert at the beginning if the key is not found
		}

		return array_slice( $array, 0, $position, true ) +
		       $new +
		       array_slice( $array, $position, null, true );
	}

	/**
	 * Group an array of associative arrays by a specified key.
	 *
	 * @param array  $array The array of associative arrays to group.
	 * @param string $key   The key to group by.
	 *
	 * @return array The grouped array.
	 */
	public static function group_by_key( array $array, string $key ): array {
		$groups = [];
		foreach ( $array as $item ) {
			$value = $item[ $key ] ?? null;
			if ( ! isset( $groups[ $value ] ) ) {
				$groups[ $value ] = [];
			}
			$groups[ $value ][] = $item;
		}

		return $groups;
	}

	/** Conversion Operations *****************************************************/

	/**
	 * Convert an array to a delimited string.
	 *
	 * @param array  $array     The input array.
	 * @param string $delimiter The delimiter to use between array elements. Default is ','.
	 * @param string $wrapper   Optional wrapper for each element. Default is empty.
	 * @param bool   $trim      Whether to trim each element. Default is true.
	 *
	 * @return string The resulting delimited string.
	 */
	public static function to_delimited_string( array $array, string $delimiter = ',', string $wrapper = '', bool $trim = true ): string {
		$result = array_map( function ( $item ) use ( $wrapper, $trim ) {
			$item = $trim ? trim( $item ) : $item;

			return $wrapper . $item . $wrapper;
		}, $array );

		return implode( $delimiter, $result );
	}

	/**
	 * Convert an array to a JSON string.
	 *
	 * @param array $array The input array.
	 * @param int   $flags JSON encoding options. Default is 0.
	 * @param int   $depth Maximum depth to traverse. Default is 512.
	 *
	 * @return string|false The JSON representation of the array or false on failure.
	 */
	public static function to_json( array $array, int $flags = 0, int $depth = 512 ) {
		return json_encode( $array, $flags, $depth );
	}

	/**
	 * Convert an array to an XML string.
	 *
	 * @param array  $array     The input array.
	 * @param string $root      The root element name. Default is 'root'.
	 * @param string $item      The item element name for numeric arrays. Default is 'item'.
	 * @param string $attribute The attribute to use for keys in associative arrays. Default is 'name'.
	 * @param int    $indent    Number of spaces to use for indentation. Default is 2.
	 *
	 * @return string The XML representation of the array.
	 */
	public static function to_xml( array $array, string $root = 'root', string $item = 'item', string $attribute = 'name', int $indent = 2 ): string {
		$xml = new \SimpleXMLElement( "<?xml version=\"1.0\"?><$root></$root>" );

		$function = function ( $array, $xml ) use ( &$function, $item, $attribute ) {
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) ) {
					if ( is_numeric( $key ) ) {
						$key = $item;
					}
					$subnode = $xml->addChild( $key );
					$function( $value, $subnode );
				} else {
					if ( is_numeric( $key ) ) {
						$xml->addChild( $item, htmlspecialchars( (string) $value ) );
					} else {
						$xml->addChild( $key, htmlspecialchars( (string) $value ) );
					}
				}
			}
		};

		$function( $array, $xml );

		$dom                     = new \DOMDocument( '1.0' );
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput       = true;
		$dom->loadXML( $xml->asXML() );

		return $dom->saveXML();
	}

	/**
	 * Convert an array to a query string.
	 *
	 * @param array  $array      The input array.
	 * @param string $prefix     The prefix to use for array keys.
	 * @param bool   $url_encode Whether to URL-encode the values. Default is true.
	 *
	 * @return string The resulting query string.
	 */
	public static function to_query_string( array $array, string $prefix = '', bool $url_encode = true ): string {
		$result = [];
		foreach ( $array as $key => $value ) {
			$key = $prefix ? $prefix . '[' . $key . ']' : $key;

			if ( is_array( $value ) ) {
				$result[] = self::to_query_string( $value, $key, $url_encode );
			} else {
				$value    = $url_encode ? urlencode( $value ) : $value;
				$result[] = $key . '=' . $value;
			}
		}

		return implode( '&', $result );
	}

	/** Flattening and Filtering **************************************************/

	/**
	 * Convert a multidimensional array to a flat array.
	 *
	 * @param array  $array     The input array.
	 * @param string $prefix    The prefix to use for flattened keys.
	 * @param string $delimiter The delimiter to use between flattened keys. Default is '.'.
	 *
	 * @return array The flattened array.
	 *
	 * @example
	 * Input:
	 * [
	 *     'person' => [
	 *         'name' => 'John',
	 *         'age'  => 30,
	 *         'address' => [
	 *             'city' => 'New York'
	 *         ]
	 *     ]
	 * ]
	 *
	 * Output:
	 * [
	 *     'person.name' => 'John',
	 *     'person.age' => 30,
	 *     'person.address.city' => 'New York'
	 * ]
	 */
	public static function flatten_with_keys( array $array, string $prefix = '', string $delimiter = '.' ): array {
		$result = [];
		foreach ( $array as $key => $value ) {
			$new_key = $prefix ? $prefix . $delimiter . $key : $key;
			if ( is_array( $value ) ) {
				$result = array_merge( $result, self::flatten_with_keys( $value, $new_key, $delimiter ) );
			} else {
				$result[ $new_key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Flatten a multidimensional array.
	 *
	 * @param array $array The multidimensional array.
	 *
	 * @return array The flattened array.
	 *
	 * @example
	 * Input:
	 * [
	 *     'fruits' => ['apple', 'banana'],
	 *     'colors' => [
	 *         'primary' => ['red', 'blue'],
	 *         'secondary' => 'green'
	 *     ]
	 * ]
	 *
	 * Output:
	 * ['apple', 'banana', 'red', 'blue', 'green']
	 */
	public static function flatten_array( array $array ): array {
		$flat_array = [];
		array_walk_recursive( $array, function ( $item ) use ( &$flat_array ) {
			$flat_array[] = $item;
		} );

		return $flat_array;
	}

	/**
	 * Ensure the value is an array and filter unique elements.
	 *
	 * @param mixed $value The value to ensure as an array.
	 *
	 * @return array The unique elements array.
	 *
	 * @example
	 * Input:
	 * ['a', 'b', 'b', ['c', 'c', 'd']]
	 *
	 * Output:
	 * ['a', 'b', 'c', 'd']
	 */
	public static function ensure_unique( $value ): array {
		return array_unique( self::flatten_array( (array) $value ) );
	}

	/**
	 * Filter an array by an array of allowed keys.
	 *
	 * @param array $array        The array to filter.
	 * @param array $allowed_keys The allowed keys.
	 *
	 * @return array The filtered array.
	 *
	 * @example
	 * Input:
	 * array: ['name' => 'John', 'age' => 30, 'email' => 'john@example.com']
	 * allowed_keys: ['name', 'email']
	 *
	 * Output:
	 * ['name' => 'John', 'email' => 'john@example.com']
	 */
	public static function filter_by_allowed_keys( array $array, array $allowed_keys ): array {
		return array_intersect_key( $array, array_flip( $allowed_keys ) );
	}

	/** Boolean and Comparison Operations *****************************************/

	/**
	 * Normalize 'boolean' values in an array.
	 *
	 * @param array $array The array to normalize.
	 * @param array $keys  The array of keys to search and replace. If empty, all keys are processed.
	 *
	 * @return array The normalized array.
	 */
	public static function normalize_booleans( array $array, array $keys = [] ): array {
		foreach ( $array as $key => $item ) {
			if ( empty( $keys ) || in_array( $key, $keys, true ) ) {
				$array[ $key ] = self::string_to_bool( $item );
			}
		}

		return $array;
	}

	/**
	 * Check if an array has any matching elements with another array.
	 *
	 * @param array $array1 The first array to compare.
	 * @param array $array2 The second array to compare.
	 *
	 * @return bool True if there are matching elements, false otherwise.
	 */
	public static function has_matches( array $array1, array $array2 ): bool {
		return ! empty( array_intersect( $array1, $array2 ) );
	}

	/**
	 * Implode an array to an English-readable string.
	 *
	 * @param array  $array       The array to implode.
	 * @param string $conjunction The conjunction to use for the last element.
	 *
	 * @return string The imploded string.
	 *
	 * @example
	 * Input:
	 * ['apple', 'banana', 'orange']
	 *
	 * Output:
	 * 'apple, banana, and orange'
	 *
	 * Input:
	 * ['red', 'blue']
	 *
	 * Output:
	 * 'red and blue'
	 */
	public static function implode_to_english( array $array, string $conjunction = 'and' ): string {
		if ( empty( $array ) ) {
			return '';
		}

		if ( count( $array ) === 1 ) {
			return (string) reset( $array );
		}

		if ( count( $array ) === 2 ) {
			return implode( " $conjunction ", $array );
		}

		$last = array_pop( $array );

		return implode( ', ', $array ) . ", $conjunction " . $last;
	}

	/** Recursive Operations ******************************************************/

	/**
	 * Recursively merge two arrays.
	 *
	 * Unlike array_merge_recursive, this method doesn't merge arrays that are values in the input arrays.
	 * This method emulates the behavior of array_merge_recursive from PHP 7.4+.
	 *
	 * @param array $array1 The first array.
	 * @param array $array2 The second array.
	 *
	 * @return array The merged array.
	 *
	 * @example
	 * Input:
	 * array1: [
	 *     'colors' => ['red', 'blue'],
	 *     'settings' => ['theme' => 'dark']
	 * ]
	 * array2: [
	 *     'colors' => ['green'],
	 *     'settings' => ['mode' => 'compact']
	 * ]
	 *
	 * Output:
	 * [
	 *     'colors' => ['green'],
	 *     'settings' => [
	 *         'theme' => 'dark',
	 *         'mode' => 'compact'
	 *     ]
	 * ]
	 */
	public static function merge_recursive( array $array1, array $array2 ): array {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				$merged[ $key ] = self::merge_recursive( $merged[ $key ], $value );
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}

	/** Array Access and Existence ************************************************/

	/**
	 * Get a value from an array using "dot" notation.
	 *
	 * @param array  $array   The array to retrieve from.
	 * @param string $key     The key to retrieve.
	 * @param mixed  $default The default value to return if the key doesn't exist.
	 *
	 * @return mixed The retrieved value or default.
	 *
	 * @example
	 * Input:
	 * array: [
	 *     'user' => [
	 *         'info' => [
	 *             'name' => 'John'
	 *         ]
	 *     ]
	 * ]
	 * key: 'user.info.name'
	 *
	 * Output:
	 * 'John'
	 */
	public static function get( array $array, string $key, $default = null ) {
		if ( isset( $array[ $key ] ) ) {
			return $array[ $key ];
		}

		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
				return $default;
			}
			$array = $array[ $segment ];
		}

		return $array;
	}

	/**
	 * Determine if an array is associative.
	 *
	 * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
	 *
	 * @param array $array The array to check.
	 *
	 * @return bool True if the array is associative, false otherwise.
	 *
	 * @example
	 * Input:
	 * ['foo' => 'bar', 'baz' => 'qux']
	 * Output: true
	 *
	 * Input:
	 * ['apple', 'banana', 'orange']
	 * Output: false
	 */
	public static function is_assoc( array $array ): bool {
		if ( [] === $array ) {
			return false;
		}

		return array_keys( $array ) !== range( 0, count( $array ) - 1 );
	}

	/**
	 * Pluck an array of values from an array.
	 *
	 * @param array  $array The array to pluck from.
	 * @param string $key   The key to pluck.
	 *
	 * @return array The plucked values.
	 *
	 * @example
	 * Input:
	 * array: [
	 *     ['name' => 'John', 'age' => 30],
	 *     ['name' => 'Jane', 'age' => 25]
	 * ]
	 * key: 'name'
	 *
	 * Output:
	 * ['John', 'Jane']
	 */
	public static function pluck( array $array, string $key ): array {
		return array_map( function ( $item ) use ( $key ) {
			return is_object( $item ) ? $item->$key : $item[ $key ];
		}, $array );
	}

	/**
	 * Exclude specified keys from an array.
	 *
	 * @param array $array The input array.
	 * @param array $keys  The keys to exclude.
	 *
	 * @return array The filtered array.
	 *
	 * @example
	 * Input:
	 * array: ['name' => 'John', 'email' => 'john@example.com', 'password' => '123456']
	 * keys: ['password', 'email']
	 *
	 * Output:
	 * ['name' => 'John']
	 */
	public static function except( array $array, array $keys ): array {
		return array_diff_key( $array, array_flip( $keys ) );
	}

	/**
	 * Get the first element of an array.
	 *
	 * @param array $array The input array.
	 *
	 * @return mixed|null The first element or null if the array is empty.
	 */
	public static function first( array $array ) {
		return empty( $array ) ? null : reset( $array );
	}

	/**
	 * Get the last element of an array.
	 *
	 * @param array $array The input array.
	 *
	 * @return mixed|null The last element or null if the array is empty.
	 */
	public static function last( array $array ) {
		return empty( $array ) ? null : end( $array );
	}

	/**
	 * Determine whether any of the provided array elements exist in the array using "loose" comparisons.
	 *
	 * @param array $haystack The array to search.
	 * @param array $needles  The values to search for.
	 *
	 * @return bool True if any needle was found, false otherwise.
	 */
	public static function exists( array $haystack, array $needles ): bool {
		foreach ( $needles as $needle ) {
			if ( in_array( $needle, $haystack, true ) ) {
				return true;
			}
		}

		return false;
	}

	/** Array Manipulation ********************************************************/

	/**
	 * Push an item onto the beginning of an array.
	 *
	 * @param array $array The input array.
	 * @param mixed $value The value to prepend.
	 * @param mixed $key   The key to use.
	 *
	 * @return array The modified array.
	 */
	public static function prepend( array $array, $value, $key = null ): array {
		if ( is_null( $key ) ) {
			array_unshift( $array, $value );
		} else {
			$array = [ $key => $value ] + $array;
		}

		return $array;
	}

	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param array $array The input array.
	 * @param array $keys  The keys of the items to return.
	 *
	 * @return array An array containing only the specified items.
	 */
	public static function only( array $array, array $keys ): array {
		return array_intersect_key( $array, array_flip( $keys ) );
	}

	/** Array Normalization *******************************************************/

	/**
	 * Normalize an array of strings with various options.
	 *
	 * @param array $values  The array of strings to normalize.
	 * @param array $options An array of normalization options (see normalize_str for details).
	 *
	 * @return array The normalized array of strings.
	 */
	public static function normalize_array( array $values, array $options = [] ): array {
		return array_map( function ( $value ) use ( $options ) {
			return is_string( $value ) ? self::normalize_str( $value, $options ) : $value;
		}, $values );
	}

	/**
	 * Recursively normalize an array of strings or nested arrays.
	 *
	 * @param array $values  The array to normalize recursively.
	 * @param array $options An array of normalization options (see normalize_str for details).
	 *
	 * @return array The recursively normalized array.
	 */
	public static function normalize_array_recursive( array $values, array $options = [] ): array {
		foreach ( $values as $key => $value ) {
			if ( is_array( $value ) ) {
				$values[ $key ] = self::normalize_array_recursive( $value, $options );
			} elseif ( is_string( $value ) ) {
				$values[ $key ] = self::normalize_str( $value, $options );
			}
		}

		return $values;
	}


	/**
	 * Apply a callback function to the keys of an array.
	 *
	 * This method applies a callback function to the keys of an array, returning a new array with the modified keys and
	 * the same values. The callback function should take a single parameter (the key) and return the modified key. The
	 * order of the array is preserved.
	 *
	 * @param array    $array    The array to modify.
	 * @param callable $callback The callback function to apply to the keys.
	 *
	 * @return array The modified array.
	 */
	public static function map_keys( array $array, callable $callback ): array {
		$result = [];
		foreach ( $array as $key => $value ) {
			$modified_key            = $callback( $key );
			$result[ $modified_key ] = $value;
		}

		return $result;
	}

	/**
	 * Validate if an array has all the required keys.
	 *
	 * @param array $array         The array to check.
	 * @param array $required_keys The list of required keys.
	 *
	 * @return bool True if all required keys are present, false otherwise.
	 */
	public static function has_required_keys( array $array, array $required_keys ): bool {
		return count( array_intersect_key( array_flip( $required_keys ), $array ) ) === count( $required_keys );
	}

	/**
	 * Prefix all keys of an array with the given prefix.
	 *
	 * @param array  $array  The input array.
	 * @param string $prefix The prefix to add to each key.
	 *
	 * @return array The array with prefixed keys.
	 */
	public static function prefix_keys( array $array, string $prefix ): array {
		return self::map_keys( $array, function ( $key ) use ( $prefix ) {
			return $prefix . $key;
		} );
	}

	/**
	 * Suffix all keys of an array with the given suffix.
	 *
	 * @param array  $array  The input array.
	 * @param string $suffix The suffix to add to each key.
	 *
	 * @return array The array with suffixed keys.
	 */
	public static function suffix_keys( array $array, string $suffix ): array {
		return self::map_keys( $array, function ( $key ) use ( $suffix ) {
			return $key . $suffix;
		} );
	}

	/**
	 * Prefix all string values of an array with the given prefix.
	 *
	 * @param array  $array  The input array.
	 * @param string $prefix The prefix to add to each string value.
	 *
	 * @return array The array with prefixed string values.
	 */
	public static function prefix_values( array $array, string $prefix ): array {
		return array_map( function ( $value ) use ( $prefix ) {
			return is_string( $value ) ? $prefix . $value : $value;
		}, $array );
	}

	/**
	 * Suffix all string values of an array with the given suffix.
	 *
	 * @param array  $array  The input array.
	 * @param string $suffix The suffix to add to each string value.
	 *
	 * @return array The array with suffixed string values.
	 */
	public static function suffix_values( array $array, string $suffix ): array {
		return array_map( function ( $value ) use ( $suffix ) {
			return is_string( $value ) ? $value . $suffix : $value;
		}, $array );
	}

	/**
	 * Wrap all string values of an array with the given HTML tag.
	 *
	 * @param array  $array      The input array.
	 * @param string $tag        The HTML tag to wrap each string value with.
	 * @param array  $attributes Optional. An array of attributes to add to the tag.
	 *
	 * @return array The array with wrapped string values.
	 */
	public static function wrap_values_with_tag( array $array, string $tag, array $attributes = [] ): array {
		$attr_string = '';
		foreach ( $attributes as $key => $value ) {
			$attr_string .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return array_map( function ( $value ) use ( $tag, $attr_string ) {
			return is_string( $value ) ? "<{$tag}{$attr_string}>" . esc_html( $value ) . "</{$tag}>" : $value;
		}, $array );
	}

	/**
	 * Apply a callback function to all string values of an array.
	 *
	 * @param array    $array    The input array.
	 * @param callable $callback The callback function to apply to each string value.
	 *
	 * @return array The array with the callback applied to all string values.
	 */
	public static function apply_to_string_values( array $array, callable $callback ): array {
		return array_map( function ( $value ) use ( $callback ) {
			return is_string( $value ) ? $callback( $value ) : $value;
		}, $array );
	}

	/**
	 * Check if a value exists as a key in the given array.
	 *
	 * @param mixed $value          The value to search for as a key.
	 * @param array $array          The array to search in.
	 * @param bool  $case_sensitive Whether the search should be case-sensitive. Default is false.
	 *
	 * @return bool True if the value exists as a key, false otherwise.
	 */
	public static function key_exists( $value, array $array, bool $case_sensitive = false ): bool {
		if ( $case_sensitive ) {
			return array_key_exists( $value, $array );
		}

		$keys = array_map( 'strtolower', array_keys( $array ) );

		return in_array( strtolower( $value ), $keys, true );
	}

	/**
	 * Check if a value exists in an array, including nested arrays.
	 *
	 * @param mixed $needle   The value to search for.
	 * @param array $haystack The array to search in.
	 * @param bool  $strict   Whether to use strict comparison. Default is false.
	 *
	 * @return bool True if the value is found, false otherwise.
	 */
	public static function nested_value_exists( $needle, array $haystack, bool $strict = false ): bool {
		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && self::nested_value_exists( $needle, $item, $strict ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Convert a value-label array to a key-value array.
	 *
	 * This function takes an array of associative arrays with 'value' and 'label' keys
	 * and converts it to a simple key-value array where 'value' becomes the key.
	 * It returns an empty array if the input is empty.
	 *
	 * @param array $input The input array in value-label format.
	 *
	 * @return array The converted key-value array.
	 */
	public static function to_key_value( array $input ): array {
		if ( empty( $input ) ) {
			return [];
		}

		$output = [];

		foreach ( $input as $item ) {
			if ( isset( $item['value'] ) && isset( $item['label'] ) ) {
				$output[ $item['value'] ] = $item['label'];
			}
		}

		return $output;
	}

	/** Helpers ******************************************************************/

	/**
	 * Convert a string to a boolean value.
	 *
	 * @param mixed $value The value to convert.
	 *
	 * @return bool The boolean value.
	 */
	private static function string_to_bool( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			$value = strtolower( trim( $value ) );

			return in_array( $value, [ 'true', '1', 'yes', 'on' ], true );
		}

		return (bool) $value;
	}

	/**
	 * Normalize a string with various options.
	 *
	 * @param string $value   The string to normalize.
	 * @param array  $options An array of normalization options:
	 *                        - 'case': 'lower' (default), 'upper', or 'none'
	 *                        - 'trim': true (default) or false
	 *                        - 'remove_spaces': true or false (default)
	 *                        - 'transliterate': true or false (default)
	 *
	 * @return string The normalized string.
	 */
	private static function normalize_str( string $value, array $options = [] ): string {
		$defaults = [
			'case'          => 'lower',
			'trim'          => true,
			'remove_spaces' => false,
			'transliterate' => false,
		];

		$options = array_merge( $defaults, $options );

		if ( $options['trim'] ) {
			$value = trim( $value );
		}

		if ( $options['remove_spaces'] ) {
			$value = str_replace( ' ', '', $value );
		}

		if ( $options['transliterate'] && function_exists( 'transliterator_transliterate' ) ) {
			$value = transliterator_transliterate( 'Any-Latin; Latin-ASCII', $value );
		}

		switch ( $options['case'] ) {
			case 'lower':
				return mb_strtolower( $value, 'UTF-8' );
			case 'upper':
				return mb_strtoupper( $value, 'UTF-8' );
			default:
				return $value;
		}
	}

	/**
	 * Get a nested value from an array using "dot" notation.
	 *
	 * @param array  $array   The array to retrieve from.
	 * @param string $key     Key using dot notation (e.g., 'parent.child').
	 * @param mixed  $default Default value if key doesn't exist.
	 *
	 * @return mixed Value at the specified key or default.
	 *
	 * @example
	 * Input:
	 * array: [
	 *     'user' => [
	 *         'profile' => [
	 *             'name' => 'John'
	 *         ]
	 *     ]
	 * ]
	 * key: 'user.profile.name'
	 *
	 * Output: 'John'
	 */
	public static function get_nested( array $array, string $key, $default = null ) {
		if ( isset( $array[ $key ] ) ) {
			return $array[ $key ];
		}

		$keys = explode( '.', $key );
		foreach ( $keys as $segment ) {
			if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
				return $default;
			}
			$array = $array[ $segment ];
		}

		return $array;
	}

	/**
	 * Set a nested value in an array using "dot" notation.
	 *
	 * @param array  $array The array to modify.
	 * @param string $key   Key using dot notation (e.g., 'parent.child').
	 * @param mixed  $value Value to set.
	 *
	 * @return array The modified array.
	 *
	 * @example
	 * Input:
	 * array: ['user' => ['profile' => []]]
	 * key: 'user.profile.name'
	 * value: 'John'
	 *
	 * Output: ['user' => ['profile' => ['name' => 'John']]]
	 */
	public static function set_nested( array $array, string $key, $value ): array {
		if ( is_null( $key ) ) {
			return $array;
		}

		$keys    = explode( '.', $key );
		$current = &$array;

		foreach ( $keys as $segment ) {
			if ( ! is_array( $current ) ) {
				$current = [];
			}
			$current = &$current[ $segment ];
		}

		$current = $value;

		return $array;
	}

	/**
	 * Remove a nested key from an array using "dot" notation.
	 *
	 * @param array  $array The array to modify.
	 * @param string $key   Key using dot notation (e.g., 'parent.child').
	 *
	 * @return array The modified array.
	 *
	 * @example
	 * Input:
	 * array: ['user' => ['profile' => ['name' => 'John']]]
	 * key: 'user.profile.name'
	 *
	 * Output: ['user' => ['profile' => []]]
	 */
	public static function remove_nested( array $array, string $key ): array {
		$keys    = explode( '.', $key );
		$last    = array_pop( $keys );
		$current = &$array;

		foreach ( $keys as $segment ) {
			if ( ! is_array( $current ) || ! array_key_exists( $segment, $current ) ) {
				return $array;
			}
			$current = &$current[ $segment ];
		}

		if ( is_array( $current ) && array_key_exists( $last, $current ) ) {
			unset( $current[ $last ] );
		}

		return $array;
	}

	/**
	 * Check if a nested key exists in an array using "dot" notation.
	 *
	 * @param array  $array The array to check.
	 * @param string $key   Key using dot notation (e.g., 'parent.child').
	 *
	 * @return bool True if the nested key exists, false otherwise.
	 *
	 * @example
	 * Input:
	 * array: ['user' => ['profile' => ['name' => 'John']]]
	 * key: 'user.profile.name'
	 *
	 * Output: true
	 */
	public static function has_nested( array $array, string $key ): bool {
		if ( isset( $array[ $key ] ) ) {
			return true;
		}

		$keys = explode( '.', $key );
		foreach ( $keys as $segment ) {
			if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
				return false;
			}
			$array = $array[ $segment ];
		}

		return true;
	}

	/**
	 * Merge arrays with support for recursive merging.
	 *
	 * @param array $array1    Initial array.
	 * @param array $array2    Array to merge.
	 * @param bool  $recursive Whether to merge recursively. Default true.
	 *
	 * @return array The merged array.
	 *
	 * @example
	 * Input:
	 * array1: ['user' => ['name' => 'John']]
	 * array2: ['user' => ['age' => 30]]
	 * recursive: true
	 *
	 * Output: ['user' => ['name' => 'John', 'age' => 30]]
	 */
	public static function merge( array $array1, array $array2, bool $recursive = true ): array {
		if ( $recursive ) {
			return self::merge_recursive( $array1, $array2 );
		}

		return array_merge( $array1, $array2 );
	}

}