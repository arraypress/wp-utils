<?php
/**
 * Cookie Utility Class for WordPress
 *
 * This class provides a set of methods for handling cookies in WordPress,
 * including setting, getting, checking, and deleting cookies.
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
 * Check if the class `Cookie` is defined, and if not, define it.
 */
if ( ! class_exists( 'Cookie' ) ) :

	/**
	 * Cookie Utility Class
	 */
	class Cookie {

		/**
		 * Set a cookie.
		 *
		 * @param string $name     The name of the cookie.
		 * @param string $value    The value of the cookie.
		 * @param int    $expire   The time the cookie expires as Unix timestamp.
		 * @param string $path     The path on the server in which the cookie will be available on.
		 * @param string $domain   The (sub)domain that the cookie is available to.
		 * @param bool   $secure   If set to true the cookie will only be transmitted over a secure HTTPS connection.
		 * @param bool   $httponly If set to true the cookie will be made accessible only through the HTTP protocol.
		 *
		 * @return bool Whether the cookie was successfully set.
		 */
		public static function set( string $name, string $value, int $expire = 0, string $path = '/', string $domain = '', bool $secure = true, bool $httponly = true ): bool {
			if ( headers_sent() ) {
				return false;
			}

			$result = setcookie( $name, $value, [
				'expires'  => $expire,
				'path'     => $path,
				'domain'   => $domain,
				'secure'   => $secure,
				'httponly' => $httponly,
				'samesite' => 'Strict'
			] );

			if ( $result ) {
				$_COOKIE[ $name ] = $value;
			}

			return $result;
		}

		/**
		 * Get a cookie value.
		 *
		 * @param string $name    The name of the cookie.
		 * @param mixed  $default The default value to return if the cookie is not set.
		 *
		 * @return mixed The value of the cookie if it exists, otherwise the default value.
		 */
		public static function get( string $name, $default = null ) {
			return $_COOKIE[ $name ] ?? $default;
		}

		/**
		 * Check if a cookie exists.
		 *
		 * @param string $name The name of the cookie.
		 *
		 * @return bool True if the cookie exists, false otherwise.
		 */
		public static function exists( string $name ): bool {
			return isset( $_COOKIE[ $name ] );
		}

		/**
		 * Delete a cookie.
		 *
		 * @param string $name   The name of the cookie.
		 * @param string $path   The path on the server in which the cookie will be available on.
		 * @param string $domain The (sub)domain that the cookie is available to.
		 *
		 * @return bool Whether the cookie was successfully deleted.
		 */
		public static function delete( string $name, string $path = '/', string $domain = '' ): bool {
			if ( self::exists( $name ) ) {
				unset( $_COOKIE[ $name ] );

				return self::set( $name, '', time() - 3600, $path, $domain );
			}

			return false;
		}

		/**
		 * Get all cookies.
		 *
		 * @return array An array of all cookies.
		 */
		public static function get_all(): array {
			return $_COOKIE;
		}

		/**
		 * Set multiple cookies at once.
		 *
		 * @param array $cookies An associative array of cookie names and values.
		 * @param int   $expire  The time the cookies expire as Unix timestamp.
		 * @param array $options Additional options for the cookies (path, domain, secure, httponly).
		 *
		 * @return bool Whether all cookies were successfully set.
		 */
		public static function set_multiple( array $cookies, int $expire = 0, array $options = [] ): bool {
			$result = true;
			foreach ( $cookies as $name => $value ) {
				$result = $result && self::set(
						$name,
						$value,
						$expire,
						$options['path'] ?? '/',
						$options['domain'] ?? '',
						$options['secure'] ?? true,
						$options['httponly'] ?? true
					);
			}

			return $result;
		}

		/**
		 * Delete multiple cookies at once.
		 *
		 * @param array $names   An array of cookie names to delete.
		 * @param array $options Additional options for deleting the cookies (path, domain).
		 *
		 * @return bool Whether all cookies were successfully deleted.
		 */
		public static function delete_multiple( array $names, array $options = [] ): bool {
			$result = true;
			foreach ( $names as $name ) {
				$result = $result && self::delete(
						$name,
						$options['path'] ?? '/',
						$options['domain'] ?? ''
					);
			}

			return $result;
		}

		/**
		 * Get the value of a cookie and decode it from JSON.
		 *
		 * @param string $name    The name of the cookie.
		 * @param mixed  $default The default value to return if the cookie is not set or invalid JSON.
		 *
		 * @return mixed The decoded value of the cookie if it exists and is valid JSON, otherwise the default value.
		 */
		public static function get_json( string $name, $default = null ) {
			$value = self::get( $name );
			if ( $value === null ) {
				return $default;
			}
			$decoded = json_decode( $value, true );

			return ( json_last_error() === JSON_ERROR_NONE ) ? $decoded : $default;
		}

		/**
		 * Set a cookie with a JSON encoded value.
		 *
		 * @param string $name     The name of the cookie.
		 * @param mixed  $value    The value to be JSON encoded and set as the cookie value.
		 * @param int    $expire   The time the cookie expires as Unix timestamp.
		 * @param string $path     The path on the server in which the cookie will be available on.
		 * @param string $domain   The (sub)domain that the cookie is available to.
		 * @param bool   $secure   If set to true the cookie will only be transmitted over a secure HTTPS connection.
		 * @param bool   $httponly If set to true the cookie will be made accessible only through the HTTP protocol.
		 *
		 * @return bool Whether the cookie was successfully set.
		 */
		public static function set_json(
			string $name,
			$value,
			int $expire = 0,
			string $path = '/',
			string $domain = '',
			bool $secure = true,
			bool $httponly = true
		): bool {
			$json_value = wp_json_encode( $value );
			if ( $json_value === false ) {
				return false;
			}

			return self::set( $name, $json_value, $expire, $path, $domain, $secure, $httponly );
		}

		/**
		 * Get the remaining lifetime of a cookie in seconds.
		 *
		 * @param string $name The name of the cookie.
		 *
		 * @return int|null The remaining lifetime in seconds, or null if the cookie doesn't exist or is a session cookie.
		 */
		public static function get_remaining_lifetime( string $name ): ?int {
			if ( ! self::exists( $name ) ) {
				return null;
			}

			$cookies = wp_parse_cookie( $_SERVER['HTTP_COOKIE'] ?? '' );
			if ( ! isset( $cookies[ $name ] ) ) {
				return null;
			}

			$expire = isset( $cookies[ $name ]['expires'] ) ? strtotime( $cookies[ $name ]['expires'] ) : null;
			if ( $expire === null || $expire === false ) {
				return null;
			}

			$remaining = $expire - time();

			return $remaining > 0 ? $remaining : null;
		}

	}

endif;