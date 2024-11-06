<?php
/**
 * Trait: Terms Query
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

trait Query {

	/**
	 * Get unused terms for a taxonomy.
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param array  $args     Optional. Additional get_terms() arguments.
	 *
	 * @return array An array of unused term objects.
	 */
	public static function get_unused( string $taxonomy, array $args = [] ): array {
		$defaults  = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		];
		$args      = wp_parse_args( $args, $defaults );
		$all_terms = get_terms( $args );

		if ( is_wp_error( $all_terms ) ) {
			return [];
		}

		return array_filter( $all_terms, function ( $term ) {
			return $term->count === 0;
		} );
	}

	/**
	 * Get the most used terms for a taxonomy.
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param int    $limit    Optional. The number of terms to return. Default 10.
	 *
	 * @return array An array of term objects sorted by usage count.
	 */
	public static function get_most_used( string $taxonomy, int $limit = 10 ): array {
		$args = [
			'taxonomy'   => $taxonomy,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $limit,
			'hide_empty' => false,
		];

		$terms = get_terms( $args );

		return is_wp_error( $terms ) ? [] : $terms;
	}

	/**
	 * Find terms that are used together frequently.
	 *
	 * Analyzes the co-occurrence of terms within the same taxonomy and returns
	 * pairs of terms that frequently appear together on the same posts.
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param int    $limit    Optional. The number of term pairs to return. Default 10.
	 *
	 * @return array An array of term pairs with their co-occurrence count.
	 */
	public static function get_related( string $taxonomy, int $limit = 10 ): array {
		global $wpdb;

		$query = $wpdb->prepare( "
            SELECT t1.term_id as term1_id, t2.term_id as term2_id, COUNT(*) as count
            FROM {$wpdb->term_relationships} tr1
            JOIN {$wpdb->term_relationships} tr2 ON tr1.object_id = tr2.object_id
            JOIN {$wpdb->term_taxonomy} tt1 ON tr1.term_taxonomy_id = tt1.term_taxonomy_id
            JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
            JOIN {$wpdb->terms} t1 ON tt1.term_id = t1.term_id
            JOIN {$wpdb->terms} t2 ON tt2.term_id = t2.term_id
            WHERE tt1.taxonomy = %s 
            AND tt2.taxonomy = %s 
            AND t1.term_id < t2.term_id
            GROUP BY t1.term_id, t2.term_id
            ORDER BY count DESC
            LIMIT %d
        ", $taxonomy, $taxonomy, $limit );

		return $wpdb->get_results( $query );
	}

	/**
	 * Get terms belonging to multiple taxonomies.
	 *
	 * @param array $taxonomies Array of taxonomy names.
	 * @param array $args       Optional. Additional get_terms() arguments.
	 *
	 * @return array An array of term objects.
	 */
	protected static function get_by_taxonomies( array $taxonomies, array $args = [] ): array {
		if ( empty( $taxonomies ) ) {
			return [];
		}

		$defaults = [
			'taxonomy'   => $taxonomies,
			'hide_empty' => false,
		];

		$args  = wp_parse_args( $args, $defaults );
		$terms = get_terms( $args );

		return is_wp_error( $terms ) ? [] : $terms;
	}

	/**
	 * Get terms that match a specific criteria across taxonomies.
	 *
	 * @param array  $taxonomies  Array of taxonomy names.
	 * @param string $field       The field to match against (e.g., 'name', 'slug').
	 * @param mixed  $value       The value to match.
	 * @param array  $args        Optional. Additional get_terms() arguments.
	 *
	 * @return array An array of matching term objects.
	 */
	protected static function get_by_field_across_taxonomies( array $taxonomies, string $field, $value, array $args = [] ): array {
		$all_terms = self::get_by_taxonomies( $taxonomies, $args );

		return array_filter( $all_terms, function ( $term ) use ( $field, $value ) {
			return isset( $term->$field ) && $term->$field === $value;
		} );
	}

	/**
	 * Get hierarchical terms organized by their parent-child relationships.
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param array  $args     Optional. Additional get_terms() arguments.
	 *
	 * @return array An array of term objects organized hierarchically.
	 */
	protected static function get_hierarchical( string $taxonomy, array $args = [] ): array {
		$defaults = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		];

		$args  = wp_parse_args( $args, $defaults );
		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		return self::build_term_tree( $terms );
	}

	/**
	 * Build a hierarchical tree of terms.
	 *
	 * @param array $terms       Array of term objects.
	 * @param int   $parent_id   Parent term ID.
	 *
	 * @return array Hierarchical array of terms.
	 */
	private static function build_term_tree( array $terms, int $parent_id = 0 ): array {
		$tree = [];

		foreach ( $terms as $term ) {
			if ( $term->parent === $parent_id ) {
				$children = self::build_term_tree( $terms, $term->term_id );
				if ( $children ) {
					$term->children = $children;
				}
				$tree[] = $term;
			}
		}

		return $tree;
	}

}