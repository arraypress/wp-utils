<?php
/**
 * Post Status Trait
 *
 * This trait provides comprehensive functionality for working with WordPress post statuses,
 * including methods to check, get, and set post statuses, verify publication states,
 * and manage indexing permissions.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

trait Status {

	/**
	 * Checks if a post is published.
	 *
	 * Determines whether the post has the 'publish' status.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if the post is published, false otherwise.
	 */
	public static function is_published( int $post_id ): bool {
		return get_post_status( $post_id ) === 'publish';
	}

	/**
	 * Get the current status of a post.
	 *
	 * Retrieves the current publication status of the post.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return string|false The current status of the post, or false if the post doesn't exist.
	 */
	public static function get_status( int $post_id ) {
		return get_post_status( $post_id );
	}

	/**
	 * Check if a post is private.
	 *
	 * Determines whether the post is marked as private.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return bool True if the post is private, false otherwise.
	 */
	public static function is_private( int $post_id ): bool {
		return get_post_status( $post_id ) === 'private';
	}

	/**
	 * Check if a post is in draft status.
	 *
	 * Determines whether the post is currently a draft.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return bool True if the post is a draft, false otherwise.
	 */
	public static function is_draft( int $post_id ): bool {
		$status = get_post_status( $post_id );

		return $status === 'draft' || $status === 'auto-draft';
	}

	/**
	 * Check if a post is pending review.
	 *
	 * Determines whether the post is pending review by an editor.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return bool True if the post is pending review, false otherwise.
	 */
	public static function is_pending( int $post_id ): bool {
		return get_post_status( $post_id ) === 'pending';
	}

	/**
	 * Check if a post is in trash.
	 *
	 * Determines whether the post has been moved to the trash.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return bool True if the post is in trash, false otherwise.
	 */
	public static function is_trashed( int $post_id ): bool {
		return get_post_status( $post_id ) === 'trash';
	}

	/**
	 * Check if a post status allows indexing.
	 *
	 * Determines whether the current post status should allow search engine indexing.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return bool True if the post status allows indexing, false otherwise.
	 */
	public static function allows_indexing( int $post_id ): bool {
		$status             = get_post_status( $post_id );
		$indexable_statuses = [ 'publish' ];

		/**
		 * Filters the post statuses that should be considered indexable.
		 *
		 * @param array $indexable_statuses Array of post statuses that allow indexing.
		 * @param int   $post_id            The ID of the post being checked.
		 */
		$indexable_statuses = apply_filters( 'post_indexable_statuses', $indexable_statuses, $post_id );

		return in_array( $status, $indexable_statuses, true );
	}

	/**
	 * Get available post statuses.
	 *
	 * Retrieves an array of all registered post statuses.
	 *
	 * @return array Associative array of post status objects.
	 */
	public static function get_available_statuses(): array {
		return get_post_stati( [], 'objects' );
	}

	/**
	 * Set post status.
	 *
	 * Updates the status of a post to the specified status.
	 *
	 * @param int    $post_id The ID of the post.
	 * @param string $status  The new status to set.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function set_status( int $post_id, string $status ): bool {
		$post_data = [
			'ID'          => $post_id,
			'post_status' => $status
		];

		return wp_update_post( $post_data ) !== 0;
	}

	/**
	 * Get post statuses and return them in label/value format.
	 *
	 * @param array $args Optional. Arguments to filter post statuses.
	 *
	 * @return array An array of post statuses in label/value format.
	 */
	public static function get_status_options( array $args = [] ): array {
		$defaults   = [];
		$args       = wp_parse_args( $args, $defaults );
		$post_stati = get_post_stati( $args, 'objects' );

		if ( empty( $post_stati ) || ! is_array( $post_stati ) ) {
			return [];
		}

		$options = [];

		foreach ( $post_stati as $status => $details ) {
			if ( ! isset( $status, $details ) ) {
				continue;
			}

			$options[] = [
				'value' => esc_attr( $status ),
				'label' => esc_html( $details->label ?? $status ),
			];
		}

		return $options;
	}

}