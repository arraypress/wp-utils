<?php
/**
 * Term Meta Utilities
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Taxonomy;

use ArrayPress\Utils\Shared\Meta as MetaUtils;

/**
 * Check if the class `Meta` is defined, and if not, define it.
 */
if ( ! class_exists( 'Meta' ) ):

	/**
	 * Term Meta Utility Functions
	 *
	 * Provides static utility functions for term meta related operations.
	 */
	class Meta {

		/**
		 * Check if a term meta key exists.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key to check.
		 *
		 * @return bool True if the meta key exists, false otherwise.
		 */
		public static function exists( int $term_id, string $meta_key ): bool {
			return MetaUtils::exists( 'term', $term_id, $meta_key );
		}

		/**
		 * Get term meta value with optional type casting and default value.
		 *
		 * @param int    $term_id   Term ID.
		 * @param string $meta_key  Meta key to retrieve.
		 * @param string $cast_type Optional. The type to cast to ('int', 'float', 'bool', 'array', 'string').
		 * @param mixed  $default   Optional. Default value if meta doesn't exist.
		 *
		 * @return mixed The meta value cast to the specified type, or default.
		 */
		public static function get( int $term_id, string $meta_key, string $cast_type = '', $default = null ) {
			if ( empty( $cast_type ) ) {
				return MetaUtils::get_with_default( 'term', $term_id, $meta_key, $default );
			}

			return MetaUtils::get_cast( 'term', $term_id, $meta_key, $cast_type, $default );
		}

		/**
		 * Retrieve a term meta value with fallback to a global value and then a default.
		 *
		 * @param int                  $term_id         The term ID.
		 * @param string               $meta_key        The meta key to retrieve.
		 * @param callable|string|null $global_callback Optional. Callback function to retrieve a global value.
		 * @param mixed                $default         Optional. Default value if no value is found.
		 *
		 * @return mixed The retrieved value.
		 */
		public static function get_with_fallback( int $term_id, string $meta_key, $global_callback = null, $default = '' ) {
			return MetaUtils::get_with_fallback( 'term', $term_id, $meta_key, $global_callback, $default );
		}

		/**
		 * Get all term meta that matches a specific prefix.
		 *
		 * @param int    $term_id Term ID.
		 * @param string $prefix  The meta key prefix to match.
		 *
		 * @return array An array of meta key-value pairs.
		 */
		public static function get_by_prefix( int $term_id, string $prefix ): array {
			return MetaUtils::get_by_prefix( 'term', $term_id, $prefix );
		}

		/**
		 * Deletes term meta data.
		 *
		 * @param int    $term_id  The term ID.
		 * @param string $meta_key The meta key to delete.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function delete( int $term_id, string $meta_key ): bool {
			return MetaUtils::delete( 'term', $term_id, $meta_key );
		}

		/**
		 * Delete term meta if its value is different from the expected value.
		 *
		 * @param int    $term_id        The term ID.
		 * @param string $meta_key       The meta key to delete.
		 * @param mixed  $expected_value The value to compare against.
		 *
		 * @return bool True if deleted (value was different), false if not deleted (value matched or error).
		 */
		public static function delete_if_different( int $term_id, string $meta_key, $expected_value ): bool {
			return MetaUtils::delete_if_different( 'term', $term_id, $meta_key, $expected_value );
		}

		/**
		 * Update term meta value.
		 *
		 * @param int    $term_id    Term ID.
		 * @param string $meta_key   Meta key to update.
		 * @param mixed  $meta_value The new value for the meta.
		 * @param mixed  $prev_value Optional. Previous value to check before updating.
		 *
		 * @return bool True if the value was changed, false otherwise.
		 */
		public static function update_if_changed( int $term_id, string $meta_key, $meta_value, $prev_value = '' ): bool {
			return MetaUtils::update_if_changed( 'term', $term_id, $meta_key, $meta_value, $prev_value );
		}

		/**
		 * Update a part of serialized term meta data.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key.
		 * @param string $key      Key within serialized data to update.
		 * @param mixed  $value    New value.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function update_serialized( int $term_id, string $meta_key, string $key, $value ): bool {
			return MetaUtils::update_serialized( 'term', $term_id, $meta_key, $key, $value );
		}

		/**
		 * Add a value to a term meta array.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key of the array.
		 * @param mixed  $value    Value to add to the array.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function add_value_to_array( int $term_id, string $meta_key, $value ): bool {
			return MetaUtils::add_to_array( 'term', $term_id, $meta_key, $value );
		}

		/**
		 * Remove a value from a term meta array.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key of the array.
		 * @param mixed  $value    Value to remove from the array.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function remove_value_from_array( int $term_id, string $meta_key, $value ): bool {
			return MetaUtils::remove_from_array( 'term', $term_id, $meta_key, $value );
		}

		/**
		 * Check if a value exists in a term meta array.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key of the array.
		 * @param mixed  $value    Value to check for in the array.
		 *
		 * @return bool True if the value exists, false otherwise.
		 */
		public static function array_has_value( int $term_id, string $meta_key, $value ): bool {
			return MetaUtils::has_value( 'term', $term_id, $meta_key, $value );
		}

		/**
		 * Compare a term meta value against a given value.
		 *
		 * @param int    $term_id        Term ID.
		 * @param string $meta_key       Meta key to compare.
		 * @param string $operator       The comparison operator.
		 * @param mixed  $value          The value to compare against.
		 * @param bool   $case_sensitive Whether the comparison should be case-sensitive. Default true.
		 *
		 * @return bool True if the comparison is satisfied, false otherwise.
		 */
		public static function compare_string( int $term_id, string $meta_key, string $operator, $value, bool $case_sensitive = true ): bool {
			return MetaUtils::compare_string( 'term', $term_id, $meta_key, $operator, $value, $case_sensitive );
		}

		/**
		 * Increment a numeric term meta value.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key to update.
		 * @param int    $amount   Amount to increment (positive) or decrement (negative).
		 *
		 * @return int|bool The new meta value on success, false on failure.
		 */
		public static function increment_value( int $term_id, string $meta_key, int $amount = 1 ) {
			return MetaUtils::increment_value( 'term', $term_id, $meta_key, $amount );
		}

		/**
		 * Decrement a numeric term meta value.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key to update.
		 * @param int    $amount   Amount to decrement (positive number).
		 *
		 * @return int|bool The new meta value on success, false on failure.
		 */
		public static function decrement_value( int $term_id, string $meta_key, int $amount = 1 ) {
			return MetaUtils::decrement_value( 'term', $term_id, $meta_key, $amount );
		}

		/**
		 * Search for values in a term meta array that match a pattern.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key of the array.
		 * @param string $pattern  The search pattern (can include wildcards * and ?).
		 *
		 * @return array An array of matching values.
		 */
		public static function search_array( int $term_id, string $meta_key, string $pattern ): array {
			return MetaUtils::search_array( 'term', $term_id, $meta_key, $pattern );
		}

		/**
		 * Get the count of items in a term meta array.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key of the array.
		 *
		 * @return int The count of items in the array.
		 */
		public static function get_array_count( int $term_id, string $meta_key ): int {
			return MetaUtils::get_array_count( 'term', $term_id, $meta_key );
		}

		/**
		 * Clear all values from a term meta array.
		 *
		 * @param int    $term_id  Term ID.
		 * @param string $meta_key Meta key of the array.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function clear_array( int $term_id, string $meta_key ): bool {
			return MetaUtils::clear_array( 'term', $term_id, $meta_key );
		}

		/**
		 * Check if a term meta value is truthy.
		 *
		 * @param int                  $term_id         The term ID.
		 * @param string               $meta_key        The meta key to check.
		 * @param bool                 $default         Optional. Default value if meta not found.
		 * @param callable|string|null $global_callback Optional. Callback for global value.
		 * @param bool                 $global_default  Optional. Default for global option.
		 *
		 * @return bool True if the meta value or global option is truthy.
		 */
		public static function is_truthy( int $term_id, string $meta_key, bool $default = false, $global_callback = null, bool $global_default = false ): bool {
			return MetaUtils::is_truthy( 'term', $term_id, $meta_key, $default, $global_callback, $global_default );
		}

		/**
		 * Check if a term meta value is falsy.
		 *
		 * @param int    $term_id  The term ID.
		 * @param string $meta_key The meta key to check.
		 * @param bool   $default  Optional. Default value if meta not found.
		 *
		 * @return bool True if the meta value is falsy.
		 */
		public static function is_falsy( int $term_id, string $meta_key, bool $default = true ): bool {
			return MetaUtils::is_falsy( 'term', $term_id, $meta_key, $default );
		}

		/** Bulk ********************************************************************/

		/**
		 * Get user meta values based on provided term IDs and meta key.
		 *
		 * @param array  $term_ids An array of term IDs.
		 * @param string $meta_key The meta key to retrieve.
		 *
		 * @return array An array of term meta values.
		 */
		public static function get_by_ids( array $term_ids, string $meta_key ): array {
			return MetaUtils::get_by_ids( 'term', $term_ids, $meta_key );
		}

		/**
		 * Update user meta for multiple terms.
		 *
		 * @param array  $term_ids   An array of term IDs.
		 * @param string $meta_key   The meta key to update.
		 * @param mixed  $meta_value The value to update the meta key with.
		 *
		 * @return bool True if the update was successful for all terms, false otherwise.
		 */
		public static function update_by_ids( array $term_ids, string $meta_key, $meta_value ): bool {
			return MetaUtils::update_by_ids( 'term', $term_ids, $meta_key, $meta_value );
		}

		/**
		 * Bulk update taxonomy metadata for multiple terms.
		 *
		 * @param array  $term_ids   An array of term IDs.
		 * @param string $meta_key   The meta key to update.
		 * @param mixed  $meta_value The new meta value.
		 *
		 * @return array An array of results, with post IDs as keys and update results as values.
		 */
		public static function bulk_update( array $term_ids, string $meta_key, $meta_value ): array {
			return MetaUtils::bulk_update( 'term', $term_ids, $meta_key, $meta_value );
		}

		/**
		 * Bulk delete taxonomy metadata for multiple terms.
		 *
		 * @param array  $term_ids An array of term IDs.
		 * @param string $meta_key The meta key to delete. If empty, all meta for each post will be deleted.
		 *
		 * @return array An array of results, with term IDs as keys and deletion results as values.
		 */
		public static function bulk_delete( array $term_ids, string $meta_key = '' ): array {
			return MetaUtils::bulk_delete( 'term', $term_ids, $meta_key );
		}

	}

endif;