<?php
/**
 * Raw Terms Database Utility Class
 *
 * This utility class provides direct database access to term data, bypassing WordPress's taxonomy system.
 * It's particularly useful in scenarios where:
 * - WordPress taxonomies haven't been registered yet (timing issues)
 * - You need to access term data before 'init' hook
 * - Performance optimization is needed for large datasets
 * - Direct database access is preferred over WordPress API
 *
 * Note: Use with caution as this bypasses WordPress filters and actions.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Terms;

/**
 * Class Term
 *
 * Utility functions for working with a specific Term.
 */
class Raw {

	/**
	 * Get the most popular terms for a taxonomy.
	 *
	 * @param string $taxonomy The taxonomy name
	 * @param array  $args     Optional arguments
	 *                         'limit'      => (int)    Number of terms to return (default: 5)
	 *                         'hide_empty' => (bool)   Whether to hide terms with count of 0 (default: true)
	 *                         'cache'      => (bool)   Whether to cache results (default: true)
	 *                         'cache_time' => (int)    Cache duration in seconds (default: HOUR_IN_SECONDS)
	 *
	 * @return array Array of term objects with term_id, name, and count
	 */
	public static function get_popular_terms( string $taxonomy, array $args = [] ): array {
		global $wpdb;

		// Parse arguments
		$defaults = [
			'limit'      => 5,
			'hide_empty' => true,
			'cache'      => true,
			'cache_time' => HOUR_IN_SECONDS,
		];
		$args     = wp_parse_args( $args, $defaults );

		// Check cache if enabled
		if ( $args['cache'] ) {
			$cache_key    = 'popular_terms_' . $taxonomy . '_' . md5( serialize( $args ) );
			$cached_terms = wp_cache_get( $cache_key, 'arraypress_terms' );

			if ( false !== $cached_terms ) {
				return $cached_terms;
			}
		}

		// Build the query
		$query = "SELECT t.term_id, t.name, t.slug, tt.count 
                 FROM {$wpdb->terms} t 
                 INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
                 WHERE tt.taxonomy = %s";

		if ( $args['hide_empty'] ) {
			$query .= " AND tt.count > 0";
		}

		$query .= " ORDER BY tt.count DESC";

		if ( $args['limit'] > 0 ) {
			$query .= " LIMIT %d";
			$query = $wpdb->prepare( $query, $taxonomy, $args['limit'] );
		} else {
			$query = $wpdb->prepare( $query, $taxonomy );
		}

		$terms = $wpdb->get_results( $query );

		if ( empty( $terms ) ) {
			if ( $args['cache'] ) {
				wp_cache_set( $cache_key, [], 'arraypress_terms', $args['cache_time'] );
			}

			return [];
		}

		// Standardize term objects
		$terms = array_map( function ( $term ) {
			return (object) [
				'term_id' => (int) $term->term_id,
				'name'    => $term->name,
				'slug'    => $term->slug,
				'count'   => (int) $term->count
			];
		}, $terms );

		// Cache if enabled
		if ( $args['cache'] ) {
			wp_cache_set( $cache_key, $terms, 'arraypress_terms', $args['cache_time'] );
		}

		return $terms;
	}

	/**
	 * Get terms by their IDs.
	 *
	 * @param array  $term_ids Array of term IDs
	 * @param string $taxonomy Optional taxonomy to filter by
	 * @param array  $args     Optional arguments for caching
	 *
	 * @return array Array of term objects
	 */
	public static function get_terms_by_ids( array $term_ids, string $taxonomy = '', array $args = [] ): array {
		global $wpdb;

		if ( empty( $term_ids ) ) {
			return [];
		}

		$defaults = [
			'cache'      => true,
			'cache_time' => HOUR_IN_SECONDS,
		];
		$args     = wp_parse_args( $args, $defaults );

		// Check cache
		if ( $args['cache'] ) {
			$cache_key    = 'terms_by_ids_' . md5( serialize( $term_ids ) . $taxonomy );
			$cached_terms = wp_cache_get( $cache_key, 'arraypress_terms' );

			if ( false !== $cached_terms ) {
				return $cached_terms;
			}
		}

		$term_ids = array_map( 'intval', $term_ids );
		$where    = "WHERE t.term_id IN (" . implode( ',', $term_ids ) . ")";

		if ( ! empty( $taxonomy ) ) {
			$where .= $wpdb->prepare( " AND tt.taxonomy = %s", $taxonomy );
		}

		$query = "SELECT t.term_id, t.name, t.slug, tt.taxonomy, tt.count 
                 FROM {$wpdb->terms} t 
                 INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
                 {$where}";

		$terms = $wpdb->get_results( $query );

		if ( empty( $terms ) ) {
			if ( $args['cache'] ) {
				wp_cache_set( $cache_key, [], 'arraypress_terms', $args['cache_time'] );
			}

			return [];
		}

		// Standardize term objects
		$terms = array_map( function ( $term ) {
			return (object) [
				'term_id'  => (int) $term->term_id,
				'name'     => $term->name,
				'slug'     => $term->slug,
				'taxonomy' => $term->taxonomy,
				'count'    => (int) $term->count
			];
		}, $terms );

		// Cache results
		if ( $args['cache'] ) {
			wp_cache_set( $cache_key, $terms, 'arraypress_terms', $args['cache_time'] );
		}

		return $terms;
	}

	/**
	 * Search terms directly from the database.
	 *
	 * @param string $search   Search term
	 * @param string $taxonomy Taxonomy name
	 * @param array  $args     Optional arguments
	 *
	 * @return array Array of matching term objects
	 */
	public static function search_terms( string $search, string $taxonomy, array $args = [] ): array {
		global $wpdb;

		$defaults = [
			'limit'      => 20,
			'hide_empty' => false,
			'cache'      => true,
			'cache_time' => HOUR_IN_SECONDS,
		];
		$args     = wp_parse_args( $args, $defaults );

		// Check cache
		if ( $args['cache'] ) {
			$cache_key    = 'term_search_' . md5( $search . $taxonomy . serialize( $args ) );
			$cached_terms = wp_cache_get( $cache_key, 'arraypress_terms' );

			if ( false !== $cached_terms ) {
				return $cached_terms;
			}
		}

		$like = '%' . $wpdb->esc_like( $search ) . '%';

		$query = "SELECT t.term_id, t.name, t.slug, tt.count 
                 FROM {$wpdb->terms} t 
                 INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
                 WHERE tt.taxonomy = %s 
                 AND (t.name LIKE %s OR t.slug LIKE %s)";

		if ( $args['hide_empty'] ) {
			$query .= " AND tt.count > 0";
		}

		$query .= " ORDER BY t.name ASC";

		if ( $args['limit'] > 0 ) {
			$query .= " LIMIT %d";
			$query = $wpdb->prepare( $query, $taxonomy, $like, $like, $args['limit'] );
		} else {
			$query = $wpdb->prepare( $query, $taxonomy, $like, $like );
		}

		$terms = $wpdb->get_results( $query );

		if ( empty( $terms ) ) {
			if ( $args['cache'] ) {
				wp_cache_set( $cache_key, [], 'arraypress_terms', $args['cache_time'] );
			}

			return [];
		}

		// Standardize term objects
		$terms = array_map( function ( $term ) {
			return (object) [
				'term_id' => (int) $term->term_id,
				'name'    => $term->name,
				'slug'    => $term->slug,
				'count'   => (int) $term->count
			];
		}, $terms );

		// Cache results
		if ( $args['cache'] ) {
			wp_cache_set( $cache_key, $terms, 'arraypress_terms', $args['cache_time'] );
		}

		return $terms;
	}

}