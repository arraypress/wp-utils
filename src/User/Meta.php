<?php
/**
 * User Meta Utilities
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\User;

use ArrayPress\Utils\Shared\Meta as MetaUtils;

/**
 * Check if the class `Meta` is defined, and if not, define it.
 */
if ( ! class_exists( 'Meta' ) ) :

	/**
	 * User Meta Utility Functions
	 *
	 * Provides static utility functions for user meta related operations.
	 */
	class Meta {

		/**
		 * Check if a user meta key exists.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key to check.
		 *
		 * @return bool True if the meta key exists, false otherwise.
		 */
		public static function exists( int $user_id, string $meta_key ): bool {
			return MetaUtils::exists( 'user', $user_id, $meta_key );
		}

		/**
		 * Get user meta value with optional type casting and default value.
		 *
		 * @param int    $user_id   User ID.
		 * @param string $meta_key  Meta key to retrieve.
		 * @param string $cast_type Optional. The type to cast to ('int', 'float', 'bool', 'array', 'string').
		 * @param mixed  $default   Optional. Default value if meta doesn't exist.
		 *
		 * @return mixed The meta value cast to the specified type, or default.
		 */
		public static function get( int $user_id, string $meta_key, string $cast_type = '', $default = null ) {
			if ( empty( $cast_type ) ) {
				return MetaUtils::get_with_default( 'user', $user_id, $meta_key, $default );
			}

			return MetaUtils::get_cast( 'user', $user_id, $meta_key, $cast_type, $default );
		}

		/**
		 * Retrieve a user meta value with fallback to a global value and then a default.
		 *
		 * @param int                  $user_id         The user ID.
		 * @param string               $meta_key        The meta key to retrieve.
		 * @param callable|string|null $global_callback Optional. Callback function to retrieve a global value.
		 * @param mixed                $default         Optional. Default value if no value is found.
		 *
		 * @return mixed The retrieved value.
		 */
		public static function get_with_fallback( int $user_id, string $meta_key, $global_callback = null, $default = '' ) {
			return MetaUtils::get_with_fallback( 'user', $user_id, $meta_key, $global_callback, $default );
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
			return MetaUtils::delete( 'user', $term_id, $meta_key );
		}

		/**
		 * Delete user meta if its value is different from the expected value.
		 *
		 * @param int    $user_id        The user ID.
		 * @param string $meta_key       The meta key to delete.
		 * @param mixed  $expected_value The value to compare against.
		 *
		 * @return bool True if deleted (value was different), false if not deleted (value matched or error).
		 */
		public static function delete_if_different( int $user_id, string $meta_key, $expected_value ): bool {
			return MetaUtils::delete_if_different( 'user', $user_id, $meta_key, $expected_value );
		}

		/**
		 * Get all user meta that matches a specific prefix.
		 *
		 * @param int    $user_id User ID.
		 * @param string $prefix  The meta key prefix to match.
		 *
		 * @return array An array of meta key-value pairs.
		 */
		public static function get_by_prefix( int $user_id, string $prefix ): array {
			return MetaUtils::get_by_prefix( 'user', $user_id, $prefix );
		}

		/**
		 * Update user meta value.
		 *
		 * @param int    $user_id    User ID.
		 * @param string $meta_key   Meta key to update.
		 * @param mixed  $meta_value The new value for the meta.
		 * @param mixed  $prev_value Optional. Previous value to check before updating.
		 *
		 * @return bool True if the value was changed, false otherwise.
		 */
		public static function update_if_changed( int $user_id, string $meta_key, $meta_value, $prev_value = '' ): bool {
			return MetaUtils::update_if_changed( 'user', $user_id, $meta_key, $meta_value, $prev_value );
		}

		/**
		 * Update a part of serialized user meta data.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key.
		 * @param string $key      Key within serialized data to update.
		 * @param mixed  $value    New value.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function update_serialized( int $user_id, string $meta_key, string $key, $value ): bool {
			return MetaUtils::update_serialized( 'user', $user_id, $meta_key, $key, $value );
		}

		/**
		 * Add a value to a user meta array.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key of the array.
		 * @param mixed  $value    Value to add to the array.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function add_value_to_array( int $user_id, string $meta_key, $value ): bool {
			return MetaUtils::add_to_array( 'user', $user_id, $meta_key, $value );
		}

		/**
		 * Remove a value from a user meta array.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key of the array.
		 * @param mixed  $value    Value to remove from the array.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function remove_value_from_array( int $user_id, string $meta_key, $value ): bool {
			return MetaUtils::remove_from_array( 'user', $user_id, $meta_key, $value );
		}

		/**
		 * Check if a value exists in a user meta array.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key of the array.
		 * @param mixed  $value    Value to check for in the array.
		 *
		 * @return bool True if the value exists, false otherwise.
		 */
		public static function array_has_value( int $user_id, string $meta_key, $value ): bool {
			return MetaUtils::has_value( 'user', $user_id, $meta_key, $value );
		}

		/**
		 * Compare a user meta value against a given value.
		 *
		 * @param int    $user_id        User ID.
		 * @param string $meta_key       Meta key to compare.
		 * @param string $operator       The comparison operator.
		 * @param mixed  $value          The value to compare against.
		 * @param bool   $case_sensitive Whether the comparison should be case-sensitive. Default true.
		 *
		 * @return bool True if the comparison is satisfied, false otherwise.
		 */
		public static function compare_meta( int $user_id, string $meta_key, string $operator, $value, bool $case_sensitive = true ): bool {
			return MetaUtils::compare_string( 'user', $user_id, $meta_key, $operator, $value, $case_sensitive );
		}

		/**
		 * Get meta values associated with a user's terms.
		 *
		 * @param int    $user_id  The user ID.
		 * @param string $taxonomy The taxonomy name.
		 * @param string $meta_key The term meta key to retrieve.
		 *
		 * @return array An array of term meta values.
		 */
		public static function get_terms_meta( int $user_id, string $taxonomy, string $meta_key ): array {
			return MetaUtils::get_object_terms_meta( 'user', $user_id, $taxonomy, $meta_key );
		}

		/**
		 * Increment a numeric user meta value.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key to update.
		 * @param int    $amount   Amount to increment (positive) or decrement (negative).
		 *
		 * @return int|bool The new meta value on success, false on failure.
		 */
		public static function increment_value( int $user_id, string $meta_key, int $amount = 1 ) {
			return MetaUtils::increment_value( 'user', $user_id, $meta_key, $amount );
		}

		/**
		 * Decrement a numeric user meta value.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key to update.
		 * @param int    $amount   Amount to decrement (positive number).
		 *
		 * @return int|bool The new meta value on success, false on failure.
		 */
		public static function decrement_value( int $user_id, string $meta_key, int $amount = 1 ) {
			return MetaUtils::decrement_value( 'user', $user_id, $meta_key, $amount );
		}

		/**
		 * Search for values in a user meta array that match a pattern.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key of the array.
		 * @param string $pattern  The search pattern (can include wildcards * and ?).
		 *
		 * @return array An array of matching values.
		 */
		public static function search_array( int $user_id, string $meta_key, string $pattern ): array {
			return MetaUtils::search_array( 'user', $user_id, $meta_key, $pattern );
		}

		/**
		 * Get the count of items in a user meta array.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key of the array.
		 *
		 * @return int The count of items in the array.
		 */
		public static function get_array_count( int $user_id, string $meta_key ): int {
			return MetaUtils::get_array_count( 'user', $user_id, $meta_key );
		}

		/**
		 * Clear all values from a user meta array.
		 *
		 * @param int    $user_id  User ID.
		 * @param string $meta_key Meta key of the array.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function clear_array( int $user_id, string $meta_key ): bool {
			return MetaUtils::clear_array( 'user', $user_id, $meta_key );
		}

		/**
		 * Check if a user meta value is truthy.
		 *
		 * @param int                  $user_id         The user ID.
		 * @param string               $meta_key        The meta key to check.
		 * @param bool                 $default         Optional. Default value if meta not found.
		 * @param callable|string|null $global_callback Optional. Callback for global value.
		 * @param bool                 $global_default  Optional. Default for global option.
		 *
		 * @return bool True if the meta value or global option is truthy.
		 */
		public static function is_truthy( int $user_id, string $meta_key, bool $default = false, $global_callback = null, bool $global_default = false ): bool {
			return MetaUtils::is_truthy( 'user', $user_id, $meta_key, $default, $global_callback, $global_default );
		}

		/**
		 * Check if a user meta value is falsy.
		 *
		 * @param int    $user_id  The user ID.
		 * @param string $meta_key The meta key to check.
		 * @param bool   $default  Optional. Default value if meta not found.
		 *
		 * @return bool True if the meta value is falsy.
		 */
		public static function is_falsy( int $user_id, string $meta_key, bool $default = true ): bool {
			return MetaUtils::is_falsy( 'user', $user_id, $meta_key, $default );
		}

		/**
		 * Get user meta values based on provided user IDs and meta key.
		 *
		 * @param array  $user_ids An array of user IDs.
		 * @param string $meta_key The meta key to retrieve.
		 *
		 * @return array An array of user meta values.
		 */
		public static function get_by_ids( array $user_ids, string $meta_key ): array {
			return MetaUtils::get_by_ids( 'user', $user_ids, $meta_key );
		}

		/**
		 * Update user meta for multiple users.
		 *
		 * @param array  $user_ids   An array of user IDs.
		 * @param string $meta_key   The meta key to update.
		 * @param mixed  $meta_value The value to update the meta key with.
		 *
		 * @return bool True if the update was successful for all users, false otherwise.
		 */
		public static function update_by_ids( array $user_ids, string $meta_key, $meta_value ): bool {
			return MetaUtils::update_by_ids( 'user', $user_ids, $meta_key, $meta_value );
		}

	}

endif;