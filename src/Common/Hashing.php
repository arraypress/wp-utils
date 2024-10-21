<?php
/**
 * Hashing Utility Class for WordPress
 *
 * This class provides utility methods for hashing operations,
 * including password hashing, verification, and general-purpose hashing.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        AI Assistant
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

/**
 * Check if the class `Hashing` is defined, and if not, define it.
 */
if ( ! class_exists( 'Hashing' ) ) :

	/**
	 * Hashing Utility Class
	 *
	 * This class provides utility methods for handling hashing-related operations.
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
		 *
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

		/**
		 * Calculate the hash of a file.
		 *
		 * @param string $file_path The path to the file.
		 * @param string $algo      The hashing algorithm to use (default: 'sha256').
		 *
		 * @return string|null The hash of the file, or null on failure.
		 */
		public static function file_hash( string $file_path, string $algo = 'sha256' ): ?string {
			if ( ! file_exists( $file_path ) ) {
				return null;
			}

			if ( ! in_array( $algo, hash_algos() ) ) {
				return null;
			}

			return hash_file( $algo, $file_path ) ?: null;
		}

		/**
		 * Calculate hashes for multiple files.
		 *
		 * @param array  $file_paths An array of file paths.
		 * @param string $algo       The hashing algorithm to use (default: 'sha256').
		 *
		 * @return array An associative array of file paths and their hashes, or null values for failures.
		 */
		public static function multi_file_hash( array $file_paths, string $algo = 'sha256' ): array {
			$results = [];
			foreach ( $file_paths as $path ) {
				$results[ $path ] = self::file_hash( $path, $algo );
			}

			return $results;
		}

		/**
		 * Calculate the hash of a WordPress attachment.
		 *
		 * @param int    $attachment_id The ID of the attachment.
		 * @param string $algo          The hashing algorithm to use (default: 'sha256').
		 *
		 * @return string|null The hash of the attachment, or null on failure.
		 */
		public static function attachment_hash( int $attachment_id, string $algo = 'sha256' ): ?string {
			$file_path = get_attached_file( $attachment_id );
			if ( ! $file_path ) {
				return null;
			}

			return self::file_hash( $file_path, $algo );
		}

		/**
		 * Calculate hashes for multiple WordPress attachments.
		 *
		 * @param array  $attachment_ids An array of attachment IDs.
		 * @param string $algo           The hashing algorithm to use (default: 'sha256').
		 *
		 * @return array An associative array of attachment IDs and their hashes, or null values for failures.
		 */
		public static function multi_attachment_hash( array $attachment_ids, string $algo = 'sha256' ): array {
			$results = [];
			foreach ( $attachment_ids as $id ) {
				$results[ $id ] = self::attachment_hash( $id, $algo );
			}

			return $results;
		}

		/**
		 * Calculate multiple hashes of a file or WordPress attachment.
		 *
		 * @param string|int $file          The file path or attachment ID.
		 * @param array      $algos         An array of hashing algorithms to use.
		 * @param bool       $is_attachment Whether the file is a WordPress attachment.
		 *
		 * @return array An associative array of calculated hashes, or an empty array on failure.
		 */
		public static function multi_hash(
			$file, array $algos = [
			'md5',
			'sha1',
			'sha256'
		], bool $is_attachment = false
		): array {
			$file_path = $is_attachment ? get_attached_file( $file ) : $file;

			if ( ! $file_path || ! file_exists( $file_path ) ) {
				return [];
			}

			$hashes = [];
			foreach ( $algos as $algo ) {
				if ( in_array( $algo, hash_algos() ) ) {
					$hash = hash_file( $algo, $file_path );
					if ( $hash !== false ) {
						$hashes[ $algo ] = $hash;
					}
				}
			}

			return $hashes;
		}


	}

endif;