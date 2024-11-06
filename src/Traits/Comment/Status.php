<?php
/**
 * Comment Status Trait
 *
 * Provides functionality for working with comment statuses
 * and related state operations.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Comment;

trait Status {

	/**
	 * Check if comment is approved.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True if comment is approved, false otherwise.
	 */
	public static function is_approved( int $comment_id ): bool {
		$comment = get_comment( $comment_id );

		return $comment && $comment->comment_approved === '1';
	}

	/**
	 * Check if comment is spam.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True if comment is spam, false otherwise.
	 */
	public static function is_spam( int $comment_id ): bool {
		$comment = get_comment( $comment_id );

		return $comment && $comment->comment_approved === 'spam';
	}

	/**
	 * Check if comment is in trash.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True if comment is in trash, false otherwise.
	 */
	public static function is_trash( int $comment_id ): bool {
		$comment = get_comment( $comment_id );

		return $comment && $comment->comment_approved === 'trash';
	}

	/**
	 * Check if comment is pending moderation.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True if comment is pending, false otherwise.
	 */
	public static function is_pending( int $comment_id ): bool {
		$comment = get_comment( $comment_id );

		return $comment && $comment->comment_approved === '0';
	}

	/**
	 * Get comment status.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return string|false The comment status or false if comment not found.
	 */
	public static function get_status( int $comment_id ) {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return false;
		}

		switch ( $comment->comment_approved ) {
			case '1':
				return 'approved';
			case '0':
				return 'pending';
			case 'spam':
				return 'spam';
			case 'trash':
				return 'trash';
			default:
				return 'unknown';
		}
	}

	/**
	 * Update comment status.
	 *
	 * @param int    $comment_id The comment ID.
	 * @param string $status     The new status to set.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function update_status( int $comment_id, string $status ): bool {
		return wp_set_comment_status( $comment_id, $status );
	}

	/**
	 * Mark comment as spam.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function mark_as_spam( int $comment_id ): bool {
		return self::update_status( $comment_id, 'spam' );
	}

	/**
	 * Mark comment as not spam.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function mark_as_not_spam( int $comment_id ): bool {
		return self::update_status( $comment_id, 'approve' );
	}

	/**
	 * Approve comment.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function approve( int $comment_id ): bool {
		return self::update_status( $comment_id, 'approve' );
	}

	/**
	 * Unapprove comment.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function unapprove( int $comment_id ): bool {
		return self::update_status( $comment_id, 'hold' );
	}

	/**
	 * Trash comment.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function trash( int $comment_id ): bool {
		return self::update_status( $comment_id, 'trash' );
	}

	/**
	 * Untrash comment.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function untrash( int $comment_id ): bool {
		return self::update_status( $comment_id, 'approve' );
	}

}

