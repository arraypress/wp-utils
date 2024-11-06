<?php
/**
 * Post Sticky Trait
 *
 * This trait provides functionality for managing sticky posts in WordPress,
 * including checking and modifying a post's sticky status.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

trait Sticky {

	/**
	 * Check if post is sticky.
	 *
	 * Determines whether the post is marked as sticky.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if the post is sticky, false otherwise.
	 */
	public static function is_sticky( int $post_id ): bool {
		return is_sticky( $post_id );
	}

	/**
	 * Set post sticky status.
	 *
	 * Changes the sticky status of a post.
	 *
	 * @param int  $post_id The post ID.
	 * @param bool $sticky  Whether to make the post sticky or not.
	 *
	 * @return bool True if the sticky status was changed successfully, false otherwise.
	 */
	public static function set_sticky( int $post_id, bool $sticky = true ): bool {
		if ( $sticky ) {
			stick_post( $post_id );
		} else {
			unstick_post( $post_id );
		}

		return self::is_sticky( $post_id ) === $sticky;
	}

}