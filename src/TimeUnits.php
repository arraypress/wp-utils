<?php
/**
 * Time and Date Utilities for WordPress
 *
 * This file contains the TimeUnits class, which provides a set of utility functions
 * for working with time-related data in WordPress applications. It offers methods
 * for retrieving localized time units, date formats, and period labels, suitable
 * for use in plugin and theme development.
 *
 * @package     ArrayPress\Utils\i18n
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\i18n;

/**
 * Check if the class `TimeUnits` is defined, and if not, define it.
 */
if ( ! class_exists( 'TimeUnits' ) ) :

	/**
	 * Time Units Utilities
	 *
	 * Provides utility functions for managing time-related data in WordPress applications.
	 * This class offers methods for retrieving localized time units, date and time formats,
	 * days of the week, months of the year, quarters, and formatting time periods. It's designed
	 * to assist in internationalization efforts and provide consistent time-related data across
	 * WordPress themes and plugins.
	 */
	class TimeUnits {

		/**
		 * Get available time units.
		 *
		 * @param bool $include_times Optional. Whether to include time-based durations (hour, minute, second). Default false.
		 *
		 * @return array An array of available time units.
		 */
		public static function get_time_units( bool $include_times = false ): array {
			$durations = [
				[
					'value' => 'day',
					'label' => esc_html__( 'Days', 'arraypress' ),
				],
				[
					'value' => 'week',
					'label' => esc_html__( 'Weeks', 'arraypress' ),
				],
				[
					'value' => 'month',
					'label' => esc_html__( 'Months', 'arraypress' ),
				],
				[
					'value' => 'year',
					'label' => esc_html__( 'Years', 'arraypress' ),
				],
			];

			if ( $include_times ) {
				$durations = array_merge( $durations, [
					[
						'value' => 'hour',
						'label' => esc_html__( 'Hours', 'arraypress' ),
					],
					[
						'value' => 'minute',
						'label' => esc_html__( 'Minutes', 'arraypress' ),
					],
					[
						'value' => 'second',
						'label' => esc_html__( 'Seconds', 'arraypress' ),
					],
				] );
			}

			return $durations;
		}


		/**
		 * Get an array of days of the week with their values and labels.
		 *
		 * @return array An array of associative arrays containing 'value' and 'label' for each day of the week.
		 */
		public static function get_days_of_week(): array {
			return [
				[
					'value' => '1',
					'label' => esc_html__( 'Monday', 'arraypress' ),
				],
				[
					'value' => '2',
					'label' => esc_html__( 'Tuesday', 'arraypress' ),
				],
				[
					'value' => '3',
					'label' => esc_html__( 'Wednesday', 'arraypress' ),
				],
				[
					'value' => '4',
					'label' => esc_html__( 'Thursday', 'arraypress' ),
				],
				[
					'value' => '5',
					'label' => esc_html__( 'Friday', 'arraypress' ),
				],
				[
					'value' => '6',
					'label' => esc_html__( 'Saturday', 'arraypress' ),
				],
				[
					'value' => '7',
					'label' => esc_html__( 'Sunday', 'arraypress' ),
				],
			];
		}

		/**
		 * Get an array of months of the year with their values and labels.
		 *
		 * @return array An array of associative arrays containing 'value' and 'label' for each month of the year.
		 */
		public static function get_months_of_year(): array {
			return [
				[
					'value' => 'January',
					'label' => esc_html__( 'January', 'arraypress' ),
				],
				[
					'value' => 'February',
					'label' => esc_html__( 'February', 'arraypress' ),
				],
				[
					'value' => 'March',
					'label' => esc_html__( 'March', 'arraypress' ),
				],
				[
					'value' => 'April',
					'label' => esc_html__( 'April', 'arraypress' ),
				],
				[
					'value' => 'May',
					'label' => esc_html__( 'May', 'arraypress' ),
				],
				[
					'value' => 'June',
					'label' => esc_html__( 'June', 'arraypress' ),
				],
				[
					'value' => 'July',
					'label' => esc_html__( 'July', 'arraypress' ),
				],
				[
					'value' => 'August',
					'label' => esc_html__( 'August', 'arraypress' ),
				],
				[
					'value' => 'September',
					'label' => esc_html__( 'September', 'arraypress' ),
				],
				[
					'value' => 'October',
					'label' => esc_html__( 'October', 'arraypress' ),
				],
				[
					'value' => 'November',
					'label' => esc_html__( 'November', 'arraypress' ),
				],
				[
					'value' => 'December',
					'label' => esc_html__( 'December', 'arraypress' ),
				],
			];
		}


		/**
		 * Get an array of quarters of the year with their values and labels.
		 *
		 * @return array An array of associative arrays containing 'value' and 'label' for each quarter of the year.
		 */
		public static function get_quarters_of_year(): array {
			return [
				[
					'value' => 'Q1',
					'label' => esc_html__( 'Q1 (January - March)', 'arraypress' ),
				],
				[
					'value' => 'Q2',
					'label' => esc_html__( 'Q2 (April - June)', 'arraypress' ),
				],
				[
					'value' => 'Q3',
					'label' => esc_html__( 'Q3 (July - September)', 'arraypress' ),
				],
				[
					'value' => 'Q4',
					'label' => esc_html__( 'Q4 (October - December)', 'arraypress' ),
				],
			];
		}

		/**
		 * Get an array of date formats.
		 *
		 * @return array An array of date formats in label/value format.
		 */
		public static function get_date_formats(): array {
			return [
				[
					'value' => 'Y-m-d',
					'label' => esc_html__( 'YYYY-MM-DD', 'arraypress' ),
				],
				[
					'value' => 'd/m/Y',
					'label' => esc_html__( 'DD/MM/YYYY', 'arraypress' ),
				],
				[
					'value' => 'm/d/Y',
					'label' => esc_html__( 'MM/DD/YYYY', 'arraypress' ),
				],
				[
					'value' => 'd-m-Y',
					'label' => esc_html__( 'DD-MM-YYYY', 'arraypress' ),
				],
				[
					'value' => 'm-d-Y',
					'label' => esc_html__( 'MM-DD-YYYY', 'arraypress' ),
				],
			];
		}

		/**
		 * Get an array of time formats.
		 *
		 * @return array An array of time formats in label/value format.
		 */
		public static function get_time_formats(): array {
			return [
				[
					'value' => 'H:i',
					'label' => esc_html__( 'HH:MM (24-hour)', 'arraypress' ),
				],
				[
					'value' => 'h:i A',
					'label' => esc_html__( 'hh:MM AM/PM (12-hour)', 'arraypress' ),
				],
				[
					'value' => 'H:i:s',
					'label' => esc_html__( 'HH:MM:SS (24-hour)', 'arraypress' ),
				],
				[
					'value' => 'h:i:s A',
					'label' => esc_html__( 'hh:MM:SS AM/PM (12-hour)', 'arraypress' ),
				],
			];
		}

		/**
		 * Gets the localized label for a time period.
		 *
		 * @param string $period The period type (e.g., 'day', 'week', 'month', etc.).
		 * @param int    $count  The count of the periods. Default is 1.
		 *
		 * @return string The localized time period label.
		 */
		public static function get_period_label( string $period, int $count = 1 ): string {
			$period = strtolower( $period );

			$labels = [
				'day'       => _nx( 'day', 'days', $count, 'time period', 'arraypress' ),
				'week'      => _nx( 'week', 'weeks', $count, 'time period', 'arraypress' ),
				'month'     => _nx( 'month', 'months', $count, 'time period', 'arraypress' ),
				'quarter'   => _x( 'quarter', 'time period', 'arraypress' ),
				'semi-year' => _x( 'six months', 'time period', 'arraypress' ),
				'year'      => _nx( 'year', 'years', $count, 'time period', 'arraypress' ),
				'hour'      => _nx( 'hour', 'hours', $count, 'time period', 'arraypress' ),
				'minute'    => _nx( 'minute', 'minutes', $count, 'time period', 'arraypress' ),
				'second'    => _nx( 'second', 'seconds', $count, 'time period', 'arraypress' ),
			];

			return $labels[ $period ] ?? $period;
		}

		/**
		 * Formats a time period with its count.
		 *
		 * @param string $period The period type.
		 * @param int    $count  The count of the periods.
		 *
		 * @return string The formatted time period string.
		 */
		public static function format_period( string $period, int $count ): string {
			$label = self::get_period_label( $period, $count );

			return sprintf( _n( '%d %s', '%d %s', $count, 'arraypress' ), $count, $label );
		}

	}
endif;