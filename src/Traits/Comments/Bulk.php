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

trait Bulk {

	/**
	 * Bulk delete comments.
	 *
	 * @param array $comment_ids  Array of comment IDs to delete.
	 * @param bool  $force_delete Whether to bypass trash. Default false.
	 *
	 * @return array Array of results with comment IDs as keys and boolean success as values.
	 */
	public static function delete( array $comment_ids, bool $force_delete = false ): array {
		$results = [];

		foreach ( $comment_ids as $comment_id ) {
			$results[ $comment_id ] = wp_delete_comment( $comment_id, $force_delete );
		}

		return $results;
	}

	/**
	 * Bulk update comment status.
	 *
	 * @param array  $comment_ids Array of comment IDs to update.
	 * @param string $status      New status to set.
	 *
	 * @return array Array of results with comment IDs as keys and boolean success as values.
	 */
	public static function update_status( array $comment_ids, string $status ): array {
		$results = [];

		foreach ( $comment_ids as $comment_id ) {
			$results[ $comment_id ] = wp_set_comment_status( $comment_id, $status );
		}

		return $results;
	}

	/**
	 * Bulk mark comments as spam.
	 *
	 * @param array $comment_ids Array of comment IDs to mark as spam.
	 *
	 * @return array Array of results with comment IDs as keys and boolean success as values.
	 */
	public static function mark_as_spam( array $comment_ids ): array {
		return self::update_status( $comment_ids, 'spam' );
	}

	/**
	 * Bulk mark comments as not spam.
	 *
	 * @param array $comment_ids Array of comment IDs to mark as not spam.
	 *
	 * @return array Array of results with comment IDs as keys and boolean success as values.
	 */
	public static function mark_as_not_spam( array $comment_ids ): array {
		return self::update_status( $comment_ids, 'approve' );
	}

	/**
	 * Bulk trash comments.
	 *
	 * @param array $comment_ids Array of comment IDs to trash.
	 *
	 * @return array Array of results with comment IDs as keys and boolean success as values.
	 */
	public static function trash( array $comment_ids ): array {
		return self::update_status( $comment_ids, 'trash' );
	}

	/**
	 * Bulk untrash comments.
	 *
	 * @param array $comment_ids Array of comment IDs to untrash.
	 *
	 * @return array Array of results with comment IDs as keys and boolean success as values.
	 */
	public static function untrash( array $comment_ids ): array {
		return self::update_status( $comment_ids, 'approve' );
	}

}