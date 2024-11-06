<?php
/**
 * Operators Utility Class
 *
 * This class provides a set of utility methods for retrieving commonly used
 * operators in various contexts such as boolean logic, numeric comparisons,
 * string operations, and array manipulations. It's designed to be used in
 * e-commerce, affiliation, and related applications where these operators
 * might be needed for filtering, searching, or conditional logic.
 *
 * @package       ArrayPress\Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\I18n;

class Operators {

	/**
	 * Returns array of generic boolean operators.
	 *
	 * @param string|null $context The context in which the operators are being used.
	 *
	 * @return array
	 */
	public static function get_boolean( ?string $context = null ): array {
		$operators = [
			'==' => esc_html__( 'Equal to', 'arraypress' ),
			'!=' => esc_html__( 'Not equal to', 'arraypress' ),
		];

		/**
		 * Filters the array of boolean operators.
		 *
		 * @param array       $operators The array of boolean operators.
		 * @param string|null $context   The context in which the operators are being used.
		 *
		 * @return array                 The filtered array of boolean operators.
		 */
		return apply_filters( 'arraypress_boolean_operators', $operators, $context );
	}

	/**
	 * Returns array of generic numeric operators.
	 *
	 * @param string|null $context The context in which the operators are being used.
	 *
	 * @return array
	 */
	public static function get_numeric( ?string $context = null ): array {
		$operators = [
			'>=' => esc_html__( 'Greater than or equal to', 'arraypress' ),
			'<=' => esc_html__( 'Less than or equal to', 'arraypress' ),
			'>'  => esc_html__( 'Greater than', 'arraypress' ),
			'<'  => esc_html__( 'Less than', 'arraypress' ),
			'==' => esc_html__( 'Equal to', 'arraypress' ),
			'!=' => esc_html__( 'Not equal to', 'arraypress' ),
		];

		/**
		 * Filters the array of numeric operators.
		 *
		 * @param array       $operators The array of numeric operators.
		 * @param string|null $context   The context in which the operators are being used.
		 *
		 * @return array                 The filtered array of numeric operators.
		 */
		return apply_filters( 'arraypress_numeric_operators', $operators, $context );
	}

	/**
	 * Returns array of generic string operators.
	 *
	 * @param string|null $context The context in which the operators are being used.
	 *
	 * @return array
	 */
	public static function get_string( ?string $context = null ): array {
		$operators = [
			'equal_to'     => esc_html__( 'Equal to', 'arraypress' ),
			'not_equal_to' => esc_html__( 'Not equal to', 'arraypress' ),
			'contains'     => esc_html__( 'Contains', 'arraypress' ),
			'not_contains' => esc_html__( 'Not Contains', 'arraypress' ),
			'starts_with'  => esc_html__( 'Starts With', 'arraypress' ),
			'ends_with'    => esc_html__( 'Ends With', 'arraypress' ),
		];

		/**
		 * Filters the array of string operators.
		 *
		 * @param array       $operators The array of string operators.
		 * @param string|null $context   The context in which the operators are being used.
		 *
		 * @return array                 The filtered array of string operators.
		 */
		return apply_filters( 'arraypress_string_operators', $operators, $context );
	}

	/**
	 * Returns array of generic array operators.
	 *
	 * @param string|null $context The context in which the operators are being used.
	 *
	 * @return array
	 */
	public static function get_array( ?string $context = null ): array {
		$operators = [
			'contains'     => esc_html__( 'Contains', 'arraypress' ),
			'not_contains' => esc_html__( 'Not Contains', 'arraypress' ),
		];

		/**
		 * Filters the array of array operators.
		 *
		 * @param array       $operators The array of array operators.
		 * @param string|null $context   The context in which the operators are being used.
		 *
		 * @return array                 The filtered array of array operators.
		 */
		return apply_filters( 'arraypress_array_operators', $operators, $context );
	}

	/**
	 * Returns array of generic multi value array operators.
	 *
	 * @param string|null $context The context in which the operators are being used.
	 *
	 * @return array
	 */
	public static function get_array_multi( ?string $context = null ): array {
		$operators = [
			'contains'     => esc_html__( 'Contains Any', 'arraypress' ),
			'contains_all' => esc_html__( 'Contains All', 'arraypress' ),
			'not_contains' => esc_html__( 'Contains None', 'arraypress' ),
		];

		/**
		 * Filters the array of multi-value array operators.
		 *
		 * @param array       $operators The array of multi-value array operators.
		 * @param string|null $context   The context in which the operators are being used.
		 *
		 * @return array                 The filtered array of multi-value array operators.
		 */
		return apply_filters( 'arraypress_array_multi_operators', $operators, $context );
	}

}