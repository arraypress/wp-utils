<?php
/**
 * Post Dates Trait
 *
 * Provides functionality for working with post dates, including publication dates,
 * modification dates, scheduling operations, and temporal comparisons.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

trait Dates {

	/**
	 * Get post date.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $format  Optional. PHP date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|false Formatted date or false if post not found.
	 */
	public static function get_date( int $post_id, string $format = 'Y-m-d H:i:s' ) {
		$post = get_post( $post_id );

		return $post ? mysql2date( $format, $post->post_date ) : false;
	}

	/**
	 * Get post date in GMT.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $format  Optional. PHP date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|false Formatted GMT date or false if post not found.
	 */
	public static function get_date_gmt( int $post_id, string $format = 'Y-m-d H:i:s' ) {
		$post = get_post( $post_id );

		return $post ? mysql2date( $format, $post->post_date_gmt ) : false;
	}

	/**
	 * Get modified date.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $format  Optional. PHP date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|false Formatted modified date or false if post not found.
	 */
	public static function get_modified_date( int $post_id, string $format = 'Y-m-d H:i:s' ) {
		$post = get_post( $post_id );

		return $post ? mysql2date( $format, $post->post_modified ) : false;
	}

	/**
	 * Get modified date in GMT.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $format  Optional. PHP date format. Default 'Y-m-d H:i:s'.
	 *
	 * @return string|false Formatted GMT modified date or false if post not found.
	 */
	public static function get_modified_date_gmt( int $post_id, string $format = 'Y-m-d H:i:s' ) {
		$post = get_post( $post_id );

		return $post ? mysql2date( $format, $post->post_modified_gmt ) : false;
	}

	/**
	 * Get post age in days.
	 *
	 * @param int  $post_id           The post ID.
	 * @param bool $use_modified_date Optional. Whether to use the modified date instead of published date. Default
	 *                                false.
	 *
	 * @return int|false Number of days since post was published/modified, or false if not found.
	 */
	public static function get_age( int $post_id, bool $use_modified_date = false ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		$post_time = strtotime( $use_modified_date ? $post->post_modified_gmt : $post->post_date_gmt );

		return (int) floor( ( time() - $post_time ) / DAY_IN_SECONDS );
	}

	/**
	 * Get human-readable time difference.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $from    Optional. Which date to use ('modified' or 'published'). Default 'published'.
	 *
	 * @return string|false Human-readable time difference or false if post not found.
	 */
	public static function get_time_diff( int $post_id, string $from = 'published' ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		$post_time = strtotime( $from === 'modified' ? $post->post_modified_gmt : $post->post_date_gmt );

		return human_time_diff( $post_time, time() );
	}

	/**
	 * Check if post was made within time period.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $period  Time period to check (e.g., '24 hours', '7 days', '1 month').
	 * @param string $from    Optional. Which date to check from ('modified' or 'published'). Default 'published'.
	 *
	 * @return bool|null True if within period, false if not, null if post not found.
	 */
	public static function is_within_time_period( int $post_id, string $period, string $from = 'published' ): ?bool {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}

		$post_time   = strtotime( $from === 'modified' ? $post->post_modified_gmt : $post->post_date_gmt );
		$period_time = strtotime( '-' . $period );

		return $post_time >= $period_time;
	}

	/**
	 * Get time between two posts.
	 *
	 * @param int    $post_id    The first post ID.
	 * @param int    $compare_id The second post ID to compare with.
	 * @param string $unit       Optional. Time unit ('seconds', 'minutes', 'hours', 'days'). Default 'days'.
	 * @param string $from       Optional. Which dates to compare ('modified' or 'published'). Default 'published'.
	 *
	 * @return int|null Time difference in specified unit, null if error.
	 */
	public static function get_time_between_posts( int $post_id, int $compare_id, string $unit = 'days', string $from = 'published' ): ?int {
		$post1 = get_post( $post_id );
		$post2 = get_post( $compare_id );

		if ( ! $post1 || ! $post2 ) {
			return null;
		}

		$time1 = strtotime( $from === 'modified' ? $post1->post_modified_gmt : $post1->post_date_gmt );
		$time2 = strtotime( $from === 'modified' ? $post2->post_modified_gmt : $post2->post_date_gmt );
		$diff  = abs( $time1 - $time2 );

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

	/**
	 * Update post date.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $date    The new date in MySQL format (YYYY-MM-DD HH:MM:SS).
	 *
	 * @return bool True if the date was updated successfully, false otherwise.
	 */
	public static function update_date( int $post_id, string $date ): bool {
		$post_data = [
			'ID'            => $post_id,
			'post_date'     => $date,
			'post_date_gmt' => get_gmt_from_date( $date )
		];

		$result = wp_update_post( $post_data );

		return $result !== 0 && ! is_wp_error( $result );
	}

	/**
	 * Reschedule a post.
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $new_date The new date in MySQL format (YYYY-MM-DD HH:MM:SS).
	 *
	 * @return bool True if the post was rescheduled successfully, false otherwise.
	 */
	public static function reschedule( int $post_id, string $new_date ): bool {
		$post_data = [
			'ID'            => $post_id,
			'post_date'     => $new_date,
			'post_date_gmt' => get_gmt_from_date( $new_date ),
			'post_status'   => 'future',
		];

		$result = wp_update_post( $post_data );

		return $result !== 0 && ! is_wp_error( $result );
	}

}