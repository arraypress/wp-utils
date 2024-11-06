<?php
/**
 * Trait: User Security
 *
 * Provides functionality for managing user security-related features
 * including verification status and IP tracking.
 *
 * @package     ArrayPress\Utils\Traits\User
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

use WP_User;

trait Security {

	/**
	 * Required trait method for getting user data.
	 *
	 * @param int  $user_id       Optional. User ID. Default is 0.
	 * @param bool $allow_current Optional. Whether to allow fallback to current user. Default true.
	 *
	 * @return WP_User|null
	 */
	abstract protected static function get( int $user_id = 0, bool $allow_current = true ): ?WP_User;

	/**
	 * Check if the user account is verified.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string|null $meta_key Optional. Custom meta key for verification status.
	 *
	 * @return bool True if verified, false otherwise.
	 */
	public static function is_verified( int $user_id = 0, ?string $meta_key = null ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'verified' );

		return (bool) get_user_meta( $user->ID, $key, true );
	}

	/**
	 * Set the user's verification status.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param bool        $status   Optional. Verification status. Default true.
	 * @param string|null $meta_key Optional. Custom meta key for verification status.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function set_verified_status( int $user_id = 0, bool $status = true, ?string $meta_key = null ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'verified' );

		return update_user_meta( $user->ID, $key, $status );
	}

	/**
	 * Get the user's IP address.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string|null $meta_key Optional. Custom meta key for registration IP.
	 *
	 * @return string|null User's IP address or null if not found.
	 */
	public static function get_ip_address( int $user_id = 0, ?string $meta_key = null ): ?string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$key = $meta_key ?? self::get_meta_key( 'registration_ip' );

		return get_user_meta( $user->ID, $key, true ) ?: null;
	}

	/**
	 * Set the user's IP address.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string      $ip       IP address to set. If empty, tries to get current user IP.
	 * @param string|null $meta_key Optional. Custom meta key for registration IP.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function set_ip_address( int $user_id = 0, string $ip = '', ?string $meta_key = null ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		if ( empty( $ip ) ) {
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
		}

		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'registration_ip' );

		return update_user_meta( $user->ID, $key, $ip );
	}

	/**
	 * Track user's login IP.
	 *
	 * @param int         $user_id  Optional. User ID. Default is the current user.
	 * @param string      $ip       Optional. IP address. If empty, tries to get current IP.
	 * @param string|null $meta_key Optional. Custom meta key for last login IP.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function track_login_ip( int $user_id = 0, string $ip = '', ?string $meta_key = null ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		if ( empty( $ip ) ) {
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
		}

		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		$key = $meta_key ?? self::get_meta_key( 'last_login_ip' );

		return update_user_meta( $user->ID, $key, $ip );
	}

	/**
	 * Get all IP addresses used by a user in their comments.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return array Array of unique IP addresses used in comments.
	 */
	public static function get_comment_ips( int $user_id = 0 ): array {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return [];
		}

		global $wpdb;
		$ips = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT comment_author_IP 
            FROM {$wpdb->comments} 
            WHERE user_id = %d 
            AND comment_author_IP != ''",
			$user->ID
		) );

		return array_filter( $ips, 'filter_var', FILTER_VALIDATE_IP );
	}

}