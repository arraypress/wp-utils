<?php
/**
 * Status Component Class
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Elements\Components;

use ArrayPress\Utils\Elements\Element;

class Status extends Base {

	/**
	 * Create a progress bar element.
	 *
	 * @param int   $percentage The percentage of progress (0-100).
	 * @param array $attrs      Optional. Additional attributes for the outer div.
	 * @param bool  $show_label Optional. Whether to show percentage label. Default true.
	 *
	 * @return string The HTML for the progress bar.
	 */
	public static function progress_bar( int $percentage, array $attrs = [], bool $show_label = true ): string {
		$percentage = max( 0, min( 100, $percentage ) );

		$default_attrs = [
			'class' => 'wp-progress' . ( $show_label ? ' wp-progress--with-label' : '' )
		];
		$attrs         = array_merge( $default_attrs, $attrs );

		$bar = Element::div( '', [
			'class' => 'wp-progress-bar',
			'style' => "width: {$percentage}%"
		] );

		if ( $show_label ) {
			$bar .= Element::span( "{$percentage}%", [ 'class' => 'wp-progress-label' ] );
		}

		self::ensure_styles();

		return Element::div( $bar, $attrs );
	}

	/**
	 * Generate HTML status badge with styling.
	 *
	 * @param string $status The status text to display.
	 * @param string $type   Optional. Badge type (success, warning, error, info). Default 'default'.
	 *
	 * @return string HTML badge element.
	 */
	public static function status_badge( string $status, string $type = 'default' ): string {
		$class = 'wp-status-badge wp-status-badge--' . $type;

		self::ensure_styles();

		return Element::span( $status, [ 'class' => $class ] );
	}

	/**
	 * Format date values with color based on past or active status.
	 *
	 * @param string $value        The date value to be formatted.
	 * @param string $past_color   The hex color for past dates.
	 * @param string $active_color The hex color for active dates.
	 * @param string $default      The default value to display if the date is not available.
	 *
	 * @return string The formatted date with color or the default value.
	 */
	public static function date_with_color(
		string $value,
		string $past_color = '#ff0000',
		string $active_color = '#a3b745',
		string $default = Element::MDASH
	): string {
		if ( ! empty( $value ) ) {
			$timestamp = strtotime( $value );
			$color     = $timestamp < time() ? $past_color : $active_color;

			$formatted_date = date_i18n( get_option( 'date_format' ), $timestamp );

			return Element::span( $formatted_date, [ 'style' => "color: $color;" ] );
		}

		return $default === Element::MDASH ? Element::MDASH : Element::span( $default );
	}

}