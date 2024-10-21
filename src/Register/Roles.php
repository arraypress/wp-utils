<?php
/**
 * Role and Capability Manager for WordPress
 *
 * @package     ArrayPress\WordPress\Roles
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @since       1.0.0
 * @author      ArrayPress Team
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Register;

use WP_Role;

/**
 * Check if the class `Roles` is defined, and if not, define it.
 */
if ( ! class_exists( 'Roles' ) ) :
	/**
	 * Class Roles
	 *
	 * Manages WordPress roles and capabilities.
	 */
	class Roles {

		/**
		 * @var array An associative array of role capabilities with their configurations.
		 */
		private array $capabilities;

		/**
		 * @var bool Whether to force a single instance of each role.
		 */
		private bool $force_single_instance;

		/**
		 * Constructor.
		 *
		 * @param array $capabilities          An associative array of role capabilities with their configurations.
		 * @param bool  $force_single_instance If set to true, ensure single instance of each role.
		 */
		public function __construct( array $capabilities, bool $force_single_instance = false ) {
			$this->capabilities          = $capabilities;
			$this->force_single_instance = $force_single_instance;
		}

		/**
		 * Add new capabilities to specific roles.
		 *
		 * @return void
		 */
		public function add_caps(): void {
			foreach ( $this->capabilities as $role_slug => $data ) {
				$role = $this->get_role( $role_slug );
				if ( ! $role || ! $this->are_valid_capabilities( $data['capabilities'] ) ) {
					continue;
				}

				foreach ( $data['capabilities'] as $capability ) {
					$role->add_cap( $capability );
				}

				if ( $this->force_single_instance ) {
					$this->clear_other_instances( $role_slug );
				}
			}
		}

		/**
		 * Remove capabilities from specific roles.
		 *
		 * @return void
		 */
		public function remove_caps(): void {
			foreach ( $this->capabilities as $role_slug => $data ) {
				$role = $this->get_role( $role_slug );
				if ( ! $role || ! $this->are_valid_capabilities( $data['capabilities'] ) ) {
					continue;
				}

				foreach ( $data['capabilities'] as $capability ) {
					$role->remove_cap( $capability );
				}
			}
		}

		/**
		 * Adds a new role to WordPress.
		 *
		 * @param string $role_slug    The slug for the role.
		 * @param string $display_name The display name for the role.
		 * @param array  $capabilities Capabilities for the new role.
		 *
		 * @return void
		 */
		public function add_role( string $role_slug, string $display_name, array $capabilities = [] ): void {
			if ( ! $this->role_exists( $role_slug ) ) {
				add_role( $role_slug, $display_name, $capabilities );
			}
		}

		/**
		 * Removes a role from WordPress.
		 *
		 * @param string $role_slug The slug for the role to remove.
		 *
		 * @return void
		 */
		public function remove_role( string $role_slug ): void {
			if ( $this->role_exists( $role_slug ) ) {
				remove_role( $role_slug );
			}
		}

		/**
		 * Check if all capabilities exist in WordPress.
		 *
		 * @param array $capabilities Capabilities to check.
		 *
		 * @return bool True if all capabilities exist, false otherwise.
		 */
		private function are_valid_capabilities( array $capabilities ): bool {
			$existing_capabilities = array_map( 'strtolower', array_keys( wp_roles()->roles ) );
			$missing_capabilities  = array_diff( array_map( 'strtolower', $capabilities ), $existing_capabilities );

			return empty( $missing_capabilities );
		}

		/**
		 * Clear other instances of a role, leaving only one.
		 *
		 * @param string $role_slug Role slug to clear instances for.
		 *
		 * @return void
		 */
		private function clear_other_instances( string $role_slug ): void {
			$roles = wp_roles()->get_names();
			foreach ( $roles as $role_name => $role_caption ) {
				if ( $role_name !== $role_slug ) {
					remove_role( $role_name );
				}
			}
		}

		/**
		 * Get a role object by its slug.
		 *
		 * @param string $role_slug The role slug.
		 *
		 * @return WP_Role|null The role object or null if not found.
		 */
		private function get_role( string $role_slug ): ?WP_Role {
			return wp_roles()->get_role( $role_slug );
		}

		/**
		 * Checks if a role exists in WordPress.
		 *
		 * @param string $role_slug Role slug to check.
		 *
		 * @return bool True if role exists, false otherwise.
		 */
		private function role_exists( string $role_slug ): bool {
			return $this->get_role( $role_slug ) instanceof WP_Role;
		}
	}
endif;