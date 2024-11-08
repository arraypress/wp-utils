<?php
/**
 * Helper function to register roles and capabilities for WordPress
 *
 * @package       ArrayPress/WordPress-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

use ArrayPress\Utils\Register\Roles;
use ArrayPress\Utils\Register\Assets;
use Exception;
use InvalidArgumentException;

if ( ! function_exists( __NAMESPACE__ . '\register_roles' ) ) :
	/**
	 * Register custom roles and capabilities for WordPress.
	 *
	 * @param array         $roles_and_caps        An associative array of roles and their capabilities.
	 * @param bool          $force_single_instance Whether to force a single instance of each role.
	 * @param callable|null $error_callback        Callback function for error handling.
	 *
	 * @return Roles|null Returns the RoleCapabilityManager instance or null if an exception occurs.
	 */
	function register_roles(
		array $roles_and_caps,
		bool $force_single_instance = false,
		?callable $error_callback = null
	): ?Roles {
		try {
			$manager = new Roles( $roles_and_caps, $force_single_instance );

			// Add new roles
			foreach ( $roles_and_caps as $role_slug => $config ) {
				if ( isset( $config['display_name'] ) ) {
					$manager->add_role( $role_slug, $config['display_name'], $config['capabilities'] ?? [] );
				}
			}

			// Add capabilities to existing roles
			$manager->add_caps();

			return $manager;
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			// Handle the exception or log it if needed
			return null; // Return null on failure
		}
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\register_assets' ) ) :
	/**
	 * Register custom assets (scripts and styles) for WordPress.
	 *
	 * Example usage:
	 * $assets = [
	 *     [
	 *         'handle' => 'my-script',
	 *         'src'    => 'js/script.js',
	 *         'type'   => 'script',
	 *         'deps'   => ['jquery'],
	 *         'async'  => true
	 *     ],
	 *     [
	 *         'handle' => 'my-style',
	 *         'src'    => 'css/style.css',
	 *         'type'   => 'style',
	 *         'media'  => 'all'
	 *     ]
	 * ];
	 *
	 * @param string        $file           Main plugin/theme file path
	 * @param array         $assets         Array of assets to register
	 * @param array         $config         Optional. Configuration settings for the Assets manager
	 *                                      Supports all options from Assets::parse_config()
	 * @param callable|null $error_callback Optional. Callback function for error handling
	 *
	 * @return Assets|null Returns the Assets manager instance or null if registration fails
	 * @throws InvalidArgumentException If the file path is invalid
	 */
	function register_assets(
		string $file,
		array $assets,
		array $config = [],
		?callable $error_callback = null
	): ?Assets {
		try {
			// Validate file exists
			if ( ! file_exists( $file ) ) {
				throw new InvalidArgumentException( "Invalid file path: {$file}" );
			}

			// Initialize the Assets manager
			$manager = new Assets( $file, $config );

			// Register all assets at once
			if ( ! empty( $assets ) ) {
				$manager->register( $assets );
			}

			return $manager;
		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			return null;
		}
	}
endif;