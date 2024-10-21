<?php
/**
 * Constants Utilities
 *
 * This class provides utility functions for defining, managing, and interacting with constants
 * in a WordPress plugin or theme context. It offers methods for safely defining constants,
 * setting up common plugin constants, retrieving constant values, and performing various
 * constant-related operations to streamline development and improve code organization.
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
 * Check if the class `Constant` is defined, and if not, define it.
 */
if ( ! class_exists( 'Constant' ) ) :

	/**
	 * Constants Utility Class
	 *
	 * This class provides methods for defining, managing, and interacting with constants in a WordPress plugin or theme.
	 */
	class Constant {

		/**
		 * Define a constant if it's not already defined.
		 *
		 * @param string $name  The name of the constant.
		 * @param mixed  $value The value of the constant.
		 *
		 * @return bool True if the constant was defined, false if it was already defined.
		 */
		public static function define( string $name, $value ): bool {
			if ( ! defined( $name ) ) {
				define( $name, $value );

				return true;
			}

			return false;
		}

		/**
		 * Define multiple constants at once.
		 *
		 * @param array $constants An associative array of constant names and values.
		 *
		 * @return array An array of constant names that were successfully defined.
		 */
		public static function define_multiple( array $constants ): array {
			$defined = [];
			foreach ( $constants as $name => $value ) {
				if ( self::define( $name, $value ) ) {
					$defined[] = $name;
				}
			}

			return $defined;
		}

		/**
		 * Undefine a constant if it's defined.
		 * Note: This only works for constants defined using define(), not const.
		 *
		 * @param string $name The name of the constant.
		 *
		 * @return bool True if the constant was undefined, false if it wasn't defined.
		 */
		public static function undefine( string $name ): bool {
			if ( defined( $name ) ) {
				return runkit_constant_remove( $name );
			}

			return false;
		}

		/**
		 * Get the value of a defined constant.
		 *
		 * @param string $name    The name of the constant.
		 * @param mixed  $default The default value to return if the constant is not defined.
		 *
		 * @return mixed The value of the constant or the default value.
		 */
		public static function get( string $name, $default = null ) {
			return defined( $name ) ? constant( $name ) : $default;
		}

		/**
		 * Get all defined constants with a specific prefix.
		 *
		 * @param string $prefix The prefix to filter constants by.
		 *
		 * @return array An associative array of constant names and their values.
		 */
		public static function get_all_with_prefix( string $prefix ): array {
			$constants      = get_defined_constants( true );
			$user_constants = $constants['user'] ?? [];

			return array_filter( $user_constants, function ( $key ) use ( $prefix ) {
				return strpos( $key, $prefix ) === 0;
			}, ARRAY_FILTER_USE_KEY );
		}

		/**
		 * Get the names of all constants with a specific prefix.
		 *
		 * @param string $prefix The prefix to filter constants by.
		 *
		 * @return array An array of constant names.
		 */
		public static function get_all_names_by_prefix( string $prefix ): array {
			return array_keys( self::get_all_with_prefix( $prefix ) );
		}

		/**
		 * Set up common plugin constants.
		 *
		 * @param string $prefix  The prefix for the constants.
		 * @param string $file    The main plugin file path.
		 * @param string $version The plugin version.
		 */
		public static function setup_plugin( string $prefix, string $file, string $version ): void {
			$prefix = strtoupper( $prefix );

			self::define( "{$prefix}_PLUGIN_VERSION", $version );
			self::define( "{$prefix}_PLUGIN_FILE", $file );
			self::define( "{$prefix}_PLUGIN_BASE", plugin_basename( $file ) );
			self::define( "{$prefix}_PLUGIN_DIR", plugin_dir_path( $file ) );
			self::define( "{$prefix}_PLUGIN_URL", plugin_dir_url( $file ) );
		}

		/**
		 * Set up additional constants.
		 *
		 * @param string $prefix    The prefix for the constants.
		 * @param array  $constants An associative array of constant names and values.
		 */
		public static function setup_additional( string $prefix, array $constants ): void {
			$prefix = strtoupper( $prefix );

			foreach ( $constants as $name => $value ) {
				self::define( "{$prefix}_{$name}", $value );
			}
		}

		/**
		 * Check if all given constants are defined.
		 *
		 * @param array $names An array of constant names.
		 *
		 * @return bool True if all constants are defined, false otherwise.
		 */
		public static function all_defined( array $names ): bool {
			foreach ( $names as $name ) {
				if ( ! defined( $name ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Check if any of the given constants are defined.
		 *
		 * @param array $names An array of constant names.
		 *
		 * @return bool True if any of the constants are defined, false if none are defined.
		 */
		public static function any_defined( array $names ): bool {
			foreach ( $names as $name ) {
				if ( defined( $name ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if a constant is defined.
		 *
		 * @param string $name The name of the constant.
		 *
		 * @return bool True if the constant is defined, false otherwise.
		 */
		public static function is_defined( string $name ): bool {
			return defined( $name );
		}

		/**
		 * Check if a constant's value is equal to a given value.
		 *
		 * @param string $name  The name of the constant.
		 * @param mixed  $value The value to compare against.
		 *
		 * @return bool True if the constant is defined and its value is equal to the given value, false otherwise.
		 */
		public static function is_equal( string $name, $value ): bool {
			return defined( $name ) && constant( $name ) === $value;
		}

	}
endif;