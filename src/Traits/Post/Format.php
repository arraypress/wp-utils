<?php
/**
 * Post Format Trait
 *
 * This trait provides methods for working with WordPress post formats,
 * including format retrieval and format checking functionality.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

trait Format {

	/**
	 * Get post format.
	 *
	 * Retrieves the current format of the post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string|false The post format if set, false otherwise.
	 */
	public static function get_format( int $post_id ) {
		return get_post_format( $post_id );
	}

	/**
	 * Check if a post is in a specific format.
	 *
	 * Determines whether the post is in the specified format.
	 *
	 * @param int    $post_id The ID of the post.
	 * @param string $format  The format to check for (e.g., 'aside', 'gallery', 'link').
	 *
	 * @return bool True if the post is in the specified format, false otherwise.
	 */
	public static function is_format( int $post_id, string $format ): bool {
		return has_post_format( $format, $post_id );
	}

}