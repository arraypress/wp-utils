<?php
/**
 * Cache Utilities
 *
 * This class provides utility functions for caching operations in WordPress,
 * including generating cache keys, remembering values, checking cache existence,
 * storing and retrieving cached values, and flushing cache items. It offers a
 * standardized approach to handle caching across WordPress applications.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

/**
 * Check if the class `Cache` is defined, and if not, define it.
 */
if ( ! class_exists( 'Cache' ) ) :

	/**
	 * Cache utility class for WordPress.
	 *
	 * This class provides utility methods for working with caching in WordPress,
	 * including generating unique cache keys, remembering values with automatic
	 * caching, checking cache existence, storing and retrieving cached values,
	 * and flushing specific cache items. It supports flexible caching operations
	 * to improve performance in WordPress applications.
	 */
	class Cache {

		/**
		 * Default cache expiration time in seconds.
		 */
		const DEFAULT_EXPIRATION = DAY_IN_SECONDS;

		/**
		 * Generate a unique cache key based on the calling function, its arguments, and an optional prefix.
		 *
		 * @param string $prefix  Optional. A prefix for the cache key. Default is 'edd_cache'.
		 * @param mixed  ...$args The arguments to include in the cache key.
		 *
		 * @return string The generated cache key.
		 */
		public static function cache_key( string $prefix = '', ...$args ): string {
			$prefix    = strtolower( trim( $prefix ) );
			$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 );
			$caller    = $backtrace[1];

			$key_parts = [
				$caller['class'] ?? '',
				$caller['function'],
				$args
			];

			return $prefix . '_' . md5( serialize( $key_parts ) );
		}

		/**
		 * Get a value from cache. If it doesn't exist, compute it using the provided callback.
		 *
		 * @param string   $key        The cache key.
		 * @param callable $callback   The function to compute the value if not found in cache.
		 * @param int      $expiration Optional. Time until expiration in seconds. Default is DEFAULT_EXPIRATION.
		 *
		 * @return mixed The cached or computed value, which may be null.
		 */
		public static function remember( string $key, callable $callback, int $expiration = self::DEFAULT_EXPIRATION ) {
			$cached_value = get_transient( $key );

			if ( false !== $cached_value ) {
				return $cached_value;
			}

			$value = $callback();

			// Cache the value even if it's null
			set_transient( $key, $value, $expiration );

			return $value;
		}

		/**
		 * Flush a specific cache item.
		 *
		 * @param string $key The cache key to flush.
		 *
		 * @return bool True if successful, false otherwise.
		 */
		public static function forget( string $key ): bool {
			return delete_transient( $key );
		}

		/**
		 * Check if a cache key exists and is not expired.
		 *
		 * @param string $key The cache key to check.
		 *
		 * @return bool True if the cache key exists and is not expired, false otherwise.
		 */
		public static function has( string $key ): bool {
			return false !== get_transient( $key );
		}

		/**
		 * Store a value in cache.
		 *
		 * @param string $key        The cache key.
		 * @param mixed  $value      The value to store.
		 * @param int    $expiration Optional. Time until expiration in seconds. Default is DEFAULT_EXPIRATION.
		 *
		 * @return bool True if successful, false otherwise.
		 */
		public static function put( string $key, $value, int $expiration = self::DEFAULT_EXPIRATION ): bool {
			return set_transient( $key, $value, $expiration );
		}

		/**
		 * Retrieve a value from cache.
		 *
		 * @param string $key     The cache key.
		 * @param mixed  $default Optional. Default value to return if the key doesn't exist.
		 *
		 * @return mixed The cached value or the default value if the key doesn't exist.
		 */
		public static function get( string $key, $default = null ) {
			$value = get_transient( $key );

			return false === $value ? $default : $value;
		}

	}
endif;