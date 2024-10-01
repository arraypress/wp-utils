<?php
/**
 * Gutenberg Utilities for WordPress
 *
 * This class provides a set of utility functions for working with Gutenberg editor
 * in WordPress. It includes methods for checking Gutenberg-related settings,
 * plugin statuses, and other editor-specific operations. This class focuses on
 * Gutenberg as a whole, rather than specific block operations.
 *
 * @package       ArrayPress/WP-Utils
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Gutenberg` is defined, and if not, define it.
 */
if ( ! class_exists( 'Gutenberg' ) ) :

	/**
	 * Gutenberg utility class for WordPress.
	 *
	 * This class provides utility methods for working with the Gutenberg editor
	 * in WordPress, including checking editor settings, plugin statuses, and
	 * other Gutenberg-related operations.
	 */
	class Gutenberg {

		/**
		 * Determine if the current request is a block rendering request in the editor.
		 *
		 * @return bool
		 */
		public static function is_rendering_preview(): bool {
			if ( is_admin() ) {
				return true;
			}

			if ( ! defined( 'REST_REQUEST' ) || ! is_user_logged_in() ) {
				return false;
			}

			global $wp;

			if ( ! $wp instanceof \WP || empty( $wp->query_vars['rest_route'] ) ) {
				return false;
			}

			$route = $wp->query_vars['rest_route'];

			return str_contains( $route, '/block-renderer/' );
		}

		/**
		 * Check if the Classic Editor plugin is active.
		 *
		 * @return bool
		 */
		public static function is_classic_editor_plugin_active(): bool {
			return is_plugin_active( 'classic-editor/classic-editor.php' );
		}

		/**
		 * Check if the Gutenberg plugin is installed (regardless of whether it's activated).
		 *
		 * @return bool
		 */
		public static function is_plugin_installed(): bool {
			return file_exists( WP_PLUGIN_DIR . '/gutenberg/gutenberg.php' );
		}

		/**
		 * Check if the Gutenberg plugin is active.
		 *
		 * @return bool
		 */
		public static function is_plugin_active(): bool {
			return is_plugin_active( 'gutenberg/gutenberg.php' );
		}

		/**
		 * Check if Gutenberg is active (either through core or plugin, and not disabled by Classic Editor).
		 *
		 * @return bool
		 */
		public static function is_active(): bool {
			// Check if block editor function exists (available in core)
			$gutenberg_in_core = function_exists( 'use_block_editor_for_post_type' );

			// Check if Gutenberg plugin is active
			$gutenberg_plugin_active = self::is_plugin_active();

			// Check if Classic Editor is not active (which would disable Gutenberg)
			$classic_editor_not_active = ! self::is_classic_editor_plugin_active();

			return ( $gutenberg_in_core || $gutenberg_plugin_active ) && $classic_editor_not_active;
		}


		/**
		 * Check if the current theme supports Gutenberg.
		 *
		 * @return bool
		 */
		public static function does_theme_support(): bool {
			return current_theme_supports( 'align-wide' ) || current_theme_supports( 'responsive-embeds' );
		}

		/**
		 * Get the current Gutenberg version.
		 *
		 * @return string|null The Gutenberg version or null if not available.
		 */
		public static function get_version(): ?string {
			if ( defined( 'GUTENBERG_VERSION' ) ) {
				return GUTENBERG_VERSION;
			}

			return null;
		}

		/**
		 * Check if full site editing is active.
		 *
		 * @return bool
		 */
		public static function is_full_site_editing_active(): bool {
			return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
		}

		/**
		 * Get the post types that support Gutenberg.
		 *
		 * @return array An array of post type names that support Gutenberg.
		 */
		public static function get_supported_post_types(): array {
			$supported_post_types = [];

			foreach ( get_post_types_by_support( 'editor' ) as $post_type ) {
				if ( use_block_editor_for_post_type( $post_type ) ) {
					$supported_post_types[] = $post_type;
				}
			}

			return $supported_post_types;
		}

	}

endif;