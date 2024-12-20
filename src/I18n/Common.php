<?php
/**
 * Common i18n Utility Class
 *
 * @package       ArrayPress\Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\I18n;

class Common {

	/**
	 * Returns array of login status options.
	 *
	 * @param string|null $context The context in which the statuses are being used.
	 *
	 * @return array
	 */
	public static function get_login_statuses( ?string $context = null ): array {
		$statuses = [
			[
				'value' => 'logged_in',
				'label' => esc_html__( 'Logged In', 'arraypress' ),
			],
			[
				'value' => 'logged_out',
				'label' => esc_html__( 'Logged Out', 'arraypress' ),
			],
		];

		/**
		 * Filters the array of login status options.
		 *
		 * @param array       $statuses The array of login status options.
		 * @param string|null $context  The context in which the statuses are being used.
		 *
		 * @return array               The filtered array of login status options.
		 */
		return apply_filters( 'arraypress_login_statuses', $statuses, $context );
	}

	/**
	 * Returns array of adjustment type options.
	 *
	 * @param string|null $context The context in which the adjustment types are being used.
	 *
	 * @return array
	 */
	public static function get_adjustment_types( ?string $context = null ): array {
		$types = [
			[
				'value' => 'percentage',
				'label' => esc_html__( 'Percentage', 'arraypress' ),
			],
			[
				'value' => 'flat',
				'label' => esc_html__( 'Flat', 'arraypress' ),
			],
		];

		/**
		 * Filters the array of adjustment type options.
		 *
		 * @param array       $types   The array of adjustment type options.
		 * @param string|null $context The context in which the adjustment types are being used.
		 *
		 * @return array The filtered array of adjustment type options.
		 */
		return apply_filters( 'arraypress_adjustment_types', $types, $context );
	}

}