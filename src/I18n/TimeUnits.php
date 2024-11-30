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

namespace ArrayPress\Utils\I18n;

class TimeUnits {

	/**
	 * Get available time units.
	 *
	 * @param bool        $include_times Optional. Whether to include time-based durations (hour, minute, second).
	 *                                   Default false.
	 * @param string|null $context       Optional. The context in which the time units are being retrieved.
	 *
	 * @return array An array of available time units.
	 */
	public static function get_time_units( bool $include_times = false, ?string $context = null ): array {
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

		/**
		 * Filters the available time units.
		 *
		 * @param array       $durations     The array of time units.
		 * @param bool        $include_times Whether time-based durations are included.
		 * @param string|null $context       The context in which the time units are being retrieved.
		 */
		return apply_filters( 'arraypress_time_units', $durations, $include_times, $context );
	}

	/**
	 * Get the time range options.
	 *
	 * This method retrieves an array of time range options that can be used in various contexts
	 * throughout the application. Each range is represented as an array with 'label' and 'value' keys.
	 *
	 * @param string|null $context Optional. The context in which the time ranges are being retrieved.
	 *
	 * @return array Array of time range options with label and value pairs.
	 */
	public static function get_time_ranges( ?string $context = null ): array {
		$ranges = [
			[
				'label' => esc_html__( 'All Time', 'arraypress' ),
				'value' => 'all_time'
			],
			[
				'label' => esc_html__( 'Today', 'arraypress' ),
				'value' => 'today'
			],
			[
				'label' => esc_html__( 'Yesterday', 'arraypress' ),
				'value' => 'yesterday'
			],
			[
				'label' => esc_html__( 'This Week', 'arraypress' ),
				'value' => 'this_week'
			],
			[
				'label' => esc_html__( 'Last Week', 'arraypress' ),
				'value' => 'last_week'
			],
			[
				'label' => esc_html__( 'Last 30 Days', 'arraypress' ),
				'value' => 'last_30_days'
			],
			[
				'label' => esc_html__( 'This Month', 'arraypress' ),
				'value' => 'this_month'
			],
			[
				'label' => esc_html__( 'Last Month', 'arraypress' ),
				'value' => 'last_month'
			],
			[
				'label' => esc_html__( 'This Quarter', 'arraypress' ),
				'value' => 'this_quarter'
			],
			[
				'label' => esc_html__( 'Last Quarter', 'arraypress' ),
				'value' => 'last_quarter'
			],
			[
				'label' => esc_html__( 'This Year', 'arraypress' ),
				'value' => 'this_year'
			],
			[
				'label' => esc_html__( 'Last Year', 'arraypress' ),
				'value' => 'last_year'
			]
		];

		/**
		 * Filters the array of time range options.
		 *
		 * @param array       $ranges  The array of time range options.
		 * @param string|null $context The context in which the time ranges are being retrieved.
		 */
		return apply_filters( 'arraypress_time_ranges', $ranges, $context );
	}


