<?php
/**
 * WordPress Widgets Utility Class
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Theme;

/**
 * Check if the class `Widget` is defined, and if not, define it.
 */
if ( ! class_exists( 'Widget' ) ) :

	/**
	 * User Utility Functions
	 *
	 * Provides static utility functions for user-related operations.
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

			return $wp_registered_sidebars;
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
		 * Get all widget instances of a specific type.
		 *
		 * @param string $widget_base The base ID of the widget type.
		 *
		 * @return array An array of widget instances.
		 */
		public static function get_instances( string $widget_base ): array {
			$settings = self::get_widget_settings( $widget_base );

			return array_filter( $settings, 'is_array' );
		}

		/**
		 * Check if a widget type is active (has instances).
		 *
		 * @param string $widget_base The base ID of the widget type.
		 *
		 * @return bool True if the widget type is active, false otherwise.
		 */
		public static function is_widget_type_active( string $widget_base ): bool {
			$instances = self::get_widget_instances( $widget_base );

			return ! empty( $instances );
		}

		/**
		 * Get the class name for a widget type.
		 *
		 * @param string $widget_base The base ID of the widget type.
		 *
		 * @return string|null The class name of the widget, or null if not found.
		 */
		public static function get_widget_class_name( string $widget_base ): ?string {
			global $wp_widget_factory;

			if ( isset( $wp_widget_factory->widgets[ $widget_base ] ) ) {
				return get_class( $wp_widget_factory->widgets[ $widget_base ] );
			}

			return null;
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
		 * Get all widget settings for a specific widget type.
		 *
		 * @param string $widget_base The base ID of the widget type.
		 *
		 * @return array An array of widget settings.
		 */
		public static function get_widget_settings( string $widget_base ): array {
			$settings = get_option( "widget_$widget_base" );

			if ( ! is_array( $settings ) ) {
				return [];
			}

			// Remove the '_multiwidget' element which WordPress uses internally
			unset( $settings['_multiwidget'] );

			return $settings;
		}

		/**
		 * Get all instances of a specific widget type.
		 *
		 * @param string $widget_base The base ID of the widget type.
		 *
		 * @return array An array of active widget instances.
		 */
		public static function get_widget_instances( string $widget_base ): array {
			global $wp_registered_widgets;
			$instances = [];

			// Get all sidebar widgets
			$sidebars_widgets = wp_get_sidebars_widgets();

			// Remove inactive widgets array
			unset( $sidebars_widgets['wp_inactive_widgets'] );

			// Loop through all sidebars
			foreach ( $sidebars_widgets as $sidebar => $widgets ) {
				if ( ! is_array( $widgets ) ) {
					continue;
				}

				// Loop through widgets in this sidebar
				foreach ( $widgets as $widget_id ) {
					// Check if this widget matches our base ID
					if ( strpos( $widget_id, $widget_base . '-' ) === 0 ) {
						if ( isset( $wp_registered_widgets[ $widget_id ] ) ) {
							$instances[] = $wp_registered_widgets[ $widget_id ];
						}
					}
				}
			}

			return $instances;
		}

	}
endif;