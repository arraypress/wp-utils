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

namespace ArrayPress\Utils\PostTypes;

use ArrayPress\Utils\Common\Sanitize;
use WP_Post_Type;

/**
 * Class PostTypes
 *
 * Search utility for post types.
 */
class Search {

	/**
	 * @var bool Whether to show internal post types.
	 */
	private bool $show_internal;

	/**
	 * @var bool Whether to show public post types only.
	 */
	private bool $public_only;

	/**
	 * @var array Additional arguments for get_post_types().
	 */
	private array $args;

	/**
	 * Constructor for the PostTypes class.
	 *
	 * @param bool  $show_internal Whether to show internal post types. Default false.
	 * @param bool  $public_only   Whether to show public post types only. Default true.
	 * @param array $args          Additional arguments for get_post_types().
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
	 * Set whether to show internal post types.
	 *
	 * @param bool $show_internal Whether to show internal post types.
	 *
	 * @return self
	 */
	public function set_show_internal( bool $show_internal ): self {
		$this->show_internal = $show_internal;

		return $this;
	}

	/**
	 * Set whether to show public post types only.
	 *
	 * @param bool $public_only Whether to show public post types only.
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
	 * @param array $args Additional arguments for get_post_types().
	 *
	 * @return self
	 */
	public function set_args( array $args ): self {
		$this->args = $args;

		return $this;
	}

	/**
	 * Perform a search for post types.
	 *
	 * @param string $search         The search string.
	 * @param array  $args           Optional. Additional arguments to pass to the search.
	 * @param bool   $return_objects Whether to return post type objects. Default false.
	 *
	 * @return array An array of formatted search results or post type objects.
	 */
	public function get_results( string $search = '', array $args = [], bool $return_objects = false ): array {
		$search     = Sanitize::search( $search );
		$args       = wp_parse_args( $args, [
			'public'   => $this->public_only,
			'show_ui'  => true,
			'_builtin' => $this->show_internal,
		] );
		$args       = array_merge( $args, $this->args );
		$post_types = get_post_types( $args, 'objects' );

		if ( ! empty( $search ) ) {
			$post_types = array_filter( $post_types, function ( $post_type ) use ( $search ) {
				return str_contains( strtolower( $post_type->label ), strtolower( $search ) ) ||
				       str_contains( strtolower( $post_type->name ), strtolower( $search ) );
			} );
		}

		return $return_objects ? $post_types : $this->format_results( $post_types );
	}

	/**
	 * Format search results into an array of options.
	 *
	 * @param WP_Post_Type[] $post_types Array of post type objects.
	 *
	 * @return array An array of formatted search results.
	 */
	private function format_results( array $post_types ): array {
		if ( empty( $post_types ) ) {
			return [];
		}

		$options = [];
		foreach ( $post_types as $post_type ) {
			$options[] = [
				'value' => $post_type->name,
				'label' => $post_type->label ?? $post_type->name,
			];
		}

		return $options;
	}

}