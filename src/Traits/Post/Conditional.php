<?php
/**
 * Post Conditional Trait
 *
 * This trait provides methods for checking various conditional states of WordPress posts,
 * such as revision status and modification state.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

trait Conditional {

	/**
	 * Check if a post is a revision.
	 *
	 * Determines whether the post is a revision of another post.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return bool True if the post is a revision, false otherwise.
	 */
	public static function is_revision( int $post_id ): bool {
		return wp_is_post_revision( $post_id ) !== false;
	}

	/**
	 * Check if a post has been modified since publication.
	 *
	 * Determines whether the post has been modified after its initial publication.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return bool True if the post has been modified, false otherwise.
	 */
	public static function has_been_modified( int $post_id ): bool {
		$post = get_post( $post_id );

		return $post && $post->post_modified > $post->post_date;
	}

	/**
	 * Check if post has excerpt.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if post has manual excerpt, false otherwise.
	 */
	public static function has_excerpt( int $post_id ): bool {
		$post = get_post( $post_id );

		return $post && ! empty( $post->post_excerpt );
	}

	/**
	 * Check if post has content.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if post has content, false otherwise.
	 */
	public static function has_content( int $post_id ): bool {
		$post = get_post( $post_id );

		return $post && ! empty( $post->post_content );
	}

	/**
	 * Check if a post has a specific shortcode.
	 *
	 * Determines whether the post content contains a specific shortcode.
	 *
	 * @param int    $post_id   The ID of the post.
	 * @param string $shortcode The shortcode to check for.
	 *
	 * @return bool True if the post contains the shortcode, false otherwise.
	 */
	public static function has_shortcode( int $post_id, string $shortcode ): bool {
		$post = get_post( $post_id );

		return $post && has_shortcode( $post->post_content, $shortcode );
	}

	/**
	 * Check if a post contains a specific block.
	 *
	 * Determines whether the post content contains a specific Gutenberg block.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $block   The block name to check for.
	 *
	 * @return bool True if the post contains the block, false otherwise.
	 */
	public static function has_block( int $post_id, string $block ): bool {
		$post = get_post( $post_id );

		return $post && has_block( $block, $post->post_content );
	}

	/**
	 * Check if post has password.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if post has password, false otherwise.
	 */
	public static function has_password( int $post_id ): bool {
		$post = get_post( $post_id );

		return $post && ! empty( $post->post_password );
	}


	/**
	 * Check if post was modified.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool|null True if modified, false if not, null if post not found.
	 */
	public static function is_modified( int $post_id ): ?bool {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}

		return strtotime( $post->post_modified_gmt ) > strtotime( $post->post_date_gmt );
	}

	/**
	 * Check if post is scheduled.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if post is scheduled for future publication, false otherwise.
	 */
	public static function is_scheduled( int $post_id ): bool {
		$post = get_post( $post_id );

		return $post && $post->post_status === 'future';
	}

	/**
	 * Check if post date is in the future.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $from    Optional. Which date to check ('modified' or 'published'). Default 'published'.
	 *
	 * @return bool True if the post date is in the future, false otherwise.
	 */
	public static function is_future_dated( int $post_id, string $from = 'published' ): bool {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		$date = $from === 'modified' ? $post->post_modified_gmt : $post->post_date_gmt;

		return strtotime( $date ) > time();
	}

}