<?php
/**
 * User Capabilities Trait
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

use ArrayPress\Utils\Capabilities\Capabilities as CoreCapabilities;
use ArrayPress\Utils\Capabilities\Capability as CoreCapability;

trait Capabilities {

	/**
	 * Get all capabilities for the user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array Array of capabilities.
	 */
	public static function get_capabilities( int $user_id ): array {
		return CoreCapabilities::get_for_user( $user_id );
	}

	/**
	 * Check if the user has a specific capability.
	 *
	 * @param int    $user_id    User ID.
	 * @param string $capability The capability to check.
	 *
	 * @return bool Whether the user has the capability.
	 */
	public static function has_capability( int $user_id, string $capability ): bool {
		return CoreCapability::user_has( $capability, $user_id );
	}

	/**
	 * Check if the user has any of the specified capabilities.
	 *
	 * @param int          $user_id      User ID.
	 * @param array|string $capabilities Single capability or array of capabilities to check.
	 *
	 * @return bool True if user has any of the capabilities.
	 */
	public static function has_any_capability( int $user_id, $capabilities ): bool {
		return CoreCapabilities::user_has_any( $capabilities, $user_id );
	}

	/**
	 * Check if the user has all the specified capabilities.
	 *
	 * @param int          $user_id      User ID.
	 * @param array|string $capabilities Single capability or array of capabilities to check.
	 *
	 * @return bool True if user has all the capabilities.
	 */
	public static function has_all_capabilities( int $user_id, $capabilities ): bool {
		return CoreCapabilities::user_has_all( $capabilities, $user_id );
	}

}