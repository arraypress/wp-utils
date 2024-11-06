<?php
/**
 * User Posts Trait
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

trait Posts {

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
	 * Get the author archive URL for a user.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return string The author archive URL or empty string.
	 */
	public static function get_author_url( int $user_id = 0 ): string {
		$user = self::get( $user_id );

		return $user ? get_author_posts_url( $user->ID ) : '';
	}

	/**
	 * Check if the user has published posts.
	 *
	 * @param int          $user_id   Optional. User ID. Default is the current user.
	 * @param string|array $post_type Optional. Post type or array of post types. Default 'post'.
	 *
	 * @return bool True if user has published posts, false otherwise.
	 */
	public static function has_published_posts( int $user_id = 0, $post_type = 'post' ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		return count_user_posts( $user->ID, $post_type, true ) > 0;
	}

	/**
	 * Count posts by a user.
	 *
	 * @param int          $user_id   The user ID.
	 * @param string|array $post_type Optional. Post type or array of post types. Default 'post'.
	 * @param string       $status    Optional. Post status. Default 'publish'.
	 *
	 * @return int Number of posts.
	 */
	public static function count_posts( int $user_id, $post_type = 'post', string $status = 'publish' ): int {
		if ( ! self::exists( $user_id ) ) {
			return 0;
		}

		$args = [
			'author'      => $user_id,
			'post_type'   => $post_type,
			'post_status' => $status,
			'fields'      => 'ids',
			'nopaging'    => true,
		];

		return count( get_posts( $args ) );
	}

}