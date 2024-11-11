<?php
/**
 * Trait: Terms Sanitize
 *
 * This trait provides advanced query functionality for WordPress terms,
 * including retrieving unused terms, most used terms, and related terms analysis.
 *
 * @package     ArrayPress\Utils\Traits\Terms
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Terms;

use WP_Term;

trait Options {

	/**
	 * Get term options suitable for form fields.
	 *
	 * @param string $taxonomy     The taxonomy name.
	 * @param array  $args         Optional. Additional get_terms() arguments.
	 * @param bool   $hierarchical Optional. Whether to return hierarchical options. Default false.
	 * @param int    $depth        Optional. Maximum depth for hierarchical display. Default 0 (all levels).
	 *
	 * @return array<string|int, array|string> Array of term options.
	 */
	public static function get_options( string $taxonomy, array $args = [], bool $hierarchical = false, int $depth = 0 ): array {
		if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
			return [];
		}

		$defaults = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		];

		$args  = wp_parse_args( $args, $defaults );
		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		if ( ! $hierarchical ) {
			return array_reduce( $terms, function ( $options, $term ) {
				$options[ $term->term_id ] = $term->name;

				return $options;
			}, [] );
		}

		// Get terms in hierarchical format
		$hierarchical_terms = self::build_hierarchical_options( $terms, 0, $depth );

		// Format the hierarchical terms into options
		return array_reduce( $hierarchical_terms, function ( $options, $term ) {
			$indent                            = str_repeat( '— ', $term['depth'] );
			$options[ $term['term']->term_id ] = $indent . $term['term']->name;

			return $options;
		}, [] );
	}

	/**
	 * Build hierarchical term options array.
	 *
	 * @param array<int, WP_Term> $terms         All terms.
	 * @param int                 $parent        Parent ID.
	 * @param int                 $max_depth     Maximum depth (0 for unlimited).
	 * @param int                 $current_depth Current depth level.
	 *
	 * @return array<int, array{term: WP_Term, depth: int}> Hierarchical terms array.
	 */
	private static function build_hierarchical_options( array $terms, int $parent = 0, int $max_depth = 0, int $current_depth = 0 ): array {
		$hierarchical = [];

		foreach ( $terms as $term ) {
			if ( $term->parent === $parent ) {
				// Skip if we've reached max depth
				if ( $max_depth > 0 && $current_depth >= $max_depth ) {
					continue;
				}

				$hierarchical[] = [
					'term'  => $term,
					'depth' => $current_depth,
				];

				// Get children
				$children = self::build_hierarchical_options(
					$terms,
					$term->term_id,
					$max_depth,
					$current_depth + 1
				);

				$hierarchical = array_merge( $hierarchical, $children );
			}
		}

		return $hierarchical;
	}

}