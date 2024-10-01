<?php
/**
 * Hashing Utility Class for WordPress
 *
 * This class provides utility methods for hashing operations,
 * including password hashing, verification, and general-purpose hashing.
 *
 * @package       ArrayPress/Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        AI Assistant
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Hashing` is defined, and if not, define it.
 */
if ( ! class_exists( 'Hashing' ) ) :

	/**
	 * Hashing Utility Class
	 */
	class Hashing {

		/**
		 * Hash a password using WordPress's password hashing function.
		 *
		 * @param string $password The password to hash.
		 *
		 * @return string The hashed password.
		 */
		public static function hash_password( string $password ): string {
			return wp_hash_password( $password );
		}

		/**
		 * Verify a password against a hash.
		 *
		 * @param string $password The password to check.
		 * @param string $hash     The hash to check against.
		 *
		 * @return bool Whether the password is correct.
		 */
		public static function verify_password( string $password, string $hash ): bool {
			return wp_check_password( $password, $hash );
		}

		/**
		 * Generate a one-way hash of a string.
		 *
		 * @param string $data The data to hash.
		 *
		 * @return string The hashed data.
		 */
		public static function hash( string $data ): string {
			return wp_hash( $data );
		}

		/**
		 * Generate a keyed hash of a string using HMAC.
		 *
		 * @param string $data The data to hash.
		 * @param string $key  The key to use for hashing.
		 *
		 * @return string The keyed hash.
		 */
		public static function hmac( string $data, string $key ): string {
			return hash_hmac( 'sha256', $data, $key );
		}

		/**
		 * Generate a nonce for a given action.
		 *
		 * @param string $action The action for which to generate the nonce.
		 * @return string The nonce.
		 */
		public static function create_nonce( string $action ): string {
			return wp_create_nonce( $action );
		}

		/**
		 * Verify a nonce.
		 *
		 * @param string $nonce  The nonce to verify.
		 * @param string $action The action to check the nonce against.
		 *
		 * @return bool True if the nonce is valid, false otherwise.
		 */
		public static function verify_nonce( string $nonce, string $action ): bool {
			$result = wp_verify_nonce( $nonce, $action );

			return $result !== false;
		}

		/**
		 * Generate a unique hash for caching purposes.
		 *
		 * @param mixed $data The data to generate a cache key for.
		 *
		 * @return string The cache key.
		 */
		public static function generate_cache_key( $data ): string {
			return md5( maybe_serialize( $data ) );
		}
	}

endif;