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

namespace ArrayPress\Utils\Traits\Post;

use WP_Post;
use WP_Comment;

trait Comments {

	/**
	 * Get comments for a post.
	 *
	 * Retrieves comments associated with a specific post.
	 *
	 * @param int|WP_Post $post    The post object or ID.
	 * @param array       $args    Optional. Array of arguments for comment retrieval. {
	 *                             Optional. An array of arguments.
	 *
	 * @type string       $status  Comment status. Default 'approve'.
	 * @type string       $type    Comment type. Default 'comment'.
	 * @type bool         $count   Whether to return only the count. Default false.
	 * @type string       $orderby How to order the comments. Default 'comment_date_gmt'.
	 * @type string       $order   Order direction. Default 'ASC'.
	 *                             }
	 *
	 * @return int|WP_Comment[] Number of comments if count requested, array of comment objects otherwise.
	 */
	public static function get_comments( $post, array $args = [] ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return $args['count'] ?? false ? 0 : [];
		}

		$default_args = [
			'post_id' => $post->ID,
			'status'  => 'approve',
			'type'    => 'comment'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_comments( $args );
	}

	/**
	 * Get comment count.
	 *
	 * Retrieves the number of comments for a specific post.
	 *
	 * @param int|WP_Post $post The post object or ID.
	 * @param array       $args Optional. Array of arguments for comment counting.
	 *
	 * @return int The number of comments.
	 */
	public static function get_comment_count( $post, array $args = [] ): int {
		$args['count'] = true;

		return self::get_comments( $post, $args );
	}

	/**
	 * Check if a post has comments.
	 *
	 * Determines whether a post has any approved comments.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return bool True if the post has comments, false otherwise.
	 */
	public static function has_comments( int $post_id ): bool {
		return get_comments_number( $post_id ) > 0;
	}

	/**
	 * Get comments by status.
	 *
	 * Retrieves comments for a post filtered by their status.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $status  Optional. Comment status to retrieve. Default 'approve'.
	 *
	 * @return WP_Comment[] Array of comment objects.
	 */
	public static function get_comments_by_status( int $post_id, string $status = 'approve' ): array {
		return get_comments( [
			'post_id' => $post_id,
			'status'  => $status
		] );
	}

	/**
	 * Get comment counts by status.
	 *
	 * Retrieves the number of comments for each status for a specific post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array{total: int, approved: int, awaiting_moderation: int, spam: int, trash: int}
	 */
	public static function get_comment_counts( int $post_id ): array {
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
	 * Get recent comments.
	 *
	 * Retrieves the most recent comments for a post.
	 *
	 * @param int   $post_id The post ID.
	 * @param int   $number  Optional. Number of comments to retrieve. Default 5.
	 * @param array $args    Optional. Additional arguments for comment query.
	 *
	 * @return WP_Comment[] Array of recent comment objects.
	 */
	public static function get_recent_comments( int $post_id, int $number = 5, array $args = [] ): array {
		$default_args = [
			'post_id' => $post_id,
			'number'  => $number,
			'status'  => 'approve',
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_comments( $args );
	}

	/**
	 * Get comment authors.
	 *
	 * Retrieves unique comment authors for a post.
	 *
	 * @param int  $post_id The post ID.
	 * @param bool $count   Optional. Whether to return count of unique authors. Default false.
	 *
	 * @return array|int Array of unique author names or count if requested.
	 */
	public static function get_comment_authors( int $post_id, bool $count = false ) {
		$comments = get_comments( [
			'post_id' => $post_id,
			'status'  => 'approve'
		] );

		$authors = array_unique( array_map( function ( $comment ) {
			return $comment->comment_author;
		}, $comments ) );

		return $count ? count( $authors ) : $authors;
	}

	/**
	 * Get hierarchical comments.
	 *
	 * Retrieves comments in a threaded, hierarchical structure.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $args    Optional. Additional arguments for comment query.
	 *
	 * @return array Nested array of comments.
	 */
	public static function get_hierarchical_comments( int $post_id, array $args = [] ): array {
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

	/**
	 * Get comment parent.
	 *
	 * Retrieves the parent comment of a given comment.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return WP_Comment|null Parent comment object or null if no parent exists.
	 */
	public static function get_comment_parent( int $comment_id ): ?WP_Comment {
		$comment = get_comment( $comment_id );
		if ( ! $comment || ! $comment->comment_parent ) {
			return null;
		}

		$parent = get_comment( $comment->comment_parent );

		return ( $parent instanceof WP_Comment ) ? $parent : null;
	}

	/**
	 * Get comment children.
	 *
	 * Retrieves direct replies to a specific comment.
	 *
	 * @param int   $comment_id The parent comment ID.
	 * @param array $args       Optional. Additional arguments for comment query.
	 *
	 * @return WP_Comment[] Array of child comment objects.
	 */
	public static function get_comment_children( int $comment_id, array $args = [] ): array {
		$default_args = [
			'parent'  => $comment_id,
			'status'  => 'approve',
			'orderby' => 'comment_date_gmt',
			'order'   => 'ASC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_comments( $args );
	}

	/**
	 * Get comment depth.
	 *
	 * Calculates how deep in the comment hierarchy a comment is.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return int The depth of the comment (0 for top-level comments).
	 */
	public static function get_comment_depth( int $comment_id ): int {
		$depth   = 0;
		$comment = get_comment( $comment_id );

		while ( $comment && $comment->comment_parent ) {
			$depth ++;
			$comment = get_comment( $comment->comment_parent );
		}

		return $depth;
	}

	/**
	 * Check if comments are open.
	 *
	 * Determines whether comments are currently open for a post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if comments are open, false otherwise.
	 */
	public static function are_comments_open( int $post_id ): bool {
		return comments_open( $post_id );
	}

	/**
	 * Get comment feed link.
	 *
	 * Retrieves the comment feed URL for a post.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $feed    Optional. Feed type ('rss2', 'atom'). Default 'rss2'.
	 *
	 * @return string Comment feed URL.
	 */
	public static function get_comment_feed_link( int $post_id, string $feed = 'rss2' ): string {
		return get_post_comments_feed_link( $post_id, $feed );
	}

}