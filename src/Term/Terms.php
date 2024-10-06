<?php
/**
 * Terms Utility Class for WordPress
 *
 * This class provides a comprehensive set of utility functions for working with multiple WordPress terms.
 * It offers methods for searching, retrieving, analyzing, and modifying terms across taxonomies.
 * The class is designed to simplify common term-related operations and extend WordPress's built-in term functionality.
 *
 * Key features include:
 * - Term searching and retrieval based on various identifiers
 * - Related terms analysis
 * - Unused terms detection
 * - Term merging and bulk operations
 * - Term field extraction and manipulation
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Term;

/**
 * Check if the class `Terms` is defined, and if not, define it.
 */
if ( ! class_exists( 'Terms' ) ) :

	/**
	 * Terms Utility Class
	 *
	 * Provides a suite of methods for working with multiple WordPress terms,
	 * focusing on term retrieval, analysis, and bulk operations across taxonomies.
	 */
	class Terms {

		/** Retrieval ************************************************************/

		/**
		 * Search for terms based on a search term and additional arguments.
		 *
		 * @param string $search_term The search term.
		 * @param string $taxonomy    The taxonomy to search within.
		 * @param array  $args        Additional arguments for get_terms.
		 *
		 * @return array An array of term options in label/value format.
		 */
		public static function search( string $search_term, string $taxonomy, array $args = [] ): array {
			$options = [];

			if ( empty( $search_term ) || empty( $taxonomy ) ) {
				return $options;
			}

			$default_args = [
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			];

			$args           = wp_parse_args( $args, $default_args );
			$args['search'] = $search_term;

			$terms = get_terms( $args );

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				return $options;
			}

			foreach ( $terms as $term ) {
				if ( ! isset( $term->name, $term->term_id ) ) {
					continue;
				}

				$options[] = [
					'label' => esc_html( $term->name ),
					'value' => esc_attr( $term->term_id ),
				];
			}

			return $options;
		}

		/**
		 * Get an array of unique term IDs or term objects based on provided term names, slugs, or IDs.
		 *
		 * @param array  $term_identifiers An array of term names, slugs, IDs, or term objects to search for.
		 * @param string $taxonomy         The name of the taxonomy to search within.
		 * @param bool   $return_objects   Whether to return term objects instead of term IDs. Default is false.
		 *
		 * @return array An array of unique term IDs as integers or term objects.
		 */
		public static function get_by_identifiers( array $term_identifiers, string $taxonomy, bool $return_objects = false ): array {
			if ( empty( $term_identifiers ) || empty( $taxonomy ) ) {
				return [];
			}

			$unique_terms = [];

			foreach ( $term_identifiers as $identifier ) {
				if ( empty( $identifier ) ) {
					continue;
				}

				if ( is_object( $identifier ) && isset( $identifier->term_id ) ) {
					$term = get_term( $identifier->term_id, $taxonomy );
				} elseif ( is_numeric( $identifier ) ) {
					$term = get_term_by( 'id', $identifier, $taxonomy );
				} else {
					$term = get_term_by( 'slug', $identifier, $taxonomy );
					if ( ! $term ) {
						$term = get_term_by( 'name', $identifier, $taxonomy );
					}
				}

				if ( $term && ! is_wp_error( $term ) ) {
					$unique_terms[ $term->term_id ] = $term;
				}
			}

			if ( $return_objects ) {
				return array_values( $unique_terms );
			}

			return array_map( 'intval', array_keys( $unique_terms ) );
		}

		/**
		 * Get specified fields from terms based on provided term names, slugs, or IDs.
		 *
		 * @param array  $term_identifiers An array of term names, slugs, IDs, or term objects to search for.
		 * @param string $taxonomy         The name of the taxonomy to search within.
		 * @param string $field            The field to extract from each term. Default is 'slug'.
		 *
		 * @return array An array of specified field values from the terms.
		 */
		public static function get_fields( array $term_identifiers, string $taxonomy, string $field = 'slug' ): array {
			$terms = self::get_by_identifiers( $term_identifiers, $taxonomy, true );

			return wp_list_pluck( $terms, $field );
		}

		/** Analysis ************************************************************/

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

		/** Relationships ***********************************************************/

		/**
		 * Find terms that are used together frequently.
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
                WHERE tt1.taxonomy = %s AND tt2.taxonomy = %s AND t1.term_id < t2.term_id
                GROUP BY t1.term_id, t2.term_id
                ORDER BY count DESC
                LIMIT %d
            ", $taxonomy, $taxonomy, $limit );

			return $wpdb->get_results( $query );
		}

		/** Modification ************************************************************/

		/**
		 * Bulk update term meta for multiple terms.
		 *
		 * @param array  $term_ids   An array of term IDs.
		 * @param string $meta_key   The meta key to update.
		 * @param mixed  $meta_value The new meta value.
		 *
		 * @return array An array of results, with term IDs as keys and update results as values.
		 */
		public static function bulk_update_meta( array $term_ids, string $meta_key, $meta_value ): array {
			$results = [];

			foreach ( $term_ids as $term_id ) {
				$results[ $term_id ] = update_term_meta( $term_id, $meta_key, $meta_value );
			}

			return $results;
		}

		/**
		 * Bulk delete terms.
		 *
		 * @param array  $term_ids An array of term IDs to delete.
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return array An array of results, with term IDs as keys and deletion results as values.
		 */
		public static function bulk_delete( array $term_ids, string $taxonomy ): array {
			$results = [];

			foreach ( $term_ids as $term_id ) {
				$results[ $term_id ] = wp_delete_term( $term_id, $taxonomy );
			}

			return $results;
		}

		/** Utility ************************************************************/

		/**
		 * Get the common ancestors of multiple terms.
		 *
		 * @param array  $term_ids An array of term IDs.
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return array An array of common ancestor term IDs.
		 */
		public static function get_common_ancestors( array $term_ids, string $taxonomy ): array {
			if ( empty( $term_ids ) ) {
				return [];
			}

			$all_ancestors = [];

			foreach ( $term_ids as $term_id ) {
				$ancestors       = get_ancestors( $term_id, $taxonomy );
				$all_ancestors[] = $ancestors;
			}

			return ! empty( $all_ancestors ) ? array_intersect( ...$all_ancestors ) : [];
		}

		/**
		 * Check if all given terms belong to the same taxonomy.
		 *
		 * @param array $term_ids An array of term IDs.
		 *
		 * @return bool True if all terms belong to the same taxonomy, false otherwise.
		 */
		public static function are_in_same_taxonomy( array $term_ids ): bool {
			if ( empty( $term_ids ) ) {
				return false;
			}

			$taxonomies = [];

			foreach ( $term_ids as $term_id ) {
				$term = get_term( $term_id );
				if ( is_wp_error( $term ) ) {
					return false;
				}
				$taxonomies[] = $term->taxonomy;
			}

			return count( array_unique( $taxonomies ) ) === 1;
		}

		/**
		 * Get terms for an object and a taxonomy
		 *
		 * @param mixed  $object   Object or object ID
		 * @param string $taxonomy Taxonomy name
		 *
		 * @return array|false Array of term objects or false on failure
		 */
		public static function get_for_object( $object, string $taxonomy ) {
			$object_id = is_object( $object ) && ! empty( $object->ID )
				? $object->ID
				: absint( $object );

			if ( empty( $object_id ) ) {
				return false;
			}

			return wp_get_object_terms( $object_id, $taxonomy, [
				'fields' => 'all_with_object_id'
			] );
		}

		/**
		 * Set taxonomy terms for a specific object
		 *
		 * @param mixed  $object   Object or object ID
		 * @param string $taxonomy Taxonomy name
		 * @param array  $terms    Array of term IDs or names
		 * @param bool   $append   Whether to append new terms to existing terms
		 *
		 * @return array|bool Array of term taxonomy IDs or false on failure
		 */
		public static function set_for_object( $object, string $taxonomy, array $terms = [], bool $append = true ) {
			$object_id = is_object( $object ) && ! empty( $object->ID )
				? $object->ID
				: absint( $object );

			if ( empty( $object_id ) ) {
				return false;
			}

			if ( empty( $terms ) ) {
				wp_delete_object_term_relationships( $object_id, $taxonomy );

				return true;
			} else {
				$result = wp_set_object_terms( $object_id, $terms, $taxonomy, $append );
				clean_object_term_cache( $object_id, $taxonomy );

				return $result;
			}
		}

		/**
		 * Get term IDs for an object and a taxonomy
		 *
		 * @param mixed  $object   Object or object ID
		 * @param string $taxonomy Taxonomy name
		 *
		 * @return array|false Array of term IDs or false on failure
		 */
		public static function get_term_ids_for_object( $object, string $taxonomy ) {
			$terms = self::get_for_object( $object, $taxonomy );

			if ( empty( $terms ) ) {
				return false;
			}

			return wp_list_pluck( $terms, 'term_id' );
		}

		/**
		 * Delete all terms for a specific object across multiple taxonomies
		 *
		 * @param mixed $object     Object or object ID
		 * @param array $taxonomies Array of taxonomy names
		 *
		 * @return bool True on success, false on failure
		 */
		public static function delete_all_object_terms( $object, array $taxonomies ): bool {
			$object_id = is_object( $object ) && ! empty( $object->ID )
				? $object->ID
				: absint( $object );

			if ( empty( $object_id ) ) {
				return false;
			}

			foreach ( $taxonomies as $taxonomy ) {
				self::set_for_object( $object_id, $taxonomy, [] );
			}

			return true;
		}

	}

endif;