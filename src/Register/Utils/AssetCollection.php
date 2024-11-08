<?php
/**
 * Asset Collection
 *
 * Manages collections of assets with methods for adding, retrieving, and checking existence.
 *
 * @package     ArrayPress/Utils/Register
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     2.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Register\Utils;

class AssetCollection {

	/**
	 * Collection of assets
	 *
	 * @var array
	 */
	protected array $items = [];

	/**
	 * Add an asset to the collection
	 *
	 * @param string $handle Asset handle
	 * @param array  $args   Asset configuration
	 *
	 * @return void
	 */
	public function add( string $handle, array $args ): void {
		$this->items[ $handle ] = $args;
	}

	/**
	 * Get an asset from the collection
	 *
	 * @param string $handle Asset handle
	 *
	 * @return array|null Asset configuration or null if not found
	 */
	public function get( string $handle ): ?array {
		return $this->items[ $handle ] ?? null;
	}

	/**
	 * Get all assets in the collection
	 *
	 * @return array All assets
	 */
	public function all(): array {
		return $this->items;
	}

	/**
	 * Check if an asset exists in the collection
	 *
	 * @param string $handle Asset handle
	 *
	 * @return bool Whether the asset exists
	 */
	public function has( string $handle ): bool {
		return isset( $this->items[ $handle ] );
	}

}