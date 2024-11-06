<?php
/**
 * WordPress Theme Utilities
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Themes;

use WP_Theme;

/**
 * Class Theme
 *
 * Utility functions for working with a specific theme.
 */
class Theme {

	/**
	 * Get a theme object.
	 *
	 * @param string $stylesheet Optional. Theme directory name. Default is current theme.
	 *
	 * @return WP_Theme The theme object.
	 */
	public static function get( string $stylesheet = '' ): WP_Theme {
		return wp_get_theme( $stylesheet );
	}

	/**
	 * Check if a theme exists.
	 *
	 * @param string $stylesheet Theme directory name.
	 *
	 * @return bool True if the theme exists, false otherwise.
	 */
	public static function exists( string $stylesheet ): bool {
		return self::get( $stylesheet )->exists();
	}

	/**
	 * Check if the specified theme or themes are active.
	 *
	 * @param string|array $stylesheet Theme directory name or array of directory names.
	 *
	 * @return bool True if specified theme is active, false otherwise.
	 */
	public static function is_active( $stylesheet ): bool {
		$current_theme = strtolower( get_template() );
		$stylesheet    = is_array( $stylesheet )
			? array_map( 'strtolower', $stylesheet )
			: strtolower( $stylesheet );

		return is_array( $stylesheet )
			? in_array( $current_theme, $stylesheet, true )
			: $current_theme === $stylesheet;
	}

	/**
	 * Check if the site is using a default WordPress theme.
	 *
	 * @return bool True if using a default WordPress theme, false otherwise.
	 */
	public static function is_default_active(): bool {
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

		return self::is_active( $default_themes );
	}

	/**
	 * Get the currently active theme object.
	 *
	 * @return WP_Theme The current theme object.
	 */
	public static function get_current(): WP_Theme {
		return self::get();
	}

	/**
	 * Get the version of a theme.
	 *
	 * @param string $stylesheet Optional. Theme directory name. Default is current theme.
	 *
	 * @return string The theme version.
	 */
	public static function get_version( string $stylesheet = '' ): string {
		return self::get( $stylesheet )->get( 'Version' );
	}

	/**
	 * Check if a specific theme version is active.
	 *
	 * @param string $stylesheet Theme directory name.
	 * @param string $version    Version to check against.
	 * @param string $compare    Comparison operator (e.g., '>', '>=', '<', '<=', '==', '!=').
	 *
	 * @return bool True if version comparison is true, false otherwise.
	 */
	public static function is_version( string $stylesheet, string $version, string $compare = '==' ): bool {
		$theme = self::get( $stylesheet );
		if ( ! $theme->exists() ) {
			return false;
		}

		return version_compare( $theme->get( 'Version' ), $version, $compare );
	}

	/**
	 * Get the theme's directory URI.
	 *
	 * @param string $stylesheet Optional. Theme directory name. Default is current theme.
	 *
	 * @return string The theme's directory URI.
	 */
	public static function get_directory_uri( string $stylesheet = '' ): string {
		$stylesheet = $stylesheet ?: get_stylesheet();

		return get_theme_root_uri( $stylesheet ) . '/' . $stylesheet;
	}

	/**
	 * Check if the current theme supports a specific feature.
	 *
	 * @param string $feature The feature to check for.
	 *
	 * @return bool True if the current theme supports the feature.
	 */
	public static function supports( string $feature ): bool {
		return current_theme_supports( $feature );
	}

	/**
	 * Get all parent themes of the current theme.
	 *
	 * @return array An array of parent theme objects.
	 */
	public static function get_parent_themes(): array {
		$parents = [];
		$theme   = self::get_current();

		while ( $theme->parent() ) {
			$theme     = new WP_Theme( $theme->get_template(), $theme->get_theme_root() );
			$parents[] = $theme;
		}

		return $parents;
	}

	/**
	 * Check if the current theme is a child theme.
	 *
	 * @return bool True if current theme is a child theme.
	 */
	public static function is_child(): bool {
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
	public static function get_mod( string $name, $default = false ) {
		return get_theme_mod( $name, $default );
	}

	/**
	 * Get theme screenshot URL.
	 *
	 * @param string $stylesheet Optional. Theme directory name. Default is current theme.
	 *
	 * @return string Theme screenshot URL or empty string if not found.
	 */
	public static function get_screenshot_url( string $stylesheet = '' ): string {
		$theme = self::get( $stylesheet );

		return $theme->exists() ? $theme->get_screenshot() : '';
	}

	/**
	 * Get theme customizer URL.
	 *
	 * @return string The theme customizer URL.
	 */
	public static function get_customizer_url(): string {
		return admin_url( 'customize.php' );
	}

	/**
	 * Check if the current theme supports Gutenberg.
	 *
	 * @return bool True if theme supports Gutenberg.
	 */
	public static function supports_gutenberg(): bool {
		return current_theme_supports( 'align-wide' ) ||
		       current_theme_supports( 'responsive-embeds' );
	}

	/**
	 * Check if full site editing is active.
	 *
	 * @return bool True if full site editing is active.
	 */
	public static function is_block_theme(): bool {
		return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	}

}