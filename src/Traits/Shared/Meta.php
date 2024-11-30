<?php
/**
 * Meta Utilities for WordPress
 *
 * This class provides utility functions for managing WordPress metadata. It includes
 * methods for splitting key-value pairs, bulk updating meta values, retrieving meta data with defaults,
 * incrementing and decrementing numeric meta values, and managing array-based meta data.
 * Additionally, it offers functionality for deleting meta data based on patterns, prefixes, or suffixes.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Shared;

use ArrayPress\Utils\Common\Compare;
use ArrayPress\Utils\Common\Cast;
use ArrayPress\Utils\Common\Sanitize;
use ArrayPress\Utils\Database\Generate;
use ArrayPress\Utils\Database\Table;

trait Meta {

	/**
	 * Get the meta type for this class.
	 *
	 * @return string The type of metadata (e.g., 'post', 'user', 'term')
	 */
	abstract protected static function get_meta_type(): string;

	/** Meta Existence ************************************************************/

	/**
	 * Check if a meta key exists for an object.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key to check.
	 *
	 * @return bool True if the meta key exists, false otherwise.
	 */
	public static function meta_exists( int $object_id, string $meta_key ): bool {
		$meta = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );

		return $meta !== '';
	}

	/** Meta Updates **************************************************************/

	/**
	 * Update a meta value only if it's different from the current value.
	 *
	 * @param int    $object_id  ID of the object metadata is for.
	 * @param string $meta_key   Meta key to update.
	 * @param mixed  $meta_value The new value for the meta.
	 * @param mixed  $prev_value Optional. Previous value to check before updating.
	 *
	 * @return bool True if the value was changed, false otherwise.
	 */
	public static function update_meta_if_changed( int $object_id, string $meta_key, $meta_value, $prev_value = '' ): bool {
		$current_value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );

		// If the new value is different from the current value, update it
		if ( $current_value !== $meta_value ) {
			return update_metadata( static::get_meta_type(), $object_id, $meta_key, $meta_value, $prev_value );
		}

		return false;
	}

	/** Meta Retrieval ************************************************************/

	/**
	 * Get meta value.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $key       Meta key to retrieve.
	 * @param bool   $single    Optional. Whether to return a single value. Default true.
	 *
	 * @return mixed Single metadata value, array of values, or null if not found.
	 */
	public static function get_meta( int $object_id, string $key, bool $single = true ) {
		$value = get_metadata( static::get_meta_type(), $object_id, $key, $single );

		// Handle empty string as null for single values
		if ( $single && $value === '' ) {
			return null;
		}

		// Handle empty array as null for multiple values
		if ( ! $single && empty( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * Get meta value with a default if not set.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key to retrieve.
	 * @param mixed  $default   Default value to return if meta doesn't exist.
	 *
	 * @return mixed The meta value or default.
	 */
	public static function get_meta_with_default( int $object_id, string $meta_key, $default ) {
		$value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );

		return $value !== '' ? $value : $default;
	}

	/**
	 * Get meta value with type casting.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key to retrieve.
	 * @param string $cast_type The type to cast to ('int', 'float', 'bool', 'array', 'string').
	 * @param mixed  $default   Default value to return if meta doesn't exist.
	 *
	 * @return mixed The meta value cast to the specified type, or default.
	 */
	public static function get_meta_cast( int $object_id, string $meta_key, string $cast_type, $default = null ) {
		$value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );

		if ( $value === '' && $default !== null ) {
			return Cast::value( $default, $cast_type );
		}

		return Cast::value( $value, $cast_type );
	}

	/**
	 * Get all meta for an object that matches a specific prefix.
	 *
	 * @param int    $object_id The object ID.
	 * @param string $prefix    The meta key prefix to match.
	 *
	 * @return array An array of meta key-value pairs.
	 */
	public static function get_meta_by_prefix( int $object_id, string $prefix ): array {
		$all_meta = get_metadata( static::get_meta_type(), $object_id );

		return array_filter( $all_meta, function ( $key ) use ( $prefix ) {
			return strpos( $key, $prefix ) === 0;
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * Get meta values associated with an object's terms.
	 *
	 * @param int    $object_id The object ID.
	 * @param string $taxonomy  The taxonomy name.
	 * @param string $meta_key  The term meta key to retrieve.
	 *
	 * @return array An array of term meta values.
	 */
	public static function get_object_terms_meta( int $object_id, string $taxonomy, string $meta_key ): array {
		// Terms can't have terms, so we return an empty array for 'term' meta type
		if ( static::get_meta_type() === 'term' ) {
			return [];
		}

		// Check if Link Manager is enabled for 'link' meta type
		if ( static::get_meta_type() === 'link' && ! get_option( 'link_manager_enabled' ) ) {
			return [];
		}

		$terms = wp_get_object_terms( $object_id, $taxonomy, [ 'fields' => 'ids' ] );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		$meta_values = [];
		foreach ( $terms as $term_id ) {
			$meta_value = get_term_meta( $term_id, $meta_key, true );
			if ( $meta_value !== '' ) {
				$meta_values[] = $meta_value;
			}
		}

		return $meta_values;
	}


	/**
	 * Retrieve a meta value with fallback to a global value and then a default.
	 *
	 * @param int                  $object_id       The ID of the object.
	 * @param string               $meta_key        The meta key to retrieve.
	 * @param callable|string|null $global_callback Optional. A callback function or function name to retrieve a global
	 *                                              value. Default is null.
	 * @param mixed                $default         Optional. A default value to use if no specific or global value is
	 *                                              found. Default is an empty string.
	 *
	 * @return mixed The retrieved value.
	 */
	public static function get_meta_with_fallback( int $object_id, string $meta_key, $global_callback = null, $default = '' ) {
		$value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );

		if ( ! empty( $value ) ) {
			return $value;
		}

		if ( $global_callback !== null ) {
			$global_value = null;
			if ( is_callable( $global_callback ) ) {
				$global_value = $global_callback();
			} elseif ( is_string( $global_callback ) && function_exists( $global_callback ) ) {
				$global_value = $global_callback();
			}

			if ( ! empty( $global_value ) ) {
				return $global_value;
			}
		}

		return $default;
	}

	/** Meta Synchronization ******************************************************/

	/**
	 * Synchronize meta from one object to another.
	 *
	 * @param string $from_type Source meta type.
	 * @param int    $from_id   Source object ID.
	 * @param string $to_type   Destination meta type.
	 * @param int    $to_id     Destination object ID.
	 * @param array  $keys      Meta keys to synchronize.
	 *
	 * @return bool True if all synchronizations were successful, false otherwise.
	 */
	public static function sync_meta( string $from_type, int $from_id, string $to_type, int $to_id, array $keys ): bool {
		$success = true;
		foreach ( $keys as $key ) {
			$value = get_metadata( $from_type, $from_id, $key, true );
			if ( ! update_metadata( $to_type, $to_id, $key, $value ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/** Meta Migration ************************************************************/

	/**
	 * Migrate meta from one key to another.
	 *
	 * @param int    $object_id  Object ID.
	 * @param string $old_key    Old meta key.
	 * @param string $new_key    New meta key.
	 * @param bool   $delete_old Whether to delete the old meta key after migration.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function migrate_meta_key( int $object_id, string $old_key, string $new_key, bool $delete_old = true ): bool {
		$value   = get_metadata( static::get_meta_type(), $object_id, $old_key, true );
		$updated = update_metadata( static::get_meta_type(), $object_id, $new_key, $value );

		if ( $updated && $delete_old ) {
			return delete_metadata( static::get_meta_type(), $object_id, $old_key );
		}

		return $updated;
	}

	/** Numeric Meta Handling *****************************************************/

	/**
	 * Increment or decrement a numeric meta value.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key to update.
	 * @param int    $amount    Amount to increment (positive) or decrement (negative).
	 *
	 * @return int|bool The new meta value on success, false on failure.
	 */
	public static function increment_meta_value( int $object_id, string $meta_key, int $amount = 1 ) {
		$current_value = (int) get_metadata( static::get_meta_type(), $object_id, $meta_key, true );
		$new_value     = $current_value + $amount;
		$success       = update_metadata( static::get_meta_type(), $object_id, $meta_key, $new_value );

		return $success ? $new_value : false;
	}

	/**
	 * Decrement a numeric meta value.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key to update.
	 * @param int    $amount    Amount to decrement (positive number).
	 *
	 * @return int|bool The new meta value on success, false on failure.
	 */
	public static function decrement_meta_value( int $object_id, string $meta_key, int $amount = 1 ) {
		$current_value = (int) get_metadata( static::get_meta_type(), $object_id, $meta_key, true );
		$new_value     = $current_value - abs( $amount ); // Use abs() to ensure we always subtract
		$success       = update_metadata( static::get_meta_type(), $object_id, $meta_key, $new_value );

		return $success ? $new_value : false;
	}

	/** Meta Array Handling *******************************************************/

	/**
	 * Check if a meta array contains a specific value.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key of the array.
	 * @param mixed  $value     Value to check for in the array.
	 *
	 * @return bool True if the value exists, false otherwise.
	 */
	public static function meta_array_contains( int $object_id, string $meta_key, $value ): bool {
		$current_array = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );

		return is_array( $current_array ) && in_array( $value, $current_array, true );
	}

	/**
	 * Append a value to a meta array.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key of the array.
	 * @param mixed  $value     Value to append.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function meta_array_append( int $object_id, string $meta_key, $value ): bool {
		$current_array = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );
		if ( ! is_array( $current_array ) ) {
			$current_array = [];
		}
		$current_array[] = $value;

		return update_metadata( static::get_meta_type(), $object_id, $meta_key, $current_array );
	}

	/**
	 * Remove all occurrences of a value from a meta array.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key of the array.
	 * @param mixed  $value     Value to remove.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function meta_array_remove_all( int $object_id, string $meta_key, $value ): bool {
		$current_array = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );
		if ( ! is_array( $current_array ) ) {
			return false;
		}
		$current_array = array_diff( $current_array, [ $value ] );

		return update_metadata( static::get_meta_type(), $object_id, $meta_key, $current_array );
	}

	/**
	 * Remove the first occurrence of a value from a meta array.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key of the array.
	 * @param mixed  $value     Value to remove.
	 *
	 * @return bool True if the value was found and removed, false otherwise.
	 */
	public static function meta_array_remove_first( int $object_id, string $meta_key, $value ): bool {
		$current_array = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );
		if ( ! is_array( $current_array ) ) {
			return false;
		}

		$key = array_search( $value, $current_array, true );
		if ( $key === false ) {
			return false;
		}

		unset( $current_array[ $key ] );

		return update_metadata( static::get_meta_type(), $object_id, $meta_key, array_values( $current_array ) );
	}

	/**
	 * Get the count of items in a meta array.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key of the array.
	 *
	 * @return int The count of items in the array.
	 */
	public static function meta_array_count( int $object_id, string $meta_key ): int {
		$current_array = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );

		return is_array( $current_array ) ? count( $current_array ) : 0;
	}

	/**
	 * Clear all values from a meta array.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key of the array.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function meta_array_clear( int $object_id, string $meta_key ): bool {
		return update_metadata( static::get_meta_type(), $object_id, $meta_key, [] );
	}

	/** Meta Deletion *************************************************************/

	/**
	 * Delete metadata for an object.
	 *
	 * @param int    $object_id The ID of the object the metadata is for.
	 * @param string $meta_key  The meta key to delete.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function delete_meta( int $object_id, string $meta_key ): bool {
		if ( empty( $object_id ) ) {
			return false;
		}

		return delete_metadata( static::get_meta_type(), $object_id, $meta_key );
	}

	/**
	 * Delete metadata if its value is different from the expected value.
	 *
	 * @param int    $object_id      The ID of the object the metadata is for.
	 * @param string $meta_key       The meta key to delete.
	 * @param mixed  $expected_value The value to compare against.
	 *
	 * @return bool True if deleted (value was different), false if not deleted (value matched or error).
	 */
	public static function delete_meta_if_different( int $object_id, string $meta_key, $expected_value ): bool {
		if ( empty( $object_id ) ) {
			return false;
		}

		$current_value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );

		// Only delete if the current value is different from the expected value
		if ( $current_value !== $expected_value ) {
			return delete_metadata( static::get_meta_type(), $object_id, $meta_key );
		}

		return false;
	}

	/**
	 * Delete metadata based on a pattern.
	 *
	 * @param string $pattern The pattern to match against meta keys.
	 * @param string $type    The type of pattern matching: 'prefix', 'suffix', 'substring', or 'exact'.
	 *
	 * @return int The number of metadata entries deleted.
	 */
	public static function delete_meta_by_pattern( string $pattern, string $type = 'exact' ): int {
		global $wpdb;

		$sql_pattern = Generate::like_pattern( $pattern, $type );
		$meta_table  = Table::get_meta_table_name( static::get_meta_type() );
		$meta_keys   = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM $meta_table WHERE meta_key LIKE %s",
				$sql_pattern
			)
		);

		$count = 0;
		foreach ( $meta_keys as $meta_key ) {
			$deleted = $wpdb->delete( $meta_table, [ 'meta_key' => $meta_key ], [ '%s' ] );
			if ( $deleted !== false ) {
				$count += $deleted;
			}
		}

		return $count;
	}

	/**
	 * Delete all metadata that are prefixed with a specific string.
	 *
	 * @param string $prefix The prefix to search for.
	 *
	 * @return int The number of metadata entries deleted.
	 */
	public static function delete_meta_by_prefix( string $prefix ): int {
		return self::delete_meta_by_pattern( $prefix, 'prefix' );
	}

	/**
	 * Delete all metadata that are suffixed with a specific string.
	 *
	 * @param string $suffix The suffix to search for.
	 *
	 * @return int The number of metadata entries deleted.
	 */
	public static function delete_meta_by_suffix( string $suffix ): int {
		return self::delete_meta_by_pattern( $suffix, 'suffix' );
	}

	/**
	 * Delete all metadata that contain a specific string.
	 *
	 * @param string $substring The substring to search for in meta keys.
	 *
	 * @return int The number of metadata entries deleted.
	 */
	public static function delete_meta_by_substring( string $substring ): int {
		return self::delete_meta_by_pattern( $substring, 'substring' );
	}

	/**
	 * Delete multiple metadata entries by their keys.
	 *
	 * @param int   $object_id The ID of the object metadata is for.
	 * @param array $keys      An array of meta keys to delete.
	 *
	 * @return int The number of metadata entries successfully deleted.
	 */
	public static function delete_meta_multiple( int $object_id, array $keys ): int {
		$meta_type = static::get_meta_type();

		return array_reduce( $keys, function ( $count, $key ) use ( $meta_type, $object_id ) {
			return $count + ( delete_metadata( $meta_type, $object_id, $key ) ? 1 : 0 );
		}, 0 );
	}

	/** Meta Comparison ***********************************************************/

	/**
	 * Compare a meta value against a given value using flexible string comparison.
	 *
	 * @param int    $object_id      Object ID.
	 * @param string $meta_key       Meta key to compare.
	 * @param string $operator       The comparison operator.
	 * @param string $value          The value to compare against.
	 * @param bool   $case_sensitive Whether the comparison should be case-sensitive. Default is true.
	 *
	 * @return bool True if the comparison is satisfied, false otherwise.
	 */
	public static function compare_meta_string( int $object_id, string $meta_key, string $operator, string $value, bool $case_sensitive = true ): bool {
		$meta_value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );
		if ( ! is_string( $meta_value ) ) {
			return false;
		}

		return Compare::string( $operator, $value, $meta_value, $case_sensitive );
	}

	/**
	 * Compare a meta value against multiple values using flexible string comparison.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key to compare.
	 * @param string $operator  The comparison operator.
	 * @param array  $values    The values to compare against.
	 *
	 * @return bool True if the comparison is satisfied, false otherwise.
	 */
	public static function compare_meta_string_multi( int $object_id, string $meta_key, string $operator, array $values ): bool {
		$meta_value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );
		if ( ! is_string( $meta_value ) ) {
			return false;
		}

		return Compare::check_string_multi( $operator, $values, $meta_value );
	}

	/**
	 * Fuzzy compare a meta value against a given value.
	 *
	 * @param int    $object_id    Object ID.
	 * @param string $meta_key     Meta key to compare.
	 * @param string $value        The value to compare against.
	 * @param int    $max_distance The maximum Levenshtein distance to consider a match.
	 *
	 * @return bool True if the fuzzy comparison is satisfied, false otherwise.
	 */
	public static function fuzzy_meta_compare( int $object_id, string $meta_key, string $value, int $max_distance = 3 ): bool {
		$meta_value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );
		if ( ! is_string( $meta_value ) ) {
			return false;
		}

		return Compare::fuzzy_string( $value, $meta_value, $max_distance );
	}

	/** Meta Search ***************************************************************/

	/**
	 * Find objects with meta values matching a specific condition.
	 *
	 * @param string $meta_key       Meta key to compare.
	 * @param string $operator       The comparison operator.
	 * @param string $value          The value to compare against.
	 * @param bool   $case_sensitive Whether the comparison should be case-sensitive. Default is true.
	 *
	 * @return array An array of object IDs that satisfy the condition.
	 */
	public static function find_objects_by_meta( string $meta_key, string $operator, string $value, bool $case_sensitive = true ): array {
		global $wpdb;
		$table     = Table::get_meta_table_name( static::get_meta_type() );
		$id_column = static::get_meta_type() . '_id';

		$query = $wpdb->prepare(
			"SELECT DISTINCT {$id_column} FROM {$table} WHERE meta_key = %s",
			$meta_key
		);

		$objects = $wpdb->get_col( $query );

		return array_filter( $objects, function ( $object_id ) use ( $meta_key, $operator, $value, $case_sensitive ) {
			return self::compare_meta_string( (int) $object_id, $meta_key, $operator, $value, $case_sensitive );
		} );
	}

	/**
	 * Find objects with meta values within a specific range.
	 *
	 * @param string $meta_key Meta key to compare.
	 * @param float  $min      The minimum value of the range.
	 * @param float  $max      The maximum value of the range.
	 *
	 * @return array An array of object IDs that have meta values within the specified range.
	 */
	public static function find_objects_by_meta_range( string $meta_key, float $min, float $max ): array {
		global $wpdb;
		$table     = Table::get_meta_table_name( static::get_meta_type() );
		$id_column = static::get_meta_type() . '_id';

		$query = $wpdb->prepare(
			"SELECT DISTINCT {$id_column} FROM {$table} WHERE meta_key = %s AND meta_value BETWEEN %f AND %f",
			$meta_key, $min, $max
		);

		return $wpdb->get_col( $query );
	}

	/** Meta Search ***************************************************************/

	/**
	 * Check if an object has a specific meta value set to true.
	 *
	 * @param int                  $object_id       The ID of the object.
	 * @param string               $meta_key        The meta key to check.
	 * @param bool                 $default         Optional. A default value to return if the meta value is not found.
	 *                                              Default is false.
	 * @param callable|string|null $global_callback Optional. A callback function or function name to retrieve a global
	 *                                              option. Default is null.
	 * @param bool                 $global_default  Optional. A default value to return if the global option is not
	 *                                              found. Default is false.
	 *
	 * @return bool True if the meta value or global option is set to true, false otherwise.
	 */
	public static function is_meta_truthy( int $object_id, string $meta_key, bool $default = false, $global_callback = null, bool $global_default = false ): bool {
		$meta_value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );

		if ( $meta_value !== '' ) {
			return (bool) $meta_value;
		}

		if ( $global_callback !== null ) {
			if ( is_callable( $global_callback ) ) {
				return (bool) $global_callback( $global_default );
			} elseif ( is_string( $global_callback ) && function_exists( $global_callback ) ) {
				return (bool) $global_callback( $global_default );
			}
		}

		return $default;
	}

	/**
	 * Check if an object has a specific meta value set to false.
	 *
	 * @param int    $object_id The ID of the object.
	 * @param string $meta_key  The meta key to check.
	 * @param bool   $default   Optional. A default value to return if the meta value is not found. Default is true.
	 *
	 * @return bool True if the meta value is set to false, false otherwise.
	 */
	public static function is_meta_falsy( int $object_id, string $meta_key, bool $default = true ): bool {
		return ! self::is_meta_truthy( $object_id, $meta_key, ! $default );
	}

	/** Bulk Methods **************************************************************/

	/**
	 * Get meta values for multiple objects based on their IDs and meta key.
	 *
	 * @param array  $object_ids An array of object IDs.
	 * @param string $meta_key   The meta key to retrieve.
	 *
	 * @return array An array of meta values keyed by object ID.
	 */
	public static function bulk_get_meta( array $object_ids, string $meta_key ): array {
		$meta_values = [];
		$object_ids  = Sanitize::object_ids( $object_ids );

		if ( empty( $object_ids ) ) {
			return $meta_values;
		}

		foreach ( $object_ids as $object_id ) {
			$meta_value = get_metadata( static::get_meta_type(), $object_id, $meta_key, true );
			if ( $meta_value !== null ) {
				$meta_values[ $object_id ] = $meta_value;
			}
		}

		return $meta_values;
	}

	/**
	 * Bulk update metadata for multiple objects.
	 *
	 * @param array  $object_ids An array of object IDs.
	 * @param string $meta_key   The meta key to update.
	 * @param mixed  $meta_value The new meta value.
	 *
	 * @return array An array of results, with object IDs as keys and update results as values.
	 */
	public static function bulk_update_meta( array $object_ids, string $meta_key, $meta_value ): array {
		$results    = [];
		$object_ids = Sanitize::object_ids( $object_ids );

		if ( empty( $object_ids ) ) {
			return $results;
		}

		foreach ( $object_ids as $object_id ) {
			$results[ $object_id ] = update_metadata( static::get_meta_type(), $object_id, $meta_key, $meta_value );
		}

		return $results;
	}

	/**
	 * Bulk delete metadata for multiple objects.
	 *
	 * @param array  $object_ids An array of object IDs.
	 * @param string $meta_key   The meta key to delete. If empty, all meta for each object will be deleted.
	 *
	 * @return array An array of results, with object IDs as keys and deletion results as values.
	 */
	public static function bulk_delete_meta( array $object_ids, string $meta_key = '' ): array {
		$results    = [];
		$object_ids = Sanitize::object_ids( $object_ids );

		if ( empty( $object_ids ) ) {
			return $results;
		}

		foreach ( $object_ids as $object_id ) {
			if ( empty( $meta_key ) ) {
				$all_meta = get_metadata( static::get_meta_type(), $object_id );
				if ( is_array( $all_meta ) ) {
					$success = true;
					foreach ( array_keys( $all_meta ) as $key ) {
						if ( ! delete_metadata( static::get_meta_type(), $object_id, $key ) ) {
							$success = false;
						}
					}
					$results[ $object_id ] = $success;
				} else {
					$results[ $object_id ] = false;
				}
			} else {
				$results[ $object_id ] = delete_metadata( static::get_meta_type(), $object_id, $meta_key );
			}
		}

		return $results;
	}

	/**
	 * Toggle a boolean meta value.
	 *
	 * @param int    $object_id Object ID.
	 * @param string $meta_key  Meta key to toggle.
	 *
	 * @return bool|null New value on success, null on failure.
	 */
	public static function toggle_meta( int $object_id, string $meta_key ): ?bool {
		$value = self::get_meta_cast( $object_id, $meta_key, 'bool', false );

		return update_metadata( static::get_meta_type(), $object_id, $meta_key, ! $value ) ? ! $value : null;
	}

}