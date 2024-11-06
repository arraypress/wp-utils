<?php
/**
 * Comment Dates Trait
 *
 * Provides functionality for working with WordPress comment dates and times.
 * Includes methods for date formatting, age calculations, time differences,
 * and temporal comparisons between comments and their associated posts.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Comment;

trait Dates {

	/**
	 * Get comment date.
	 *
	 * @param int    $comment_id The comment ID.
	 * @param string $format     Optional. PHP date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|false Formatted date or false if comment not found.
	 */
	public static function get_date( int $comment_id, string $format = 'Y-m-d H:i:s' ) {
		$comment = get_comment( $comment_id );

		return $comment ? mysql2date( $format, $comment->comment_date ) : false;
	}

	/**
	 * Get comment date in GMT.
	 *
	 * @param int    $comment_id The comment ID.
	 * @param string $format     Optional. PHP date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|false Formatted GMT date or false if comment not found.
	 */
	public static function get_date_gmt( int $comment_id, string $format = 'Y-m-d H:i:s' ) {
		$comment = get_comment( $comment_id );

		return $comment ? mysql2date( $format, $comment->comment_date_gmt ) : false;
	}

	/**
	 * Get comment age in days.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return int|false Number of days since comment was made, or false if not found.
	 */
	public static function get_age( int $comment_id ) {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return false;
		}

		$comment_time = strtotime( $comment->comment_date_gmt );
		$current_time = time();

		return floor( ( $current_time - $comment_time ) / DAY_IN_SECONDS );
	}

	/**
	 * Get human-readable time difference.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return string|false Human-readable time difference or false if comment not found.
	 */
	public static function get_time_diff( int $comment_id ) {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return false;
		}

		$comment_time = strtotime( $comment->comment_date_gmt );

		return human_time_diff( $comment_time, time() );
	}

	/**
	 * Check if comment was made within time period.
	 *
	 * @param int    $comment_id The comment ID.
	 * @param string $period     Time period to check (e.g., '24 hours', '7 days', '1 month').
	 *
	 * @return bool|null True if within period, false if not, null if comment not found.
	 */
	public static function is_within_time_period( int $comment_id, string $period ): ?bool {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return null;
		}

		$comment_time = strtotime( $comment->comment_date_gmt );
		$period_time  = strtotime( '-' . $period );

		return $comment_time >= $period_time;
	}

	/**
	 * Check if comment was modified.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return bool|null True if modified, false if not, null if comment not found.
	 */
	public static function is_modified( int $comment_id ): ?bool {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return null;
		}

		return strtotime( $comment->comment_date_gmt ) !== strtotime( $comment->comment_date_gmt );
	}

	/**
	 * Get time between comment and post publication.
	 *
	 * @param int    $comment_id The comment ID.
	 * @param string $unit       Optional. Time unit ('seconds', 'minutes', 'hours', 'days'). Default 'days'.
	 *
	 * @return int|null Time difference in specified unit, null if error.
	 */
	public static function get_time_since_post( int $comment_id, string $unit = 'days' ): ?int {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return null;
		}

		$post = get_post( $comment->comment_post_ID );
		if ( ! $post ) {
			return null;
		}

		$comment_time = strtotime( $comment->comment_date_gmt );
		$post_time    = strtotime( $post->post_date_gmt );
		$diff         = $comment_time - $post_time;

		switch ( $unit ) {
			case 'seconds':
				return $diff;
			case 'minutes':
				return (int) floor( $diff / MINUTE_IN_SECONDS );
			case 'hours':
				return (int) floor( $diff / HOUR_IN_SECONDS );
			case 'days':
			default:
				return (int) floor( $diff / DAY_IN_SECONDS );
		}
	}

}