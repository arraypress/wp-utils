<?php
/**
 * User Comments Trait
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

use WP_User;

trait Comments {

	/**
	 * Required trait method for getting user data.
	 *
	 * @param int  $user_id       Optional. User ID. Default is 0.
	 * @param bool $allow_current Optional. Whether to allow fallback to current user. Default true.
	 *
	 * @return WP_User|null
	 */
	abstract protected static function get( int $user_id = 0, bool $allow_current = true ): ?WP_User;

	/**
	 * Get comments for a user with optional arguments.
	 *
	 * This method provides a flexible way to retrieve user comments with custom filtering,
	 * sorting, and pagination options. It merges provided arguments with default values
	 * ensuring consistent behavior while allowing for customization.
	 *
	 * @param int   $user_id Optional. User ID. Default is the current user.
	 * @param array $args    Optional. Arguments to pass to get_comments(). Default empty array.
	 *                       See WP_Comment_Query::__construct() for supported arguments.
	 *                       Some common arguments include:
	 *                       - status     (string|array) Comment status. Default 'approve'.
	 *                       - order      (string) Sort order. Default 'DESC'.
	 *                       - orderby    (string) Sort column. Default 'comment_date_gmt'.
	 *                       - number     (int) Number of comments to retrieve. Default null (all).
	 *                       - offset     (int) Number of comments to offset. Default 0.
	 *                       - type       (string|array) Comment type. Default 'comment'.
	 *                       - parent     (int) Parent comment ID. Default empty.
	 *                       - search     (string) Search term(s). Default empty.
	 *                       - count      (bool) Return count instead of results. Default false.
	 *
	 * @return mixed Array of comment objects or comment count (int) if 'count' argument is true.
	 */
	public static function get_comments( int $user_id = 0, array $args = [] ) {
		$user = static::get( $user_id );
		if ( ! $user ) {
			return [];
		}

		// Default arguments
		$defaults = [
			'user_id' => $user->ID,
			'status'  => 'approve',
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
			'type'    => 'comment'
		];

		// Merge user-provided arguments with defaults
		$args = array_merge( $defaults, $args );

		// Get comments
		$comments = get_comments( $args );

		// If count was requested, return the integer
		if ( ! empty( $args['count'] ) ) {
			return (int) $comments;
		}

		// Return empty array if no comments found
		return is_array( $comments ) ? $comments : [];
	}

	/**
	 * Get the number of comments made by a user.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param string $status  Optional. Comment status. Default 'approve'.
	 *
	 * @return int Number of comments.
	 */
	public static function count_comments( int $user_id = 0, string $status = 'approve' ): int {
		$user = static::get( $user_id );
		if ( ! $user ) {
			return 0;
		}

		$args = [
			'user_id' => $user->ID,
			'status'  => $status,
			'count'   => true,
		];

		return get_comments( $args );
	}

	/**
	 * Get user's recent comments.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param int    $limit   Optional. Number of comments to retrieve. Default 10.
	 * @param string $status  Optional. Comment status. Default 'approve'.
	 *
	 * @return array Array of comment objects.
	 */
	public static function get_recent_comments( int $user_id = 0, int $limit = 10, string $status = 'approve' ): array {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return [];
		}

		$args = [
			'user_id' => $user->ID,
			'status'  => $status,
			'number'  => $limit,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
		];

		return get_comments( $args );
	}

	/**
	 * Get the first comment date for a user.
	 *
	 * @param int    $user_id Optional. User ID. Default is the current user.
	 * @param string $format  Optional. Date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|null Date of first comment or null if no comments.
	 */
	public static function get_first_comment_date( int $user_id = 0, string $format = 'Y-m-d H:i:s' ): ?string {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$args = [
			'user_id' => $user->ID,
			'number'  => 1,
			'orderby' => 'comment_date_gmt',
			'order'   => 'ASC',
		];

		$comments = get_comments( $args );
		if ( empty( $comments ) ) {
			return null;
		}

		return mysql2date( $format, $comments[0]->comment_date_gmt );
	}

	/**
	 * Get user's comment frequency (average comments per day).
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return float|null Average comments per day or null if no comments.
	 */
	public static function get_comment_frequency( int $user_id = 0 ): ?float {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$first_comment_date = self::get_first_comment_date( $user->ID, 'U' );
		if ( ! $first_comment_date ) {
			return null;
		}

		$days_since_first_comment = ( time() - (int) $first_comment_date ) / DAY_IN_SECONDS;
		if ( $days_since_first_comment < 1 ) {
			return (float) self::count_comments( $user->ID );
		}

		return round( self::count_comments( $user->ID ) / $days_since_first_comment, 2 );
	}

	/**
	 * Check if a user has moderated comments.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return bool True if user has moderated comments, false otherwise.
	 */
	public static function has_moderated_comments( int $user_id = 0 ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$args = [
			'user_id' => $user->ID,
			'status'  => 'hold',
			'count'   => true,
		];

		return get_comments( $args ) > 0;
	}

	/**
	 * Get the spam ratio for user's comments.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return float|null Percentage of comments that are spam (0-100), or null if no comments.
	 */
	public static function get_comment_spam_ratio( int $user_id = 0 ): ?float {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return null;
		}

		$total_comments = self::count_comments( $user->ID, 'all' );
		if ( $total_comments === 0 ) {
			return null;
		}

		$spam_comments = self::count_spam_comments( $user->ID );

		return round( ( $spam_comments / $total_comments ) * 100, 2 );
	}

	/**
	 * Check if user has any spam comments.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return bool True if user has spam comments, false otherwise.
	 */
	public static function has_spam_comments( int $user_id = 0 ): bool {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return false;
		}

		$args = [
			'user_id' => $user->ID,
			'status'  => 'spam',
			'count'   => true,
		];

		return get_comments( $args ) > 0;
	}

	/**
	 * Get the count of spam comments for a user.
	 *
	 * @param int $user_id Optional. User ID. Default is the current user.
	 *
	 * @return int Number of spam comments.
	 */
	public static function count_spam_comments( int $user_id = 0 ): int {
		$user = self::get( $user_id );
		if ( ! $user ) {
			return 0;
		}

		$args = [
			'user_id' => $user->ID,
			'status'  => 'spam',
			'count'   => true,
		];

		return get_comments( $args );
	}

}