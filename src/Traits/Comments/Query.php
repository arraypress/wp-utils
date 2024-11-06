<?php
/**
 * Post Comments Trait
 *
 * Provides functionality for working with post comments, including
 * retrieval, counting, and status management of comments.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Comments;

use WP_Comment;

trait Query {

	/**
	 * Get comments by post.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $args    Optional. Additional query arguments.
	 *
	 * @return WP_Comment[] Array of comment objects.
	 */
	public static function get_by_post( int $post_id, array $args = [] ): array {
		$default_args = [
			'post_id' => $post_id,
			'status'  => 'approve',
			'orderby' => 'comment_date_gmt',
			'order'   => 'ASC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_comments( $args );
	}

	/**
	 * Get comments by author email.
	 *
	 * @param string $email The author's email address.
	 * @param array  $args  Optional. Additional query arguments.
	 *
	 * @return WP_Comment[] Array of comment objects.
	 */
	public static function get_by_author_email( string $email, array $args = [] ): array {
		$default_args = [
			'author_email' => $email,
			'status'       => 'approve',
			'orderby'      => 'comment_date_gmt',
			'order'        => 'DESC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_comments( $args );
	}

	/**
	 * Get comments by status.
	 *
	 * @param string|array $status The comment status(es) to retrieve.
	 * @param array        $args   Optional. Additional query arguments.
	 *
	 * @return WP_Comment[] Array of comment objects.
	 */
	public static function get_by_status( $status, array $args = [] ): array {
		$default_args = [
			'status'  => $status,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_comments( $args );
	}

	/**
	 * Get comments by date range.
	 *
	 * @param string $start_date Start date in MySQL format.
	 * @param string $end_date   End date in MySQL format.
	 * @param array  $args       Optional. Additional query arguments.
	 *
	 * @return WP_Comment[] Array of comment objects.
	 */
	public static function get_by_date_range( string $start_date, string $end_date, array $args = [] ): array {
		$default_args = [
			'date_query' => [
				[
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,
				]
			],
			'orderby'    => 'comment_date_gmt',
			'order'      => 'ASC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_comments( $args );
	}

	/**
	 * Get recent comments.
	 *
	 * @param int   $count Number of comments to retrieve.
	 * @param array $args  Optional. Additional query arguments.
	 *
	 * @return WP_Comment[] Array of comment objects.
	 */
	public static function get_recent( int $count = 10, array $args = [] ): array {
		$default_args = [
			'number'  => $count,
			'status'  => 'approve',
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_comments( $args );
	}

	/**
	 * Get comment counts by status for a post or site-wide.
	 *
	 * @param int|null $post_id Optional. Post ID for specific post counts.
	 *
	 * @return array Comment counts by status.
	 */
	public static function get_counts( ?int $post_id = null ): array {
		$counts = wp_count_comments( $post_id );

		return [
			'total'               => (int) $counts->total_comments,
			'approved'            => (int) $counts->approved,
			'awaiting_moderation' => (int) $counts->moderated,
			'spam'                => (int) $counts->spam,
			'trash'               => (int) $counts->trash
		];
	}

	/**
	 * Get hierarchical comments.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $args    Optional. Additional query arguments.
	 *
	 * @return array Nested array of comment objects.
	 */
	public static function get_hierarchical( int $post_id, array $args = [] ): array {
		$default_args = [
			'post_id'      => $post_id,
			'status'       => 'approve',
			'hierarchical' => 'threaded',
			'orderby'      => 'comment_date_gmt',
			'order'        => 'ASC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_comments( $args );
	}

}