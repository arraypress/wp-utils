<?php
/**
 * Array Helper Utilities
 *
 * This class provides utility functions for working with arrays in PHP. It includes
 * methods for sorting, shuffling, selecting random elements, checking conditions across
 * elements, managing keys, and converting arrays into other formats such as JSON, XML,
 * or delimited strings. Additionally, it offers advanced operations for flattening, filtering,
 * normalizing, and recursive array manipulations.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

if ( ! function_exists( 'na' ) ) {
	/**
	 * Return 'N/A' if value is empty.
	 *
	 * @param mixed $value Optional. Value to check.
	 *
	 * @return string The value or 'N/A' if empty.
	 */
	function na( $value = null ): string {
		if ( $value === null || $value === '' || $value === false ) {
			return 'N/A';
		}

		return (string) $value;
	}
}

if ( ! function_exists( 'na_e' ) ) {
	/**
	 * Echo 'N/A' if value is empty.
	 *
	 * @param mixed $value Optional. Value to check.
	 *
	 * @return void
	 */
	function na_e( $value = null ): void {
		echo na( $value );
	}
}

if ( ! function_exists( 'zero' ) ) {
	/**
	 * Return '0' if value is empty, useful for numeric displays.
	 *
	 * @param mixed $value  Optional. Value to check.
	 * @param bool  $format Whether to format the number. Default true.
	 *
	 * @return string The value or '0' if empty.
	 */
	function zero( $value = null, bool $format = true ): string {
		if ( $value === null || $value === '' || $value === false ) {
			return '0';
		}

		return $format ? number_format_i18n( $value ) : (string) $value;
	}
}

if ( ! function_exists( 'zero_e' ) ) {
	/**
	 * Echo '0' if value is empty, useful for numeric displays.
	 *
	 * @param mixed $value  Optional. Value to check.
	 * @param bool  $format Whether to format the number. Default true.
	 *
	 * @return void
	 */
	function zero_e( $value = null, bool $format = true ): void {
		echo zero( $value, $format );
	}
}

if ( ! function_exists( 'none' ) ) {
	/**
	 * Return 'None' if value is empty (translatable).
	 *
	 * @param mixed $value Optional. Value to check.
	 *
	 * @return string The value or translated 'None' if empty.
	 */
	function none( $value = null ): string {
		if ( $value === null || $value === '' || $value === false ) {
			return __( 'None', 'arraypress' );
		}

		return (string) $value;
	}
}

if ( ! function_exists( 'bool_yn' ) ) {
	/**
	 * Convert boolean to Yes/No (translatable).
	 *
	 * @param bool|null $value Value to check.
	 *
	 * @return string Translated 'Yes' or 'No'.
	 */
	function bool_yn( ?bool $value ): string {
		return $value ? __( 'Yes', 'arraypress' ) : __( 'No', 'arraypress' );
	}
}