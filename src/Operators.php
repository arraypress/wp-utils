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

namespace ArrayPress\Utils;

/**
 * Check if the class `Operators` is defined, and if not, define it.
 */
if ( ! class_exists( 'Operators' ) ) :

	/**
	 * Operators Utility Class
	 */
	class Operators {

		/**
		 * Returns array of generic boolean operators.
		 *
		 * @return array
		 */
		public static function get_boolean(): array {
			return [
				'==' => esc_html__( 'Equal to', 'arraypress' ),
				'!=' => esc_html__( 'Not equal to', 'arraypress' ),
			];
		}

		/**
		 * Returns array of generic numeric operators.
		 *
		 * @return array
		 */
		public static function get_numeric(): array {
			return [
				'>=' => esc_html__( 'Greater than or equal to', 'arraypress' ),
				'<=' => esc_html__( 'Less than or equal to', 'arraypress' ),
				'>'  => esc_html__( 'Greater than', 'arraypress' ),
				'<'  => esc_html__( 'Less than', 'arraypress' ),
				'==' => esc_html__( 'Equal to', 'arraypress' ),
				'!=' => esc_html__( 'Not equal to', 'arraypress' ),
			];
		}

		/**
		 * Returns array of generic string operators.
		 *
		 * @return array
		 */
		public static function get_string(): array {
			return [
				'equal_to'     => esc_html__( 'Equal to', 'arraypress' ),
				'not_equal_to' => esc_html__( 'Not equal to', 'arraypress' ),
				'contains'     => esc_html__( 'Contains', 'arraypress' ),
				'not_contains' => esc_html__( 'Not Contains', 'arraypress' ),
				'starts_with'  => esc_html__( 'Starts With', 'arraypress' ),
				'ends_with'    => esc_html__( 'Ends With', 'arraypress' ),
			];
		}

		/**
		 * Returns array of generic array operators.
		 *
		 * @return array
		 */
		public static function get_array(): array {
			return [
				'contains'     => esc_html__( 'Contains', 'arraypress' ),
				'not_contains' => esc_html__( 'Not Contains', 'arraypress' ),
			];
		}

		/**
		 * Returns array of generic multi value array operators.
		 *
		 * @return array
		 */
		public static function get_array_multi(): array {
			return [
				'contains'     => esc_html__( 'Contains Any', 'arraypress' ),
				'contains_all' => esc_html__( 'Contains All', 'arraypress' ),
				'not_contains' => esc_html__( 'Contains None', 'arraypress' ),
			];
		}

		/**
		 * Convert a human-readable comparison operator to a symbol
		 *
		 * @param string $operator The human-readable comparison operator.
		 *
		 * @return string|null The symbol comparison operator, or null if the operator is not recognized.
		 */
		public static function operator_to_symbol( string $operator ): ?string {
			switch ( strtolower( $operator ) ) {
				case 'more_than':
					return '>';
				case 'less_than':
					return '<';
				case 'at_least':
					return '>=';
				case 'at_most':
					return '<=';
				case 'equal_to':
					return '==';
				case 'not_equal_to':
					return '!=';
				default:
					return null;
			}
		}

	}
endif;