<?php
/**
 * MDash Polyfills
 *
 * This file provides a collection of utility functions centered around the mdash HTML entity.
 * These functions offer consistent ways to handle empty or null values in display contexts by
 * returning or echoing an mdash character when appropriate. The functions include variants for
 * handling different data types such as strings, arrays, numbers, and dates.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

if ( ! function_exists( 'mdash' ) ) {
	/**
	 * Return an mdash if value is empty, otherwise return the value.
	 *
	 * @param mixed $value Optional. Value to check.
	 *
	 * @return string The value or mdash if empty.
	 */
	function mdash( $value = null ): string {
		if ( $value === null || $value === '' || $value === false ) {
			return '&mdash;';
		}

		return (string) $value;
	}
}

if ( ! function_exists( 'mdash_e' ) ) {
	/**
	 * Echo an mdash if value is empty, otherwise echo the value.
	 *
	 * @param mixed $value Optional. Value to check.
	 */
	function mdash_e( $value = null ): void {
		echo mdash( $value );
	}
}

if ( ! function_exists( 'mdash_array' ) ) {
	/**
	 * Check array value by key and return mdash if empty.
	 *
	 * @param array  $array Array to check.
	 * @param string $key   Key to look for.
	 *
	 * @return string The value or mdash if empty/not found.
	 */
	function mdash_array( array $array, string $key ): string {
		return mdash( $array[ $key ] ?? null );
	}
}

if ( ! function_exists( 'mdash_array_e' ) ) {
	/**
	 * Echo array value by key or mdash if empty.
	 *
	 * @param array  $array Array to check.
	 * @param string $key   Key to look for.
	 *
	 * @return void
	 */
	function mdash_array_e( array $array, string $key ): void {
		echo mdash_array( $array, $key );
	}
}

if ( ! function_exists( 'mdash_number' ) ) {
	/**
	 * Return an mdash if number is empty or zero, otherwise return the formatted number.
	 *
	 * @param int|float|null $number Optional. Number to check.
	 * @param bool           $format Whether to format the number. Default true.
	 *
	 * @return string The formatted number or mdash.
	 */
	function mdash_number( $number = null, bool $format = true ): string {
		if ( $number === null || $number === '' || $number === 0 ) {
			return '&mdash;';
		}

		return $format ? number_format_i18n( $number ) : (string) $number;
	}
}

if ( ! function_exists( 'mdash_number_e' ) ) {
	/**
	 * Echo a formatted number or mdash if empty/zero.
	 *
	 * @param int|float|null $number Optional. Number to check.
	 * @param bool           $format Whether to format the number. Default true.
	 *
	 * @return void
	 */
	function mdash_number_e( $number = null, bool $format = true ): void {
		echo mdash_number( $number, $format );
	}
}

if ( ! function_exists( 'mdash_date' ) ) {
	/**
	 * Display formatted date or mdash if empty.
	 *
	 * @param string|null $date   Date string.
	 * @param string      $format Date format. Default WordPress date format.
	 *
	 * @return string Formatted date or mdash.
	 */
	function mdash_date( ?string $date, string $format = '' ): string {
		if ( empty( $date ) ) {
			return '&mdash;';
		}

		if ( empty( $format ) ) {
			$format = get_option( 'date_format' );
		}

		return date_i18n( $format, strtotime( $date ) );
	}
}

if ( ! function_exists( 'mdash_date_e' ) ) {
	/**
	 * Echo formatted date or mdash if empty.
	 *
	 * @param string|null $date   Date string.
	 * @param string      $format Date format. Default WordPress date format.
	 *
	 * @return void
	 */
	function mdash_date_e( ?string $date, string $format = '' ): void {
		echo mdash_date( $date, $format );
	}
}

if ( ! function_exists( 'mdash_url' ) ) {
	/**
	 * Return mdash if URL is empty or invalid, otherwise return the URL.
	 *
	 * @param string|null $url URL to check.
	 * @return string The URL or mdash if empty/invalid.
	 */
	function mdash_url( ?string $url ): string {
		if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return '&mdash;';
		}
		return $url;
	}
}

if ( ! function_exists( 'mdash_url_e' ) ) {
	/**
	 * Echo mdash if URL is empty or invalid, otherwise echo the URL.
	 *
	 * @param string|null $url URL to check.
	 * @return void
	 */
	function mdash_url_e( ?string $url ): void {
		echo mdash_url( $url );
	}
}

if ( ! function_exists( 'mdash_list' ) ) {
	/**
	 * Return mdash if array is empty, otherwise return imploded list.
	 *
	 * @param array       $array    Array to implode.
	 * @param string      $glue     Glue string for implode. Default ', '.
	 * @param string|null $last_glue Optional last glue for natural language lists.
	 * @return string Imploded list or mdash.
	 */
	function mdash_list( array $array, string $glue = ', ', ?string $last_glue = null ): string {
		if ( empty( $array ) ) {
			return '&mdash;';
		}

		if ( $last_glue !== null ) {
			$last = array_pop( $array );
			if ( $array ) {
				return implode( $glue, $array ) . $last_glue . $last;
			}
			return $last;
		}

		return implode( $glue, $array );
	}
}

if ( ! function_exists( 'mdash_list_e' ) ) {
	/**
	 * Echo mdash if array is empty, otherwise echo imploded list.
	 *
	 * @param array       $array    Array to implode.
	 * @param string      $glue     Glue string for implode. Default ', '.
	 * @param string|null $last_glue Optional last glue for natural language lists.
	 * @return void
	 */
	function mdash_list_e( array $array, string $glue = ', ', ?string $last_glue = null ): void {
		echo mdash_list( $array, $glue, $last_glue );
	}
}