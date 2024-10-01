<?php
/**
 * User Utilities
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

use function array_intersect;
use function delete_user_meta;
use function esc_html;
use function get_avatar_url;
use function get_current_user_id;
use function get_editable_roles;
use function get_locale;
use function get_userdata;
use function get_user_by;
use function get_user_meta;
use function get_users;
use function is_array;
use function is_email;
use function is_user_logged_in;
use function update_user_meta;

/**
 * Check if the class `User` is defined, and if not, define it.
 */
if ( ! class_exists( 'User' ) ) :

	/**
	 * User Utility Functions
	 *
	 * Provides static utility functions for user-related operations.
	 */
	class User {

		/** Helpers *******************************************************************/

		/**
		 * Retrieves user data for a given user ID, or the current user if no ID is provided.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return \WP_User|false WP_User object on success, false on failure.
		 */
		private static function get_data( int $user_id = 0 ) {
			if ( empty( $user_id ) && is_user_logged_in() ) {
				$user_id = get_current_user_id();
			}

			return get_userdata( $user_id );
		}

		/**
		 * Retrieves a user's ID by their email address.
		 *
		 * @param string $email The email address to look up.
		 *
		 * @return int|false The user ID if found, false otherwise.
		 */
		public static function get_user_id_by_email( string $email ) {
			$user = get_user_by( 'email', $email );

			return $user ? $user->ID : false;
		}

		/** User Information **********************************************************/

		/**
		 * Verify a WordPress user exists by ID in the database.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return bool True if the user exists, false otherwise.
		 */
		public static function exists( int $user_id ): bool {
			global $wpdb;

			if ( empty( $user_id ) ) {
				return false;
			}

			$found = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->users} WHERE ID = %d LIMIT 1;",
					$user_id
				)
			);

			return (bool) $found;
		}

		/**
		 * Retrieves the user's full name (first and/or last name), or display name if full name is not set.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return string The user's full name, display name, or an empty string if no user found.
		 */
		public static function get_full_name( int $user_id = 0 ): string {
			$user_info = self::get_data( $user_id );

			if ( ! $user_info ) {
				return '';
			}

			$first_name = trim( esc_html( $user_info->first_name ) );
			$last_name  = trim( esc_html( $user_info->last_name ) );
			$full_name  = trim( "$first_name $last_name" );

			if ( empty( $full_name ) ) {
				return esc_html( $user_info->display_name );
			}

			return $full_name;
		}

		/**
		 * Retrieves the user's first name, if set.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return string The user's first name, or an empty string if not set.
		 */
		public static function get_first_name( int $user_id = 0 ): string {
			$user_info = self::get_data( $user_id );

			return $user_info ? esc_html( $user_info->first_name ) : '';
		}

		/**
		 * Retrieves the user's last name, if set.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return string The user's last name, or an empty string if not set.
		 */
		public static function get_last_name( int $user_id = 0 ): string {
			$user_info = self::get_data( $user_id );

			return $user_info ? esc_html( $user_info->last_name ) : '';
		}

		/**
		 * Retrieves the user's email address.
		 *
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 * @param string $default Optional. Default email address. Default is an empty string.
		 *
		 * @return string User email or default.
		 */
		public static function get_email( int $user_id = 0, string $default = '' ): string {
			$user_info = self::get_data( $user_id );

			if ( ! $user_info || ! is_email( $user_info->user_email ) ) {
				return $default;
			}

			return $user_info->user_email;
		}

		/**
		 * Retrieves the user's username (user login).
		 *
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 * @param string $default Optional. Default username. Default is an empty string.
		 *
		 * @return string User login or default.
		 */
		public static function get_user_login( int $user_id = 0, string $default = '' ): string {
			$user_info = self::get_data( $user_id );

			return $user_info ? $user_info->user_login : $default;
		}

		/**
		 * Retrieves the user's registration date.
		 *
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 * @param string $format  Optional. Date format. Default is 'Y-m-d H:i:s'.
		 * @param bool   $gmt     Optional. Whether to return GMT time. Default is false.
		 *
		 * @return string|false The user's registration date in the specified format, or false on failure.
		 */
		public static function get_registration_date( int $user_id = 0, string $format = 'Y-m-d H:i:s', bool $gmt = false ): ?string {
			$user_info = self::get_data( $user_id );

			if ( ! $user_info ) {
				return null;
			}

			$date = $user_info->user_registered;

			if ( $gmt ) {
				return mysql2date( $format, $date, false );
			}

			return get_date_from_gmt( $date, $format );
		}

		/**
		 * Retrieves the user's avatar URL.
		 *
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 * @param int    $size    Optional. Size of the avatar. Default is 96.
		 * @param string $default Optional. Default avatar URL. Default is an empty string.
		 *
		 * @return string The user's avatar URL.
		 */
		public static function get_avatar_url( int $user_id = 0, int $size = 96, string $default = '' ): string {
			$user_info = self::get_data( $user_id );

			if ( ! $user_info ) {
				return $default;
			}

			$avatar_url = get_avatar_url( $user_info->ID, [ 'size' => $size ] );

			return $avatar_url ?: $default;
		}

		/**
		 * Retrieves the user's display name.
		 *
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 * @param string $default Optional. Default display name. Default is an empty string.
		 *
		 * @return string The user's display name.
		 */
		public static function get_display_name( int $user_id = 0, string $default = '' ): string {
			$user_info = self::get_data( $user_id );

			return $user_info ? $user_info->display_name : $default;
		}

		/**
		 * Retrieves the user's URL (website).
		 *
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 * @param string $default Optional. Default URL. Default is an empty string.
		 *
		 * @return string The user's URL or default.
		 */
		public static function get_url( int $user_id = 0, string $default = '' ): string {
			$user_info = self::get_data( $user_id );

			return $user_info ? $user_info->user_url : $default;
		}

		/**
		 * Retrieves the user's language.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return string The user's language or the site's default language if not set.
		 */
		public static function get_language( int $user_id = 0 ): string {
			$user_info = self::get_data( $user_id );

			if ( ! $user_info ) {
				return get_locale(); // Fallback to the site's default language.
			}

			$language = get_user_meta( $user_info->ID, 'locale', true );

			return $language ?: get_locale();
		}

		/**
		 * Retrieves the user's description (bio).
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return string The user's description or an empty string if not set.
		 */
		public static function get_description( int $user_id = 0 ): string {
			$user_info = self::get_data( $user_id );

			if ( ! $user_info ) {
				return '';
			}

			return $user_info->description;
		}

		/** Roles and Capabilities ****************************************************/

		/**
		 * Check if the specified user has any of the specified roles.
		 *
		 * @param array|string $user_roles A single role or an array of roles to check for.
		 * @param int          $user_id    Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool Whether the specified user has any of the specified roles.
		 */
		public static function has_role( $user_roles, int $user_id = 0 ): bool {
			if ( ! is_array( $user_roles ) ) {
				$user_roles = [ $user_roles ];
			}

			$user_roles = array_unique( $user_roles );
			$user       = self::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			return count( array_intersect( $user_roles, $user->roles ) ) > 0;
		}

		/**
		 * Check if the specified user has the specified capability.
		 *
		 * @param string $capability The capability to check.
		 * @param int    $user_id    Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool Whether the specified user has the specified capability.
		 */
		public static function has_capability( string $capability, int $user_id = 0 ): bool {
			$user = self::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			return $user->has_cap( $capability );
		}

		/**
		 * Retrieves the user's primary role.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return string|false The user's primary role, or false on failure.
		 */
		public static function get_role( int $user_id = 0 ) {
			$user_info = self::get_data( $user_id );

			if ( ! $user_info || empty( $user_info->roles ) ) {
				return false;
			}

			return $user_info->roles[0];
		}

		/**
		 * Checks if the specified user is an administrator.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool True if the user is an administrator, false otherwise.
		 */
		public static function is_admin( int $user_id = 0 ): bool {
			return self::has_role( 'administrator', $user_id );
		}

		/**
		 * Sets the user's role, replacing any existing roles.
		 *
		 * @param string $role    The role to set.
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function set_user_role( string $role, int $user_id = 0 ): bool {
			$user = self::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			$user->set_role( $role );

			return in_array( $role, $user->roles );
		}

		/**
		 * Adds a role to the specified user.
		 *
		 * @param string $role    The role to add.
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function add_role( string $role, int $user_id = 0 ): bool {
			$user = self::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			$user->add_role( $role );

			return in_array( $role, $user->roles );
		}

		/**
		 * Removes a role from the specified user.
		 *
		 * @param string $role    The role to remove.
		 * @param int    $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function remove_role( string $role, int $user_id = 0 ): bool {
			$user = self::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			$user->remove_role( $role );

			return ! in_array( $role, $user->roles );
		}

		/**
		 * Retrieves all roles for the specified user.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return array|false An array of role names, or false on failure.
		 */
		public static function get_roles( int $user_id = 0 ) {
			$user = self::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			return $user->roles;
		}

		/**
		 * Retrieves all capabilities for the specified user.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return array|false An array of capabilities, or false on failure.
		 */
		public static function get_capabilities( int $user_id = 0 ) {
			$user = self::get_data( $user_id );

			if ( ! $user ) {
				return false;
			}

			return $user->allcaps;
		}

		/**
		 * Counts users, optionally filtered by role.
		 *
		 * @param string $role Optional. The role to count users for. If not provided, counts all users.
		 *
		 * @return int The number of users.
		 */
		public static function count_users_by_role( string $role = '' ): int {
			$args = array( 'fields' => 'ID' );
			if ( ! empty( $role ) ) {
				$args['role'] = $role;
			}

			return count( get_users( $args ) );
		}

		/**
		 * Retrieve role names with keys in lowercase of all roles registered in WordPress.
		 *
		 * This method fetches the role names from the global $wp_roles instance and returns an associative
		 * array containing role names where keys are role slugs converted to lowercase.
		 *
		 * @return array Associative array containing role names with lowercase role slugs as keys.
		 */
		public static function get_role_names(): array {
			global $wp_roles;

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}

			$role_names = $wp_roles->get_names();

			return array_change_key_case( $role_names, CASE_LOWER );
		}

		/**
		 * Retrieve lowercase role slugs of all roles registered in WordPress.
		 *
		 * This method fetches the role names from the global $wp_roles instance, converts them to lowercase,
		 * and returns an array containing all lowercase role slugs.
		 *
		 * @return array Array containing lowercase role slugs.
		 */
		public static function get_role_slugs(): array {
			global $wp_roles;

			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}

			$role_slugs = array_keys( $wp_roles->get_names() );

			return array_map( 'strtolower', $role_slugs );
		}

		/**
		 * Get the options for editable roles.
		 *
		 * @param bool $include_guest Optional. Whether to include the guest role option. Default false.
		 *
		 * @return array An array of role options in label/value format.
		 */
		public static function get_role_options( bool $include_guest = false ): array {
			if ( ! function_exists( 'get_editable_roles' ) ) {
				require_once ABSPATH . '/wp-admin/includes/user.php';
			}

			$roles = get_editable_roles();

			if ( empty( $roles ) || ! is_array( $roles ) ) {
				return [];
			}

			$options = [];

			if ( $include_guest ) {
				$options[] = [
					'value' => 'guest',
					'label' => esc_html__( 'Guest', 'arraypress' ),
				];
			}

			foreach ( $roles as $role => $details ) {
				if ( ! isset( $role, $details['name'] ) ) {
					continue;
				}

				$options[] = [
					'value' => esc_attr( $role ),
					'label' => esc_html( $details['name'] ),
				];
			}

			return $options;
		}

		/** Meta Data *****************************************************************/

		/**
		 * Retrieves user meta data.
		 *
		 * @param int    $user_id The user ID.
		 * @param string $key     The meta key to retrieve.
		 * @param bool   $single  Whether to return a single value. Default true.
		 *
		 * @return mixed The meta data value(s).
		 */
		public static function get_meta( int $user_id, string $key, bool $single = true ) {
			if ( empty( $user_id ) ) {
				return null;
			}

			return get_user_meta( $user_id, $key, $single );
		}

		/**
		 * Updates user meta data.
		 *
		 * @param int    $user_id The user ID.
		 * @param string $key     The meta key to update.
		 * @param mixed  $value   The meta value to update.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function update_meta( int $user_id, string $key, $value ): bool {
			if ( empty( $user_id ) ) {
				return false;
			}

			return (bool) update_user_meta( $user_id, $key, $value );
		}

		/**
		 * Deletes user meta data.
		 *
		 * @param int    $user_id The user ID.
		 * @param string $key     The meta key to delete.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function delete_meta( int $user_id, string $key ): bool {
			if ( empty( $user_id ) ) {
				return false;
			}

			return delete_user_meta( $user_id, $key );
		}

		/**
		 * Get a specific field from the user.
		 *
		 * @param int    $user_id The user ID.
		 * @param string $field   The field name.
		 *
		 * @return mixed The field value or null if not found.
		 */
		public static function get_field( int $user_id, string $field ) {
			if ( empty( $user_id ) ) {
				return null;
			}

			$user = get_userdata( $user_id );

			if ( ! $user ) {
				return null;
			}

			// First, check if it's a property of the user object
			if ( isset( $user->$field ) ) {
				return $user->$field;
			}

			// Check if it's a custom meta field
			return get_user_meta( $user->ID, $field, true );
		}

		/** Authentication ************************************************************/

		/**
		 * Checks if a user is logged in.
		 *
		 * @return bool True if a user is logged in, false otherwise.
		 */
		public static function is_logged_in(): bool {
			return is_user_logged_in();
		}
	}

endif;