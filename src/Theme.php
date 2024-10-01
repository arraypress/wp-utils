<?php
/**
 * WordPress Theme Utilities
 *
 * This file contains utility functions for managing WordPress theme-related operations.
 * It provides a set of static methods encapsulated in the Theme class to handle various
 * theme-specific tasks and retrieve theme information.
 *
 * @package       ArrayPress/Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Theme` is defined, and if not, define it.
 */
if ( ! class_exists( 'Theme' ) ) :

	/**
	 * Theme Utilities
	 *
	 * Provides utility functions for managing WordPress themes, such as retrieving available themes,
	 * checking active themes, working with theme versions, and handling theme-specific operations.
	 * This class offers methods for getting theme information, checking theme support for features,
	 * and interacting with theme mods and customizer.
	 */
	class Theme {

		/**
		 * Get available themes and return them in label/value format.
		 *
		 * @return array An array of available themes in label/value format.
		 */
		public static function get_themes(): array {
			$themes = wp_get_themes();

			if ( empty( $themes ) || ! is_array( $themes ) ) {
				return [];
			}

			$options = [];

			foreach ( $themes as $theme_slug => $theme ) {
				$options[] = [
					'value' => esc_attr( $theme_slug ),
					'label' => esc_html( $theme->get( 'Name' ) ),
				];
			}

			return $options;
		}

		/**
		 * Check if the specified theme or themes are active.
		 *
		 * @param string|array $theme Theme name or array of theme names to check.
		 *
		 * @return bool True if the specified theme or one of the themes in the array is active, false otherwise.
		 */
		public static function is_active_theme( $theme ): bool {
			$current_theme = get_template();

			if ( is_array( $theme ) ) {
				return in_array( $current_theme, $theme, true );
			}

			return $current_theme === $theme;
		}

		/**
		 * Check if the site is using a default WordPress theme.
		 *
		 * @return bool True if the site is using a default WordPress theme, false otherwise.
		 */
		public static function is_default_theme_active(): bool {
			$default_themes = [
				'twentytwentyfive',
				'twentytwentyfour',
				'twentytwentythree',
				'twentytwentytwo',
				'twentytwentyone',
				'twentytwenty',
				'twentynineteen',
				'twentyseventeen',
				'twentysixteen',
				'twentyfifteen',
				'twentyfourteen',
				'twentythirteen',
				'twentyeleven',
				'twentytwelve',
				'twentyten',
			];

			return self::is_active_theme( $default_themes );
		}

		/**
		 * Get the currently active theme object.
		 *
		 * @return \WP_Theme The current theme object.
		 */
		public static function get_current_theme(): \WP_Theme {
			return wp_get_theme();
		}

		/**
		 * Get the version of the currently active theme.
		 *
		 * @return string The version of the current theme.
		 */
		public static function get_current_theme_version(): string {
			return self::get_current_theme()->get( 'Version' );
		}

		/**
		 * Check if a specific theme version is active.
		 *
		 * @param string $theme   Theme name or stylesheet.
		 * @param string $version Version to check against.
		 * @param string $compare Comparison operator (e.g., '>', '>=', '<', '<=', '==', '!=').
		 *
		 * @return bool True if the version comparison is true, false otherwise.
		 */
		public static function is_theme_version( string $theme, string $version, string $compare = '==' ): bool {
			$current_theme = wp_get_theme( $theme );
			if ( ! $current_theme->exists() ) {
				return false;
			}

			return version_compare( $current_theme->get( 'Version' ), $version, $compare );
		}

		/**
		 * Get the theme's directory URI.
		 *
		 * @param string $theme Optional. Theme name. Defaults to the current theme.
		 *
		 * @return string The theme's directory URI.
		 */
		public static function get_theme_directory_uri( string $theme = '' ): string {
			return get_theme_root_uri( $theme ) . '/' . ( $theme ?: get_stylesheet() );
		}

		/**
		 * Check if the current theme supports a specific feature.
		 *
		 * @param string $feature The feature to check for.
		 *
		 * @return bool True if the current theme supports the feature, false otherwise.
		 */
		public static function theme_supports( string $feature ): bool {
			return current_theme_supports( $feature );
		}

		/**
		 * Get all parent themes of the current theme.
		 *
		 * @return array An array of parent theme objects.
		 */
		public static function get_parent_themes(): array {
			$parents = [];
			$theme   = self::get_current_theme();

			while ( $theme && $theme->parent() ) {
				$theme     = new \WP_Theme( $theme->get_template(), $theme->get_theme_root() );
				$parents[] = $theme;
			}

			return $parents;
		}

		/**
		 * Check if the current theme is a child theme.
		 *
		 * @return bool True if the current theme is a child theme, false otherwise.
		 */
		public static function is_child_theme(): bool {
			return is_child_theme();
		}

		/**
		 * Get theme mod with a default value.
		 *
		 * @param string $name    Theme modification name.
		 * @param mixed  $default Default value.
		 *
		 * @return mixed Theme modification value or default.
		 */
		public static function get_theme_mod( string $name, $default = false ) {
			return get_theme_mod( $name, $default );
		}

		/**
		 * Check if a theme exists.
		 *
		 * @param string $theme_name Theme name or stylesheet.
		 *
		 * @return bool True if the theme exists, false otherwise.
		 */
		public static function theme_exists( string $theme_name ): bool {
			return wp_get_theme( $theme_name )->exists();
		}

		/**
		 * Get theme screenshot URL.
		 *
		 * @param string $theme Optional. Theme name. Defaults to the current theme.
		 *
		 * @return string|false The theme screenshot URL or false if not found.
		 */
		public static function get_theme_screenshot_url( string $theme = '' ): string {
			$theme_obj = wp_get_theme( $theme );

			return $theme_obj->exists() ? $theme_obj->get_screenshot() : '';
		}

		/**
		 * Get theme customizer URL.
		 *
		 * @return string The theme customizer URL.
		 */
		public static function get_customizer_url(): string {
			return admin_url( 'customize.php' );
		}
	}

endif;