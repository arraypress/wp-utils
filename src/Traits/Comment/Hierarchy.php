<?php
/**
 * Comment Hierarchy Trait
 *
 * Provides functionality for working with WordPress comment hierarchies.
 * Includes methods for managing parent-child relationships, determining
 * comment depths, and retrieving threaded comment structures.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Comment;

use WP_Comment;

trait Hierarchy {

	/**
	 * Get comment parent.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return WP_Comment|null Parent comment object or null if no parent exists.
	 */
	public static function get_parent( int $comment_id ): ?WP_Comment {
		$comment = get_comment( $comment_id );
		if ( ! $comment || ! $comment->comment_parent ) {
			return null;
		}

		$parent = get_comment( $comment->comment_parent );

		return $parent instanceof WP_Comment ? $parent : null;
	}

	/**
	 * Get comment children.
	 *
	 * @param int   $comment_id The parent comment ID.
	 * @param array $args       Optional. Additional arguments for comment query.
	 *
	 * @return WP_Comment[] Array of child comment objects.
	 */
	public static function get_children( int $comment_id, array $args = [] ): array {
		$default_args = [
			'parent'  => $comment_id,
			'status'  => 'approve',
			'orderby' => 'comment_date_gmt',
			'order'   => 'ASC'
		];

		return get_comments( wp_parse_args( $args, $default_args ) );
	}

	/**
	 * Get comment depth.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return int The depth of the comment (0 for top-level comments).
	 */
	public static function get_depth( int $comment_id ): int {
		$depth   = 0;
		$comment = get_comment( $comment_id );

		while ( $comment && $comment->comment_parent ) {
			$depth ++;
			$comment = get_comment( $comment->comment_parent );
		}

		return $depth;
	}

	/**
	 * Get comment thread.
	 *
	 * Retrieves all comments in the thread (parent and all replies).
	 *
	 * @param int   $comment_id The comment ID.
	 * @param array $args       Optional. Additional arguments for comment query.
	 *
	 * @return WP_Comment[] Array of comment objects in the thread.
	 */
	public static function get_thread( int $comment_id, array $args = [] ): array {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return [];
		}

		// Get top-level parent
		while ( $comment->comment_parent ) {
			$comment = get_comment( $comment->comment_parent );
		}

		$default_args = [
			'thread_id' => $comment->comment_ID,
			'status'    => 'approve',
			'orderby'   => 'comment_date_gmt',
			'order'     => 'ASC'
		];

		return get_comments( wp_parse_args( $args, $default_args ) );
	}

}