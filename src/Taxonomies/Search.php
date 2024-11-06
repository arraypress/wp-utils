<?php
/**
 * Search Utility Classes for WordPress
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Taxonomies;

use ArrayPress\Utils\Common\Sanitize;
use WP_Taxonomy;

/**
 * Class Taxonomies
 *
 * Search utility for taxonomies.
 */
class Search {

	/**
	 * @var bool Whether to show internal taxonomies.
	 */
	private bool $show_internal;

	/**
	 * @var bool Whether to show public taxonomies only.
	 */
	private bool $public_only;

	/**
	 * @var array Additional arguments for get_taxonomies().
	 */
	private array $args;

	/**
	 * Constructor for the Taxonomies class.
	 *
	 * @param bool  $show_internal Whether to show internal taxonomies. Default false.
	 * @param bool  $public_only   Whether to show public taxonomies only. Default true.
	 * @param array $args          Additional arguments for get_taxonomies().
	 */
	public function __construct(
		bool $show_internal = false,
		bool $public_only = true,
		array $args = []
	) {
		$this->show_internal = $show_internal;
		$this->public_only   = $public_only;
		$this->args          = $args;
	}

	/**
	 * Set whether to show internal taxonomies.
	 *
	 * @param bool $show_internal Whether to show internal taxonomies.
	 *
	 * @return self
	 */
	public function set_show_internal( bool $show_internal ): self {
		$this->show_internal = $show_internal;

		return $this;
	}

	/**
	 * Set whether to show public taxonomies only.
	 *
	 * @param bool $public_only Whether to show public taxonomies only.
	 *
	 * @return self
	 */
	public function set_public_only( bool $public_only ): self {
		$this->public_only = $public_only;

		return $this;
	}

	/**
	 * Set additional arguments.
	 *
	 * @param array $args Additional arguments for get_taxonomies().
	 *
	 * @return self
	 */
	public function set_args( array $args ): self {
		$this->args = $args;

		return $this;
	}

	/**
	 * Perform a search for taxonomies.
	 *
	 * @param string $search         The search string.
	 * @param array  $args           Optional. Additional arguments to pass to the search.
	 * @param bool   $return_objects Whether to return taxonomy objects. Default false.
	 *
	 * @return array An array of formatted search results or taxonomy objects.
	 */
	public function get_results( string $search = '', array $args = [], bool $return_objects = false ): array {
		$search = Sanitize::search( $search );

		$args = wp_parse_args( $args, [
			'public'   => $this->public_only,
			'show_ui'  => true,
			'_builtin' => $this->show_internal,
		] );

		$args       = array_merge( $args, $this->args );
		$taxonomies = get_taxonomies( $args, 'objects' );

		if ( ! empty( $search ) ) {
			$taxonomies = array_filter( $taxonomies, function ( $taxonomy ) use ( $search ) {
				return stripos( $taxonomy->label, $search ) !== false ||
				       stripos( $taxonomy->name, $search ) !== false;
			} );
		}

		return $return_objects ? $taxonomies : $this->format_results( $taxonomies );
	}

	/**
	 * Format search results into an array of options.
	 *
	 * @param WP_Taxonomy[] $taxonomies Array of taxonomy objects.
	 *
	 * @return array An array of formatted search results.
	 */
	private function format_results( array $taxonomies ): array {
		if ( empty( $taxonomies ) ) {
			return [];
		}

		$options = [];
		foreach ( $taxonomies as $taxonomy ) {
			$options[] = [
				'value' => $taxonomy->name,
				'label' => $taxonomy->label ?? $taxonomy->name,
			];
		}

		return $options;
	}

}