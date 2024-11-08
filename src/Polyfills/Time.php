<?php
/**
 * MDash Polyfills
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

if ( ! function_exists( 'time_ago' ) ) {
	/**
	 * Convert timestamp to "time ago" format.
	 *
	 * @param string|int $timestamp Timestamp or date string.
	 *
	 * @return string Human-readable time difference.
	 */
	function time_ago( $timestamp ): string {
		if ( empty( $timestamp ) ) {
			return '&mdash;';
		}

		if ( ! is_numeric( $timestamp ) ) {
			$timestamp = strtotime( $timestamp );
		}

		return sprintf(
			__( '%s ago', 'arraypress' ),
			human_time_diff( $timestamp, current_time( 'timestamp' ) )
		);
	}
}

if ( ! function_exists( 'time_ago_e' ) ) {
	/**
	 * Echo timestamp in "time ago" format.
	 *
	 * @param string|int $timestamp Timestamp or date string.
	 *
	 * @return void
	 */
	function time_ago_e( $timestamp ): void {
		echo time_ago( $timestamp );
	}
}