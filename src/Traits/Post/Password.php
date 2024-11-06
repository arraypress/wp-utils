<?php
/**
 * Post Password Trait
 *
 * This trait provides methods for managing WordPress post password protection,
 * including setting, removing, verifying passwords, and checking password
 * protection status.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

trait Password {

	/**
	 * Check if a post is password protected.
	 *
	 * Determines whether the post has a password set for viewing.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if the post is password protected, false otherwise.
	 */
	public static function is_password_protected( int $post_id ): bool {
		$post = get_post( $post_id );

		return $post && ! empty( $post->post_password );
	}

	/**
	 * Get post password.
	 *
	 * Retrieves the password for a password protected post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string|null The post password if set, null otherwise.
	 */
	public static function get_password( int $post_id ): ?string {
		$post = get_post( $post_id );

		return ( $post && ! empty( $post->post_password ) ) ? $post->post_password : null;
	}

	/**
	 * Set post password.
	 *
	 * Sets or removes a post's password protection.
	 *
	 * @param int         $post_id  The post ID.
	 * @param string|null $password The password to set, or null to remove protection.
	 *
	 * @return bool True if the password was set/removed successfully, false otherwise.
	 */
	public static function set_password( int $post_id, ?string $password ): bool {
		$post_data = [
			'ID'            => $post_id,
			'post_password' => $password,
		];

		$result = wp_update_post( $post_data );

		return $result !== 0 && ! is_wp_error( $result );
	}

	/**
	 * Remove post password.
	 *
	 * Removes password protection from a post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if the password was removed successfully, false otherwise.
	 */
	public static function remove_password( int $post_id ): bool {
		return self::set_password( $post_id, null );
	}

	/**
	 * Verify post password.
	 *
	 * Checks if a given password matches the post's password.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $password The password to verify.
	 *
	 * @return bool True if the password matches or post isn't protected, false otherwise.
	 */
	public static function verify_password( int $post_id, string $password ): bool {
		$post = get_post( $post_id );
		if ( ! $post || empty( $post->post_password ) ) {
			return true;
		}

		return $post->post_password === $password;
	}

	/**
	 * Check if post requires password.
	 *
	 * Determines whether a post requires a password and it hasn't been provided.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if post requires password, false otherwise.
	 */
	public static function requires_password( int $post_id ): bool {
		return post_password_required( $post_id );
	}

}