	/**
	 * Get an array of days of the week with their values and labels.
	 *
	 * @param string|null $context Optional. The context in which the time units are being retrieved.
	 *
	 * @return array An array of associative arrays containing 'value' and 'label' for each day of the week.
	 */
	public static function get_days_of_week( ?string $context = null ): array {
		$days = [
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

		/**
		 * Filters the days of the week.
		 *
		 * @param array       $days    The array of days with their values and labels.
		 * @param string|null $context The context in which the days of the week are being retrieved.
		 */
		return apply_filters( 'arraypress_days_of_week', $days, $context );
	}

	/**
	 * Get an array of months of the year with their values and labels.
	 *
	 * @return array An array of associative arrays containing 'value' and 'label' for each month of the year.
	 */
	public static function get_months_of_year( ?string $context = null ): array {
		$months = [
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

		/**
		 * Filters the months of the year.
		 *
		 * @param array       $months  The array of months with their values and labels.
		 * @param string|null $context The context in which the months are being retrieved.
		 */
		return apply_filters( 'arraypress_months_of_year', $months, $context );
	}

	/**
	 * Get an array of weeks of the year with their values and labels.
	 *
	 * @param string|null $context Optional. The context in which the weeks are being retrieved.
	 *
	 * @return array An array of associative arrays containing 'value' and 'label' for each week of the year.
	 */
	public static function get_weeks_of_year( ?string $context = null ): array {
		$weeks = [];

		// Generate 52 weeks
		for ( $i = 1; $i <= 52; $i ++ ) {
			$weeks[] = [
				'value' => (string) $i,
				'label' => sprintf(
					esc_html__( 'Week %d', 'arraypress' ),
					$i
				)
			];
		}

		/**
		 * Filters the weeks of the year.
		 *
		 * @param array       $weeks   The array of weeks with their values and labels.
		 * @param string|null $context The context in which the weeks of the year are being retrieved.
		 */
		return apply_filters( 'arraypress_weeks_of_year', $weeks, $context );
	}

	/**
	 * Get an array of quarters of the year with their values and labels.
	 *
	 * @param string|null $context Optional. The context in which the quarters are being retrieved.
	 *
	 * @return array An array of associative arrays containing 'value' and 'label' for each quarter of the year.
	 */
	public static function get_quarters_of_year( ?string $context = null ): array {
		$quarters = [
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

		/**
		 * Filters the quarters of the year.
		 *
		 * @param array       $quarters The array of quarters with their values and labels.
		 * @param string|null $context  The context in which the quarters of the year are being retrieved.
		 */
		return apply_filters( 'arraypress_quarters_of_year', $quarters, $context );
	}

	/**
	 * Get an array of date formats.
	 *
	 * @param string|null $context Optional. The context in which the date formats are being retrieved.
	 *
	 * @return array An array of date formats in label/value format.
	 */
	public static function get_date_formats( ?string $context = null ): array {
		$formats = [
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

		/**
		 * Filters the date formats.
		 *
		 * @param array       $formats The default date formats.
		 * @param string|null $context The context in which the date formats are being retrieved.
		 */
		return apply_filters( 'arraypress_date_formats', $formats, $context );
	}

	/**
	 * Get an array of time formats.
	 *
	 * @param string|null $context Optional. The context in which the time formats are being retrieved.
	 *
	 * @return array An array of time formats in label/value format.
	 */
	public static function get_time_formats( ?string $context = null ): array {
		$formats = [
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

		/**
		 * Filters the time formats.
		 *
		 * @param array       $formats The default time formats.
		 * @param string|null $context The context in which the time formats are being retrieved.
		 */
		return apply_filters( 'arraypress_time_formats', $formats, $context );
	}

	/**
	 * Get period labels for singular and plural forms.
	 *
	 * @return array An array of period labels.
	 */
	public static function get_period_labels(): array {
		$labels = [
			'day'       => [
				'singular' => _x( 'day', 'time period singular', 'arraypress' ),
				'plural'   => _x( 'days', 'time period plural', 'arraypress' ),
			],
			'week'      => [
				'singular' => _x( 'week', 'time period singular', 'arraypress' ),
				'plural'   => _x( 'weeks', 'time period plural', 'arraypress' ),
			],
			'month'     => [
				'singular' => _x( 'month', 'time period singular', 'arraypress' ),
				'plural'   => _x( 'months', 'time period plural', 'arraypress' ),
			],
			'quarter'   => [
				'singular' => _x( 'quarter', 'time period singular', 'arraypress' ),
				'plural'   => _x( 'quarters', 'time period plural', 'arraypress' ),
			],
			'semi-year' => [
				'singular' => _x( 'six months', 'time period singular', 'arraypress' ),
				'plural'   => _x( 'six months', 'time period plural', 'arraypress' ),
			],
			'year'      => [
				'singular' => _x( 'year', 'time period singular', 'arraypress' ),
				'plural'   => _x( 'years', 'time period plural', 'arraypress' ),
			],
			'hour'      => [
				'singular' => _x( 'hour', 'time period singular', 'arraypress' ),
				'plural'   => _x( 'hours', 'time period plural', 'arraypress' ),
			],
			'minute'    => [
				'singular' => _x( 'minute', 'time period singular', 'arraypress' ),
				'plural'   => _x( 'minutes', 'time period plural', 'arraypress' ),
			],
			'second'    => [
				'singular' => _x( 'second', 'time period singular', 'arraypress' ),
				'plural'   => _x( 'seconds', 'time period plural', 'arraypress' ),
			],
		];

		/**
		 * Filters the period labels.
		 *
		 * @param array $labels The default period labels.
		 */
		return apply_filters( 'arraypress_period_labels', $labels );
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
		$labels = self::get_period_labels();

		if ( isset( $labels[ $period ] ) ) {
			return $count === 1 ? $labels[ $period ]['singular'] : $labels[ $period ]['plural'];
		}

		return $period;
	}

	/**
	 * Formats a time period with its count.
	 *
	 * @param string      $period  The period type.
	 * @param int         $count   The count of the periods.
	 * @param string|null $context Optional. The context in which the period is being formatted.
	 *
	 * @return string The formatted time period string.
	 */
	public static function format_period( string $period, int $count, ?string $context = null ): string {
		$label = self::get_period_label( $period, $count );

		/**
		 * Filters the formatted period string.
		 *
		 * @param string      $formatted The default formatted string.
		 * @param string      $period    The period type.
		 * @param int         $count     The count of periods.
		 * @param string      $label     The localized period label.
		 * @param string|null $context   The context in which the period is being formatted.
		 */
		return apply_filters(
			'arraypress_formatted_period',
			sprintf( _n( '%d %s', '%d %s', $count, 'arraypress' ), $count, $label ),
			$period,
			$count,
			$label,
			$context
		);
	}

	/**
	 * Get available recurring periods.
	 *
	 * @return array An array of recurring periods with their labels.
	 */
	public static function get_recurring_periods( ?string $context = null ): array {
		$periods = [
			'day'     => __( 'Per Day', 'arraypress' ),
			'week'    => __( 'Per Week', 'arraypress' ),
			'month'   => __( 'Per Month', 'arraypress' ),
			'quarter' => __( 'Per Quarter', 'arraypress' ),
			'year'    => __( 'Per Year', 'arraypress' )
		];

		/**
		 * Filters the recurring periods.
		 *
		 * @param array       $periods The default recurring periods.
		 * @param string|null $context The context in which the periods are being retrieved.
		 */
		return apply_filters( 'arraypress_recurring_periods', $periods, $context );
	}

	/**
	 * Get the label for a specific recurring period.
	 *
	 * @param string $period The period key.
	 *
	 * @return string|null The label for the period, or null if not found.
	 */
	public static function get_recurring_period_label( string $period ): ?string {
		$periods = self::get_recurring_periods();

		return $periods[ $period ] ?? null;
	}

}