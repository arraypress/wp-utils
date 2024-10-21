<?php
/**
 * Request Utilities for WordPress
 *
 * This class provides utility functions for identifying and handling
 * various types of requests in a WordPress environment. It includes methods
 * for checking request types, retrieving request variables, and accessing
 * common request-related information.
 *
 * @package       YourNamespace\Utils
 * @copyright     Copyright 2024, YourCompany
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

/**
 * Check if the class `Request` is defined, and if not, define it.
 */
if ( ! class_exists( 'Request' ) ) :

	/**
	 * Request Utilities
	 *
	 * Provides a range of utility functions for request-related operations,
	 * such as identifying request types, retrieving request variables,
	 * and accessing common request-related information.
	 */
	class Request {

		/**
		 * What type of request is this?
		 *
		 * @param string|array $type admin, ajax, cron, frontend, json, api, rest, cli, editor.
		 *
		 * @return bool
		 */
		public static function is( $type ): bool {
			if ( is_string( $type ) ) {
				return self::is_type( $type );
			}

			if ( is_array( $type ) ) {
				foreach ( $type as $t ) {
					if ( self::is_type( $t ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Check if the request is of a certain type.
		 *
		 * @param string $type admin, ajax, cron, frontend, json, api, rest, cli, editor.
		 *
		 * @return bool
		 */
		private static function is_type( string $type ): bool {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return self::is_ajax();
				case 'cron':
					return self::is_cron();
				case 'rest':
					return self::is_rest();
				case 'frontend':
					return self::is_frontend();
				case 'json':
					return wp_is_json_request();
				case 'api':
					return self::is_api();
				case 'editor':
					return self::is_editor();
				case 'cli':
					return self::is_cli();
				default:
					return false;
			}
		}

		/**
		 * Returns true if the request is a frontend request.
		 *
		 * @return bool
		 */
		public static function is_frontend(): bool {
			return ! self::is( [ 'admin', 'ajax', 'cron', 'rest', 'api', 'cli' ] );
		}

		/**
		 * Returns true if the request is an AJAX request.
		 *
		 * @return bool
		 */
		public static function is_ajax(): bool {
			return defined( 'DOING_AJAX' ) && DOING_AJAX;
		}

		/**
		 * Returns true if the request is a cron request.
		 *
		 * @return bool
		 */
		public static function is_cron(): bool {
			return defined( 'DOING_CRON' ) && DOING_CRON;
		}

		/**
		 * Returns true if the request is a REST API request.
		 *
		 * @return bool
		 */
		public static function is_rest(): bool {
			return defined( 'REST_REQUEST' ) && REST_REQUEST;
		}

		/**
		 * Returns true if the request is an API request.
		 *
		 * @return bool
		 */
		public static function is_api(): bool {
			return defined( 'EDD_DOING_API' ) && EDD_DOING_API;
		}

		/**
		 * Determines if the current request is related to the block editor.
		 *
		 * This method checks for three scenarios:
		 * 1. If we're in the admin area and the current screen is a block editor.
		 * 2. If it's a frontend request for block rendering (e.g., for server-side rendered blocks).
		 * 3. If it's a REST API request for block rendering.
		 *
		 * @return bool True if the request is related to the block editor, false otherwise.
		 */
		public static function is_editor(): bool {
			// Check if we're in the admin area and it's a block editor screen
			if ( is_admin() && function_exists( 'get_current_screen' ) ) {
				$screen = get_current_screen();
				if ( $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
					return true;
				}
			}

			// Check if it's a frontend block rendering request
			if ( ! is_admin() && defined( 'REST_REQUEST' ) && REST_REQUEST && is_user_logged_in() ) {
				$current_route = self::get_current_rest_route();
				if ( $current_route !== null && str_contains( $current_route, '/block-renderer/' ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Returns true if the request is a CLI request.
		 *
		 * @return bool
		 */
		public static function is_cli(): bool {
			return ( php_sapi_name() === 'cli' || defined( 'STDIN' ) );
		}

		/**
		 * Retrieves and sanitizes the HTTP_USER_AGENT from $_SERVER.
		 *
		 * @return string The sanitized HTTP_USER_AGENT or an empty string if not set.
		 */
		public static function get_user_agent(): string {
			$user_agent = wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' );

			return wp_strip_all_tags( $user_agent );
		}

		/**
		 * Checks if the current request is from a mobile device.
		 *
		 * @return bool True if the request is from a mobile device, false otherwise.
		 */
		public static function is_mobile(): bool {
			$user_agent    = self::get_user_agent();
			$mobile_agents = [
				'Android',
				'webOS',
				'iPhone',
				'iPad',
				'iPod',
				'BlackBerry',
				'Windows Phone'
			];

			foreach ( $mobile_agents as $agent ) {
				if ( stripos( $user_agent, $agent ) !== false ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Checks if the current request is from a desktop device.
		 *
		 * @return bool True if the request is from a desktop device, false otherwise.
		 */
		public static function is_desktop(): bool {
			return ! self::is_mobile();
		}

		/**
		 * Retrieves and sanitizes the HTTP_ACCEPT_LANGUAGE from $_SERVER.
		 *
		 * @param string $default The default value to return if HTTP_ACCEPT_LANGUAGE is not set.
		 *
		 * @return string The sanitized HTTP_ACCEPT_LANGUAGE or the provided default value.
		 */
		public static function get_accept_language( string $default = '' ): string {
			$accept_language = wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '' );

			return $accept_language ? wp_strip_all_tags( $accept_language ) : $default;
		}

		/**
		 * Retrieves the IP address of the current user.
		 *
		 * This method uses the IP class to get the user's IP address. It handles
		 * various scenarios such as proxy servers and provides a reliable way to
		 * obtain the user's actual IP address.
		 *
		 * @return string The IP address of the current user.
		 */
		public static function get_user_ip(): string {
			return IP::get_user_ip();
		}

		/**
		 * Returns the sanitized version of the `$_REQUEST` super-global array.
		 *
		 * @param bool $refresh Whether to refresh the cache.
		 *
		 * @return array The sanitized version of the `$_REQUEST` super-global.
		 */
		public static function get_request_vars( bool $refresh = false ): array {
			static $cache = null;

			if ( null !== $cache && ! $refresh ) {
				return $cache;
			}

			$cache = array();
			foreach ( $_REQUEST as $key => $value ) {
				$sanitized_key = sanitize_key( $key );
				if ( is_array( $value ) ) {
					$cache[ $sanitized_key ] = array_map( 'sanitize_text_field', $value );
				} else {
					$cache[ $sanitized_key ] = sanitize_text_field( $value );
				}
			}

			return $cache;
		}

		/**
		 * Returns the sanitized version of the `$_POST` super-global array.
		 *
		 * @param bool $refresh Whether to refresh the cache.
		 *
		 * @return array The sanitized version of the `$_POST` super-global.
		 */
		public static function get_post_vars( bool $refresh = false ): array {
			static $cache = null;

			if ( null !== $cache && ! $refresh ) {
				return $cache;
			}

			$cache = [];
			foreach ( $_POST as $key => $value ) {
				$sanitized_key = sanitize_key( $key );
				if ( is_array( $value ) ) {
					$cache[ $sanitized_key ] = array_map( 'sanitize_text_field', $value );
				} else {
					$cache[ $sanitized_key ] = sanitize_text_field( $value );
				}
			}

			return $cache;
		}

		/**
		 * Returns the sanitized version of the `$_GET` super-global array.
		 *
		 * @param bool $refresh Whether to refresh the cache.
		 *
		 * @return array The sanitized version of the `$_GET` super-global.
		 */
		public static function get_get_vars( bool $refresh = false ): array {
			static $cache = null;

			if ( null !== $cache && ! $refresh ) {
				return $cache;
			}

			$cache = [];
			foreach ( $_GET as $key => $value ) {
				$sanitized_key = sanitize_key( $key );
				if ( is_array( $value ) ) {
					$cache[ $sanitized_key ] = array_map( 'sanitize_text_field', $value );
				} else {
					$cache[ $sanitized_key ] = sanitize_text_field( $value );
				}
			}

			return $cache;
		}

		/**
		 * Checks if the current connection is secure (SSL).
		 *
		 * This method checks for SSL connection using various methods:
		 * 1. WordPress core is_ssl() function
		 * 2. WordPress HTTPS plugin (if installed)
		 * 3. Cloudflare SSL indicator
		 *
		 * @return bool True if the connection is secure (SSL), false otherwise.
		 */
		public static function is_ssl(): bool {
			// Check WordPress core function first
			if ( is_ssl() ) {
				return true;
			}

			// Check WordPress HTTPS plugin if available
			global $wordpress_https;
			if ( class_exists( 'WordPressHTTPS' ) && isset( $wordpress_https ) ) {
				if ( method_exists( $wordpress_https, 'is_ssl' ) ) {
					return $wordpress_https->is_ssl();
				} elseif ( method_exists( $wordpress_https, 'isSsl' ) ) {
					return $wordpress_https->isSsl();
				}
			}

			// Check Cloudflare SSL indicator
			if ( isset( $_SERVER['HTTP_CF_VISITOR'] ) ) {
				$cf_visitor = json_decode( $_SERVER['HTTP_CF_VISITOR'] );
				if ( isset( $cf_visitor->scheme ) && $cf_visitor->scheme === 'https' ) {
					return true;
				}
			}

			return false;
		}

		/** Helpers ******************************************************************/

		/**
		 * Get the current REST route.
		 *
		 * @return string|null The current REST route or null if not available.
		 */
		private static function get_current_rest_route(): ?string {
			global $wp;
			if ( $wp instanceof \WP && ! empty( $wp->query_vars['rest_route'] ) ) {
				return $wp->query_vars['rest_route'];
			}

			return null;
		}
	}

endif;