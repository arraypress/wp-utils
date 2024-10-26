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

namespace ArrayPress\Utils\User;

use ArrayPress\Utils\Database\Exists;
use WP_User;

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

		/**
		 * Verify a WordPress user exists by ID in the database.
		 *
		 * @param int $user_id User ID.
		 *
		 * @return bool True if the user exists, false otherwise.
		 */
		public static function exists( int $user_id ): bool {
			// Bail if a user ID was not passed.
			if ( empty( $user_id ) ) {
				return false;
			}

			return Exists::row( 'users', 'ID', $user_id );
		}

		/**
		 * Retrieves user data for a given user ID, or the current user if no ID is provided.
		 *
		 * @param int $user_id Optional. User ID. Default is the current logged-in user.
		 *
		 * @return WP_User|false WP_User object on success, false on failure.
		 */
		public static function get_data( int $user_id = 0 ) {
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
		 * Retrieves the user's full name, falling back to display name if not set.
		 *
		 * @param int $user_id Optional. User ID. Defaults to current user.
		 *
		 * @return string User's full name, display name, or empty string if no user found.
		 */
		public static function get_full_name( int $user_id = 0 ): string {
			$user = self::get_data( $user_id );
			if ( ! $user ) {
				return '';
			}

			// Get and sanitize name components
			$first = trim( esc_html( $user->first_name ) );
			$last  = trim( esc_html( $user->last_name ) );

			// Combine and check full name
			$full_name = trim( "$first $last" );

			// Return display name if full name is empty
			return empty( $full_name )
				? esc_html( $user->display_name )
				: $full_name;
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