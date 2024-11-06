<?php
/**
 * Trait: Terms Object Relations
 *
 * This trait provides methods for managing relationships between WordPress terms
 * and objects (posts, etc.), including getting, setting, adding, and removing terms,
 * as well as managing term relationships across multiple objects.
 *
 * @package     ArrayPress\Utils\Traits\Terms
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Terms;

use ArrayPress\Utils\Common\Extract;
use WP_Error;
use WP_Term;

trait ObjectRelations {

	/**
	 * Get terms for an object and a taxonomy
	 *
	 * @param mixed      $object   Object or object ID
	 * @param string     $taxonomy Taxonomy name
	 * @param array|null $args     Optional. Array of arguments for wp_get_object_terms
	 *
	 * @return array|null Array of term objects or null on failure
	 */
	public static function get_for_object( $object, string $taxonomy, ?array $args = null ): ?array {
		$object_id = Extract::object_id( $object );
		if ( $object_id === null || ! taxonomy_exists( $taxonomy ) ) {
			return null;
		}

		// Default arguments
		$default_args = [
			'fields'                 => 'all_with_object_id',
			'orderby'                => 'name',
			'order'                  => 'ASC',
			'update_term_meta_cache' => true
		];

		// Merge provided args with defaults
		$args = $args !== null ? array_merge( $default_args, $args ) : $default_args;

		// Get the terms
		$terms = wp_get_object_terms( $object_id, $taxonomy, $args );

		// Return null on error
		if ( is_wp_error( $terms ) ) {
			return null;
		}

		// Cache the results
		wp_cache_set( "object_terms_{$object_id}_{$taxonomy}", $terms, 'terms' );

		return $terms;
	}

	/**
	 * Set taxonomy terms for a specific object
	 *
	 * @param mixed  $object   Object or object ID
	 * @param string $taxonomy Taxonomy name
	 * @param array  $terms    Array of term IDs, slugs, or names
	 * @param bool   $append   Optional. Whether to append new terms to existing terms. Default false
	 *
	 * @return array|null Array of term taxonomy IDs or null on failure
	 */
	public static function set_for_object( $object, string $taxonomy, array $terms = [], bool $append = false ): ?array {
		$object_id = Extract::object_id( $object );
		if ( $object_id === null || ! taxonomy_exists( $taxonomy ) ) {
			return null;
		}

		// If terms is empty, delete all terms
		if ( empty( $terms ) ) {
			wp_delete_object_term_relationships( $object_id, $taxonomy );
			clean_object_term_cache( $object_id, $taxonomy );
			return [];
		}

		// Process terms to handle different input formats
		$processed_terms = array_map( function ( $term ) {
			if ( is_numeric( $term ) ) {
				return absint( $term );
			}
			return $term;
		}, $terms );

		// Set the terms
		$result = wp_set_object_terms( $object_id, $processed_terms, $taxonomy, $append );
		if ( is_wp_error( $result ) ) {
			return null;
		}

		// Clean term cache
		clean_object_term_cache( $object_id, $taxonomy );

		return $result;
	}

	/**
	 * Add single term to an object
	 *
	 * @param mixed      $object   Object or object ID
	 * @param string     $taxonomy Taxonomy name
	 * @param int|string $term     Term ID, slug, or name
	 *
	 * @return array|null Array of term taxonomy IDs or null on failure
	 */
	public static function add_term( $object, string $taxonomy, $term ): ?array {
		return self::set_for_object( $object, $taxonomy, [ $term ], true );
	}

	/**
	 * Remove single term from an object
	 *
	 * @param mixed      $object   Object or object ID
	 * @param string     $taxonomy Taxonomy name
	 * @param int|string $term     Term ID, slug, or name
	 *
	 * @return array|null Array of remaining term taxonomy IDs or null on failure
	 */
	public static function remove_term( $object, string $taxonomy, $term ): ?array {
		$object_id = Extract::object_id( $object );
		if ( $object_id === null ) {
			return null;
		}

		// Get current terms
		$current_terms = wp_get_object_terms( $object_id, $taxonomy, [ 'fields' => 'ids' ] );
		if ( is_wp_error( $current_terms ) ) {
			return null;
		}

		// If term is numeric, assume it's an ID
		if ( is_numeric( $term ) ) {
			$term_id       = absint( $term );
			$current_terms = array_diff( $current_terms, [ $term_id ] );
		} else {
			// Get term by slug or name
			$term_obj = get_term_by( 'slug', $term, $taxonomy ) ?: get_term_by( 'name', $term, $taxonomy );
			if ( $term_obj ) {
				$current_terms = array_diff( $current_terms, [ $term_obj->term_id ] );
			}
		}

		return self::set_for_object( $object_id, $taxonomy, $current_terms, false );
	}

	/**
	 * Check if object has specific term
	 *
	 * @param mixed      $object   Object or object ID
	 * @param string     $taxonomy Taxonomy name
	 * @param int|string $term     Term ID, slug, or name
	 *
	 * @return bool True if has term, false if not
	 */
	public static function has_term( $object, string $taxonomy, $term ): bool {
		$object_id = Extract::object_id( $object );
		if ( $object_id === null ) {
			return false;
		}

		return has_term( $term, $taxonomy, $object_id );
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
		$object_id = Extract::object_id( $object );
		if ( $object_id === null ) {
			return false;
		}

		foreach ( $taxonomies as $taxonomy ) {
			if ( taxonomy_exists( $taxonomy ) ) {
				self::set_for_object( $object_id, $taxonomy, [] );
			}
		}

		return true;
	}

}