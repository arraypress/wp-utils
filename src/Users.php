<?php
/**
 * Users Utilities
 *
 * @package       ArrayPress/Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

if ( ! class_exists( 'Users' ) ) :

	/**
	 * User Utility Functions
	 *
	 * Provides static utility functions for user-related operations.
	 */
	class Users {

		/** Search and Query ************************************************************/

		/**
		 * Search users by term and arguments, returning results in key/value or label/value format.
		 *
		 * @param string $search_term The term to search for.
		 * @param array  $args        The arguments to search with (e.g., role, number, etc.).
		 *
		 * @return array An array of search results.
		 */
		public static function search( string $search_term, array $args = [] ): array {
			$options = [];

			if ( empty( $search_term ) ) {
				return $options;
			}

			$default_args = [
				'search'         => '*' . $search_term . '*',
				'search_columns' => [ 'user_login', 'user_nicename', 'user_email', 'display_name' ],
				'number'         => - 1,
				'orderby'        => 'display_name',
				'order'          => 'ASC',
			];

			$args = wp_parse_args( $args, $default_args );

			$users = get_users( $args );

			if ( empty( $users ) ) {
				return $options;
			}

			foreach ( $users as $user ) {
				$options[] = [
					'label' => $user->display_name,
					'value' => $user->ID,
				];
			}

			return $options;
		}

		/**
		 * Get an array of user objects based on provided user IDs.
		 *
		 * @param int[] $user_ids An array of user IDs.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_by_ids( array $user_ids ): array {
			$users = [];

			$user_ids = Sanitize::object_ids( $user_ids );

			if ( empty( $user_ids ) ) {
				return $users;
			}

			foreach ( $user_ids as $user_id ) {
				$user = get_user_by( 'ID', $user_id );
				if ( $user ) {
					$users[] = $user;
				}
			}

			return $users;
		}

		/**
		 * Get users by meta key and value.
		 *
		 * @param string $meta_key   The meta key to search for.
		 * @param mixed  $meta_value The meta value to match.
		 * @param array  $args       Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_by_meta( string $meta_key, $meta_value, array $args = [] ): array {
			$default_args = [
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value,
				'number'     => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Get recent users.
		 *
		 * @param int   $number The number of users to retrieve.
		 * @param array $args   Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_recent( int $number = 5, array $args = [] ): array {
			$default_args = [
				'number'  => $number,
				'orderby' => 'registered',
				'order'   => 'DESC',
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Get users by role.
		 *
		 * @param string $role The role to search for.
		 * @param array  $args Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_by_role( string $role, array $args = [] ): array {
			$default_args = [
				'role'   => $role,
				'number' => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Count users by role.
		 *
		 * @param string $role The role to count users by.
		 *
		 * @return int The number of users with the specified role.
		 */
		public static function count_by_role( string $role ): int {
			$args = [
				'role'   => $role,
				'number' => - 1,
			];

			return count( get_users( $args ) );
		}

		/**
		 * Get users by a specific meta key exists.
		 *
		 * @param string $meta_key The meta key to check.
		 * @param array  $args     Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_where_meta_exists( string $meta_key, array $args = [] ): array {
			$default_args = [
				'meta_query' => [
					[
						'key'     => $meta_key,
						'compare' => 'EXISTS',
					],
				],
				'number'     => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Get users where a meta value is compared with a specific amount.
		 *
		 * @param string $meta_key The meta key to check.
		 * @param mixed  $amount   The amount to compare against.
		 * @param string $operator The comparison operator (e.g., '>', '<', '=', '!=').
		 * @param array  $args     Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_where_meta_compared( string $meta_key, $amount, string $operator, array $args = [] ): array {
			if ( ! Validate::is_valid_operator( $operator ) ) {
				return [];
			}

			$default_args = [
				'meta_query' => [
					[
						'key'     => $meta_key,
						'value'   => $amount,
						'compare' => $operator,
						'type'    => 'NUMERIC',
					],
				],
				'number'     => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Get users by a specific capability.
		 *
		 * @param string $capability The capability to search for.
		 * @param array  $args       Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_by_capability( string $capability, array $args = [] ): array {
			global $wpdb;

			$default_args = [
				'meta_query' => [
					[
						'key'     => $wpdb->prefix . 'capabilities',
						'value'   => '"' . $capability . '"',
						'compare' => 'LIKE',
					],
				],
				'number'     => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Get users with no posts.
		 *
		 * @param array $args Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_with_no_posts( array $args = [] ): array {
			$args['has_published_posts'] = false;

			return get_users( $args );
		}

		/**
		 * Get users by registration date range.
		 *
		 * @param string $start_date The start date (YYYY-MM-DD).
		 * @param string $end_date   The end date (YYYY-MM-DD).
		 * @param array  $args       Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_by_registration_date_range( string $start_date, string $end_date, array $args = [] ): array {
			$default_args = [
				'number'     => - 1,
				'date_query' => [
					[
						'after'     => $start_date,
						'before'    => $end_date,
						'inclusive' => true,
					],
				],
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Get users by email domain.
		 *
		 * @param string $domain The email domain to search for.
		 * @param array  $args   Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_by_email_domain( string $domain, array $args = [] ): array {
			$default_args = [
				'search'         => '*@' . $domain,
				'search_columns' => [ 'user_email' ],
				'number'         => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Get users by a custom meta query.
		 *
		 * @param array $meta_query The meta query array.
		 * @param array $args       Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_by_meta_query( array $meta_query, array $args = [] ): array {
			$default_args = [
				'meta_query' => $meta_query,
				'number'     => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Get users by display name.
		 *
		 * @param string $name The display name to search for.
		 * @param array  $args Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_by_name( string $name, array $args = [] ): array {
			$default_args = [
				'search'         => '*' . $name . '*',
				'search_columns' => [ 'display_name' ],
				'number'         => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/**
		 * Get users by role and meta key/value.
		 *
		 * @param string $role       The role to search for.
		 * @param string $meta_key   The meta key to search for.
		 * @param mixed  $meta_value The meta value to match.
		 * @param array  $args       Additional query arguments.
		 *
		 * @return WP_User[] An array of user objects.
		 */
		public static function get_by_role_and_meta( string $role, string $meta_key, $meta_value, array $args = [] ): array {
			$default_args = [
				'role'       => $role,
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value,
				'number'     => - 1,
			];

			$args = wp_parse_args( $args, $default_args );

			return get_users( $args );
		}

		/** Meta Operations ************************************************************/

		/**
		 * Get user meta values based on provided user IDs and meta key.
		 *
		 * @param int[]  $user_ids An array of user IDs.
		 * @param string $meta_key The meta key to retrieve.
		 *
		 * @return array An array of user meta values.
		 */
		public static function get_meta( array $user_ids, string $meta_key ): array {
			$meta_values = [];

			$user_ids = Sanitize::object_ids( $user_ids );

			if ( empty( $user_ids ) ) {
				return $meta_values;
			}

			foreach ( $user_ids as $user_id ) {
				$meta_value = get_user_meta( $user_id, $meta_key, true );
				if ( $meta_value !== null ) {
					$meta_values[ $user_id ] = $meta_value;
				}
			}

			return $meta_values;
		}

		/**
		 * Update user meta for multiple users.
		 *
		 * @param int[]  $user_ids   An array of user IDs.
		 * @param string $meta_key   The meta key to update.
		 * @param mixed  $meta_value The value to update the meta key with.
		 *
		 * @return bool True if the update was successful for all users, false otherwise.
		 */
		public static function update_meta( array $user_ids, string $meta_key, $meta_value ): bool {
			$user_ids = Sanitize::object_ids( $user_ids );

			if ( empty( $user_ids ) ) {
				return false;
			}

			$success = true;

			foreach ( $user_ids as $user_id ) {
				if ( ! update_user_meta( $user_id, $meta_key, $meta_value ) ) {
					$success = false;
				}
			}

			return $success;
		}

		/** User Management ************************************************************/

		/**
		 * Delete users by IDs.
		 *
		 * @param int[] $user_ids An array of user IDs.
		 *
		 * @return bool True if all users were deleted successfully, false otherwise.
		 */
		public static function delete_by_ids( array $user_ids ): bool {
			$user_ids = Sanitize::object_ids( $user_ids );

			if ( empty( $user_ids ) ) {
				return false;
			}

			$success = true;

			foreach ( $user_ids as $user_id ) {
				if ( ! wp_delete_user( $user_id ) ) {
					$success = false;
				}
			}

			return $success;
		}

	}

endif;
