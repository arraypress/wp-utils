<?php
/**
 * Posts Bulk Trait
 *
 * Provides functionality for performing bulk operations on multiple WordPress posts.
 * Includes methods for batch updates, status changes, deletions, and other
 * operations that affect multiple posts simultaneously.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Posts;

use ArrayPress\Utils\Common\Sanitize;

/**
 * Bulk Trait
 *
 * Handles bulk operations for multiple posts including status updates,
 * meta operations, and post management functions.
 */
trait Bulk {

	/**
	 * Delete posts by IDs.
	 *
	 * Permanently deletes multiple posts by their IDs.
	 *
	 * @param int[] $post_ids Array of post IDs to delete.
	 *
	 * @return bool True if all posts were deleted successfully, false otherwise.
	 */
	public static function delete( array $post_ids ): bool {
		$post_ids = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			if ( ! wp_delete_post( $post_id, true ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Bulk change post status for multiple posts.
	 *
	 * @param array  $post_ids   Array of post IDs.
	 * @param string $new_status The new post status to set.
	 *
	 * @return bool True if all updates were successful, false otherwise.
	 */
	public static function change_status( array $post_ids, string $new_status ): bool {
		$post_ids = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$updated = wp_update_post( [
				'ID'          => $post_id,
				'post_status' => $new_status,
			] );

			if ( ! $updated || is_wp_error( $updated ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Bulk update post type for multiple posts.
	 *
	 * @param array  $post_ids    Array of post IDs.
	 * @param string $new_type    The new post type.
	 * @param bool   $clear_terms Optional. Whether to clear taxonomy terms. Default false.
	 *
	 * @return bool True if all updates were successful, false otherwise.
	 */
	public static function change_post_type( array $post_ids, string $new_type, bool $clear_terms = false ): bool {
		$post_ids = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) || ! post_type_exists( $new_type ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$post_data = [
				'ID'        => $post_id,
				'post_type' => $new_type,
			];

			$updated = wp_update_post( $post_data );

			if ( ! $updated || is_wp_error( $updated ) ) {
				$success = false;
				continue;
			}

			// Clear taxonomy terms if requested
			if ( $clear_terms ) {
				$taxonomies = get_object_taxonomies( $new_type );
				foreach ( $taxonomies as $taxonomy ) {
					wp_delete_object_term_relationships( $post_id, $taxonomy );
				}
			}
		}

		return $success;
	}

	/**
	 * Bulk update post author for multiple posts.
	 *
	 * @param array $post_ids      Array of post IDs.
	 * @param int   $new_author_id The new author's user ID.
	 *
	 * @return bool True if all updates were successful, false otherwise.
	 */
	public static function change_author( array $post_ids, int $new_author_id ): bool {
		$post_ids = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) || ! get_userdata( $new_author_id ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$updated = wp_update_post( [
				'ID'          => $post_id,
				'post_author' => $new_author_id,
			] );

			if ( ! $updated || is_wp_error( $updated ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Bulk update comment status for multiple posts.
	 *
	 * @param array  $post_ids       Array of post IDs.
	 * @param string $comment_status The new comment status ('open' or 'closed').
	 *
	 * @return bool True if all updates were successful, false otherwise.
	 */
	public static function change_comment_status( array $post_ids, string $comment_status ): bool {
		$post_ids = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) || ! in_array( $comment_status, [ 'open', 'closed' ], true ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$updated = wp_update_post( [
				'ID'             => $post_id,
				'comment_status' => $comment_status,
			] );

			if ( ! $updated || is_wp_error( $updated ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Bulk update ping status for multiple posts.
	 *
	 * @param array  $post_ids    Array of post IDs.
	 * @param string $ping_status The new ping status ('open' or 'closed').
	 *
	 * @return bool True if all updates were successful, false otherwise.
	 */
	public static function change_ping_status( array $post_ids, string $ping_status ): bool {
		$post_ids = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) || ! in_array( $ping_status, [ 'open', 'closed' ], true ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$updated = wp_update_post( [
				'ID'          => $post_id,
				'ping_status' => $ping_status,
			] );

			if ( ! $updated || is_wp_error( $updated ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Bulk trash posts.
	 *
	 * @param array $post_ids Array of post IDs.
	 *
	 * @return bool True if all posts were trashed successfully, false otherwise.
	 */
	public static function trash( array $post_ids ): bool {
		return self::change_status( $post_ids, 'trash' );
	}

	/**
	 * Bulk untrash posts.
	 *
	 * @param array $post_ids Array of post IDs.
	 *
	 * @return bool True if all posts were untrashed successfully, false otherwise.
	 */
	public static function untrash( array $post_ids ): bool {
		return self::change_status( $post_ids, 'publish' );
	}

	/**
	 * Bulk update menu order for multiple posts.
	 *
	 * @param array $post_ids    Array of post IDs.
	 * @param int   $start_order Starting order number.
	 *
	 * @return bool True if all updates were successful, false otherwise.
	 */
	public static function update_menu_order( array $post_ids, int $start_order = 0 ): bool {
		$post_ids = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) ) {
			return false;
		}

		$success = true;
		$order   = $start_order;

		foreach ( $post_ids as $post_id ) {
			$updated = wp_update_post( [
				'ID'         => $post_id,
				'menu_order' => $order ++,
			] );

			if ( ! $updated || is_wp_error( $updated ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Bulk update post dates.
	 *
	 * @param array  $post_ids Array of post IDs.
	 * @param string $date     The new date in MySQL format (YYYY-MM-DD HH:MM:SS).
	 *
	 * @return bool True if all updates were successful, false otherwise.
	 */
	public static function update_dates( array $post_ids, string $date ): bool {
		$post_ids = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) ) {
			return false;
		}

		$success = true;

		foreach ( $post_ids as $post_id ) {
			$updated = wp_update_post( [
				'ID'            => $post_id,
				'post_date'     => $date,
				'post_date_gmt' => get_gmt_from_date( $date ),
			] );

			if ( ! $updated || is_wp_error( $updated ) ) {
				$success = false;
			}
		}

		return $success;
	}

}