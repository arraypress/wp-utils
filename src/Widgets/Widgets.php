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
 * Class Widgets
 *
 * Utility functions for working with multiple widgets and global widget operations.
 */
class Widgets {

	/**
	 * Get available widgets and return them in label/value format.
	 *
	 * @return array An array of available widgets in label/value format.
	 */
	public static function get_registered(): array {
		global $wp_registered_widgets;

		if ( empty( $wp_registered_widgets ) || ! is_array( $wp_registered_widgets ) ) {
			return [];
		}

		$options = [];

		foreach ( $wp_registered_widgets as $widget_id => $widget ) {
			$options[] = [
				'value' => esc_attr( $widget_id ),
				'label' => esc_html( $widget['name'] ?? __( 'Unknown', 'arraypress' ) ),
			];
		}

		return $options;
	}

	/**
	 * Get all registered sidebars.
	 *
	 * @return array An array of registered sidebars.
	 */
	public static function get_sidebars(): array {
		global $wp_registered_sidebars;

		return $wp_registered_sidebars ?? [];
	}

	/**
	 * Get widgets in a specific sidebar.
	 *
	 * @param string $sidebar_id The ID of the sidebar.
	 *
	 * @return array An array of widget IDs in the sidebar.
	 */
	public static function get_sidebar_widgets( string $sidebar_id ): array {
		$sidebars_widgets = wp_get_sidebars_widgets();

		return $sidebars_widgets[ $sidebar_id ] ?? [];
	}

	/**
	 * Check if a sidebar is active (has widgets).
	 *
	 * @param string $sidebar_id The ID of the sidebar to check.
	 *
	 * @return bool True if the sidebar is active, false otherwise.
	 */
	public static function is_sidebar_active( string $sidebar_id ): bool {
		return is_active_sidebar( $sidebar_id );
	}

	/**
	 * Get all instances of a specific widget type.
	 *
	 * @param string $widget_base The base ID of the widget type.
	 *
	 * @return array An array of active widget instances.
	 */
	public static function get_instances( string $widget_base ): array {
		global $wp_registered_widgets;
		$instances = [];

		$sidebars_widgets = wp_get_sidebars_widgets();
		unset( $sidebars_widgets['wp_inactive_widgets'] );

		foreach ( $sidebars_widgets as $widgets ) {
			if ( ! is_array( $widgets ) ) {
				continue;
			}

			foreach ( $widgets as $widget_id ) {
				if ( strpos( $widget_id, $widget_base . '-' ) === 0 ) {
					if ( isset( $wp_registered_widgets[ $widget_id ] ) ) {
						$instances[] = $wp_registered_widgets[ $widget_id ];
					}
				}
			}
		}

		return $instances;
	}

	/**
	 * Check if a widget type has any active instances.
	 *
	 * @param string $widget_base The base ID of the widget type.
	 *
	 * @return bool True if the widget type has active instances, false otherwise.
	 */
	public static function has_instances( string $widget_base ): bool {
		return ! empty( self::get_instances( $widget_base ) );
	}

	/**
	 * Get all sidebar options in label/value format.
	 *
	 * @return array An array of sidebar options.
	 */
	public static function get_sidebar_options(): array {
		$sidebars = self::get_sidebars();
		$options  = [];

		foreach ( $sidebars as $id => $sidebar ) {
			$options[] = [
				'value' => esc_attr( $id ),
				'label' => esc_html( $sidebar['name'] ?? __( 'Unknown Sidebar', 'arraypress' ) ),
			];
		}

		return $options;
	}

	/**
	 * Get count of active widgets in all sidebars.
	 *
	 * @return array An array of sidebar IDs and their widget counts.
	 */
	public static function get_widget_counts(): array {
		$sidebars_widgets = wp_get_sidebars_widgets();
		$counts           = [];

		foreach ( $sidebars_widgets as $sidebar_id => $widgets ) {
			if ( $sidebar_id !== 'wp_inactive_widgets' ) {
				$counts[ $sidebar_id ] = is_array( $widgets ) ? count( $widgets ) : 0;
			}
		}

		return $counts;
	}

}