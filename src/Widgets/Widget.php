<?php
/**
 * WordPress Widgets Utility Classes
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Widgets;

/**
 * Class Widget
 *
 * Utility functions for working with a specific widget.
 */
class Widget {

	/**
	 * Get information about a specific widget.
	 *
	 * @param string $widget_id The ID of the widget.
	 *
	 * @return array|null Information about the widget, or null if not found.
	 */
	public static function get_info( string $widget_id ): ?array {
		global $wp_registered_widgets;

		if ( isset( $wp_registered_widgets[ $widget_id ] ) ) {
			$widget = $wp_registered_widgets[ $widget_id ];

			return [
				'id'          => $widget_id,
				'name'        => $widget['name'],
				'callback'    => $widget['callback'],
				'classname'   => $widget['classname'],
				'description' => $widget['description'] ?? '',
			];
		}

		return null;
	}

	/**
	 * Check if a widget exists.
	 *
	 * @param string $widget_id The ID of the widget to check.
	 *
	 * @return bool True if the widget exists, false otherwise.
	 */
	public static function exists( string $widget_id ): bool {
		global $wp_registered_widgets;

		return isset( $wp_registered_widgets[ $widget_id ] );
	}

	/**
	 * Get the settings for a specific widget type.
	 *
	 * @param string $widget_base The base ID of the widget type.
	 *
	 * @return array An array of widget settings.
	 */
	public static function get_settings( string $widget_base ): array {
		$settings = get_option( "widget_$widget_base" );

		return is_array( $settings ) ? $settings : [];
	}

	/**
	 * Update the settings for a specific widget type.
	 *
	 * @param string $widget_base The base ID of the widget type.
	 * @param array  $settings    The new settings for the widget type.
	 *
	 * @return bool True if settings were updated, false otherwise.
	 */
	public static function update_settings( string $widget_base, array $settings ): bool {
		return update_option( "widget_$widget_base", $settings );
	}

	/**
	 * Get the class name for a widget type.
	 *
	 * @param string $widget_base The base ID of the widget type.
	 *
	 * @return string|null The class name of the widget, or null if not found.
	 */
	public static function get_class_name( string $widget_base ): ?string {
		global $wp_widget_factory;

		if ( isset( $wp_widget_factory->widgets[ $widget_base ] ) ) {
			return get_class( $wp_widget_factory->widgets[ $widget_base ] );
		}

		return null;
	}

	/**
	 * Get all settings for a specific widget type.
	 *
	 * @param string $widget_base The base ID of the widget type.
	 *
	 * @return array An array of widget settings.
	 */
	public static function get_all_settings( string $widget_base ): array {
		$settings = get_option( "widget_$widget_base" );

		if ( ! is_array( $settings ) ) {
			return [];
		}

		// Remove the '_multiwidget' element which WordPress uses internally
		unset( $settings['_multiwidget'] );

		return $settings;
	}

	/**
	 * Delete a widget's settings.
	 *
	 * @param string $widget_base The base ID of the widget type.
	 *
	 * @return bool True if settings were deleted, false otherwise.
	 */
	public static function delete_settings( string $widget_base ): bool {
		return delete_option( "widget_$widget_base" );
	}

}

