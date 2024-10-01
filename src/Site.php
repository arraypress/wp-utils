<?php
/**
 * WordPress Site Utilities
 *
 * This file contains utility functions for managing WordPress site-related operations.
 * It provides a set of static methods encapsulated in the Site class to handle various
 * site-specific tasks and retrieve site information.
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
 * Check if the class `Site` is defined, and if not, define it.
 */
if ( ! class_exists( 'Site' ) ) :

	/**
	 * Site Utilities
	 *
	 * Provides utility functions for managing WordPress site-related operations, such as
	 * retrieving site information, checking site status, and handling site-specific tasks.
	 * This class offers methods for getting site name, URL, tagline, language, and other
	 * essential site details.
	 */
	class Site {

		/**
		 * Retrieves the site name.
		 *
		 * @return string The decoded site name, with special HTML characters converted to their corresponding entities.
		 */
		public static function get_sitename(): string {
			return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		}

		/**
		 * Retrieves the site URL.
		 *
		 * @param string      $path   Optional. Path relative to the site URL.
		 * @param string|null $scheme Optional. Scheme to give the site URL context. See set_url_scheme().
		 *
		 * @return string Site URL link with optional path appended.
		 */
		public static function get_site_url( string $path = '', ?string $scheme = null ): string {
			return get_site_url( null, $path, $scheme );
		}

		/**
		 * Retrieves the site's tagline.
		 *
		 * @return string The site's tagline.
		 */
		public static function get_tagline(): string {
			return get_bloginfo( 'description' );
		}

		/**
		 * Checks if the current site is a multisite installation.
		 *
		 * @return bool True if multisite, false otherwise.
		 */
		public static function is_multisite(): bool {
			return is_multisite();
		}

		/**
		 * Retrieves the current site's language.
		 *
		 * @return string The site's language code.
		 */
		public static function get_language(): string {
			return get_bloginfo( 'language' );
		}

		/**
		 * Retrieves the WordPress version for the current site.
		 *
		 * @return string The WordPress version.
		 */
		public static function get_wp_version(): string {
			global $wp_version;

			return $wp_version;
		}

		/**
		 * Retrieves the site's timezone.
		 *
		 * @return string The site's timezone string.
		 */
		public static function get_timezone(): string {
			return wp_timezone_string();
		}

		/**
		 * Checks if the site is currently in maintenance mode.
		 *
		 * @return bool True if in maintenance mode, false otherwise.
		 */
		public static function is_maintenance_mode(): bool {
			return wp_is_maintenance_mode();
		}

		/**
		 * Retrieves the site's admin email address.
		 *
		 * @return string The site admin email address.
		 */
		public static function get_admin_email(): string {
			return get_option( 'admin_email' );
		}

		/**
		 * Checks if the site is running on HTTPS.
		 *
		 * @return bool True if the site is using HTTPS, false otherwise.
		 */
		public static function is_ssl(): bool {
			return is_ssl();
		}

		/**
		 * Retrieves the home URL for the current site.
		 *
		 * @param string      $path   Optional. Path relative to the home URL.
		 * @param string|null $scheme Optional. Scheme to give the home URL context. See set_url_scheme().
		 *
		 * @return string Home URL link with optional path appended.
		 */
		public static function get_home_url( string $path = '', ?string $scheme = null ): string {
			return get_home_url( null, $path, $scheme );
		}

		/**
		 * Retrieves the login URL for the current site.
		 *
		 * @param string $redirect     Optional. Path to redirect to on login.
		 * @param bool   $force_reauth Optional. Whether to force reauthorization, even if a cookie is present.
		 *
		 * @return string The login URL.
		 */
		public static function get_login_url( string $redirect = '', bool $force_reauth = false ): string {
			return wp_login_url( $redirect, $force_reauth );
		}

		/**
		 * Get an array of time zones.
		 *
		 * @return array An array of time zones in label/value format.
		 */
		public static function get_time_zone_options(): array {
			$time_zones = wp_timezone_choice();
			$options    = [];

			foreach ( $time_zones as $label => $value ) {
				$options[] = [
					'value' => esc_attr( $value ),
					'label' => esc_html( $label ),
				];
			}

			return $options;
		}

	}
endif;