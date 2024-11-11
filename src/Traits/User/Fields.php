<?php
/**
 * User Info Trait
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

use WP_User;

trait Fields {
	use Core;

	/**
	 * Get the user's full name, falling back to display name if not set.
	 *
	 * @param int $user_id Optional. User ID. Defaults to current user.
	 *
	 * @return string User's full name, display name, or empty string if no user found.
	 */
	public static function get_full_name( int $user_id = 0 ): string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return '';
		}

		$first     = trim( $user->first_name );
		$last      = trim( $user->last_name );
		$full_name = trim( "$first $last" );

		return empty( $full_name ) ? $user->display_name : $full_name;
	}

	/**
	 * Get the user's first name.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return string The user's first name or empty string.
	 */
	public static function get_first_name( int $user_id = 0 ): string {
		$user = self::get( $user_id );

		return $user ? $user->first_name : '';
	}

	/**
	 * Get the user's last name.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return string The user's last name or empty string.
	 */
	public static function get_last_name( int $user_id = 0 ): string {
		$user = self::get( $user_id );

		return $user ? $user->last_name : '';
	}

	/**
	 * Get the user's email address.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param string $default Optional. Default email address.
	 *
	 * @return string User email or default value.
	 */
	public static function get_email( int $user_id = 0, string $default = '' ): string {
		$user = self::get( $user_id );

		if ( ! $user || ! is_email( $user->user_email ) ) {
			return $default;
		}

		return $user->user_email;
	}

	/**
	 * Get the user's login (username).
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param string $default Optional. Default username.
	 *
	 * @return string User login or default value.
	 */
	public static function get_login( int $user_id = 0, string $default = '' ): string {
		$user = self::get( $user_id );

		return $user ? $user->user_login : $default;
	}

	/**
	 * Get the user's display name.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param string $default Optional. Default display name.
	 *
	 * @return string Display name or default value.
	 */
	public static function get_display_name( int $user_id = 0, string $default = '' ): string {
		$user = self::get( $user_id );

		return $user ? $user->display_name : $default;
	}

	/**
	 * Get the user's URL (website).
	 *
	 * Note: URL should be properly escaped (esc_url) when output in HTML context.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param string $default Optional. Default URL.
	 *
	 * @return string User's URL or default value.
	 */
	public static function get_url( int $user_id = 0, string $default = '' ): string {
		$user = self::get( $user_id );

		return $user ? $user->user_url : $default;
	}

	/**
	 * Get the user's language preference.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return string User's language or site's default locale.
	 */
	public static function get_language( int $user_id = 0 ): string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return get_locale();
		}

		$language = get_user_meta( $user->ID, 'locale', true );

		return $language ?: get_locale();
	}

	/**
	 * Get the user's description (bio).
	 *
	 * Note: The description may contain HTML content. Use appropriate escaping or
	 * sanitization based on your context (e.g., esc_html for plaintext, wp_kses
	 * for allowing specific HTML tags).
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return string User's description or empty string.
	 */
	public static function get_description( int $user_id = 0 ): string {
		$user = self::get( $user_id );

		return $user ? $user->description : '';
	}

	/**
	 * Get the user's nicename (slug).
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return string The user's nicename or empty string.
	 */
	public static function get_nicename( int $user_id = 0 ): string {
		$user = self::get( $user_id );

		return $user ? $user->user_nicename : '';
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
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		if ( isset( $user->$field ) ) {
			return $user->$field;
		}

		return get_user_meta( $user->ID, $field, true );
	}

}