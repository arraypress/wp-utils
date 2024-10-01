<?php
/**
 * Term Utility Class for WordPress
 *
 * This class provides a comprehensive set of utility functions for working with individual WordPress terms.
 * It offers methods for navigating term hierarchies, retrieving related terms, and analyzing term relationships.
 * The class is designed to simplify common term-related operations and extend WordPress's built-in term functionality.
 *
 * Key features include:
 * - Hierarchical term navigation (parents, children, siblings, cousins)
 * - Term relationship analysis (descendants, ancestors)
 * - Term path and depth calculation
 * - Utility methods for term validation and information retrieval
 *
 * @package       ArrayPress/Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

// Prevent direct file access
use WP_Term;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Check if the class `Term` is defined, and if not, define it.
 */
if ( ! class_exists( 'Term' ) ) :

	/**
	 * Term Utility Class
	 *
	 * Provides a suite of methods for working with individual WordPress terms,
	 * focusing on term relationships, hierarchies, and common term operations.
	 */
	class Term {

		/** Hierarchical Navigation *********************************************/

		/**
		 * Get all child terms of a given term.
		 *
		 * @param int    $parent_id The parent term ID.
		 * @param string $taxonomy  The taxonomy name.
		 * @param array  $args      Optional. Additional get_terms() arguments.
		 *
		 * @return array An array of child term objects.
		 */
		public static function get_children( int $parent_id, string $taxonomy, array $args = [] ): array {
			$defaults = [
				'taxonomy'   => $taxonomy,
				'child_of'   => $parent_id,
				'hide_empty' => false,
			];
			$args     = wp_parse_args( $args, $defaults );
			$terms    = get_terms( $args );

			return is_wp_error( $terms ) ? [] : $terms;
		}

		/**
		 * Get term siblings.
		 *
		 * @param int    $term_id  The term ID.
		 * @param string $taxonomy The taxonomy name.
		 * @param array  $args     Optional. Additional get_terms() arguments.
		 *
		 * @return array An array of sibling term objects.
		 */
		public static function get_siblings( int $term_id, string $taxonomy, array $args = [] ): array {
			$term = get_term( $term_id, $taxonomy );
			if ( is_wp_error( $term ) ) {
				return [];
			}

			$defaults = [
				'taxonomy'   => $taxonomy,
				'parent'     => $term->parent,
				'exclude'    => $term_id,
				'hide_empty' => false,
			];
			$args     = wp_parse_args( $args, $defaults );
			$siblings = get_terms( $args );

			return is_wp_error( $siblings ) ? [] : $siblings;
		}

		/**
		 * Get the next sibling term.
		 *
		 * @param int    $term_id  The term ID.
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return WP_Term|null The next sibling term or null if not found.
		 */
		public static function get_next_sibling( int $term_id, string $taxonomy ): ?WP_Term {
			$term = get_term( $term_id, $taxonomy );
			if ( is_wp_error( $term ) ) {
				return null;
			}

			$siblings = self::get_siblings( $term_id, $taxonomy, [ 'orderby' => 'name', 'order' => 'ASC' ] );
			$found    = false;
			foreach ( $siblings as $sibling ) {
				if ( $found ) {
					return $sibling;
				}
				if ( $sibling->term_id === $term_id ) {
					$found = true;
				}
			}

			return null;
		}

		/**
		 * Get the previous sibling term.
		 *
		 * @param int    $term_id  The term ID.
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return WP_Term|null The previous sibling term or null if not found.
		 */
		public static function get_previous_sibling( int $term_id, string $taxonomy ): ?WP_Term {
			$term = get_term( $term_id, $taxonomy );
			if ( is_wp_error( $term ) ) {
				return null;
			}

			$siblings = self::get_siblings( $term_id, $taxonomy, [ 'orderby' => 'name', 'order' => 'ASC' ] );
			$previous = null;
			foreach ( $siblings as $sibling ) {
				if ( $sibling->term_id === $term_id ) {
					return $previous;
				}
				$previous = $sibling;
			}

			return null;
		}

		/**
		 * Get all terms that share the same parent as the given term.
		 *
		 * @param int    $term_id  The term ID.
		 * @param string $taxonomy The taxonomy name.
		 * @param array  $args     Optional. Additional get_terms() arguments.
		 *
		 * @return array An array of term objects that share the same parent.
		 */
		public static function get_cousins( int $term_id, string $taxonomy, array $args = [] ): array {
			$term = get_term( $term_id, $taxonomy );
			if ( is_wp_error( $term ) ) {
				return [];
			}

			$defaults = [
				'taxonomy'   => $taxonomy,
				'parent'     => $term->parent,
				'hide_empty' => false,
			];
			$args     = wp_parse_args( $args, $defaults );
			$cousins  = get_terms( $args );

			return is_wp_error( $cousins ) ? [] : $cousins;
		}

		/** Hierarchy Analysis **************************************************/

		/**
		 * Get the term hierarchy as a nested array.
		 *
		 * @param string $taxonomy The taxonomy name.
		 * @param int    $parent   The parent term ID. Use 0 for top-level terms.
		 * @param array  $args     Optional. Additional get_terms() arguments.
		 *
		 * @return array An array of term objects with 'children' key for nested terms.
		 */
		public static function get_hierarchy( string $taxonomy, int $parent = 0, array $args = [] ): array {
			$defaults = [
				'taxonomy'   => $taxonomy,
				'parent'     => $parent,
				'hide_empty' => false,
			];
			$args     = wp_parse_args( $args, $defaults );
			$terms    = get_terms( $args );

			if ( is_wp_error( $terms ) ) {
				return [];
			}

			$hierarchy = [];
			foreach ( $terms as $term ) {
				$term->children = self::get_hierarchy( $taxonomy, $term->term_id, $args );
				$hierarchy[]    = $term;
			}

			return $hierarchy;
		}

		/**
		 * Get the full hierarchical path of a term.
		 *
		 * @param int    $term_id   The term ID.
		 * @param string $taxonomy  The taxonomy name.
		 * @param string $separator The separator between terms in the path.
		 *
		 * @return string The full path of the term.
		 */
		public static function get_path( int $term_id, string $taxonomy, string $separator = ' > ' ): string {
			$term = get_term( $term_id, $taxonomy );
			if ( is_wp_error( $term ) ) {
				return '';
			}

			$path = [ $term->name ];
			while ( $term->parent !== 0 ) {
				$term = get_term( $term->parent, $taxonomy );
				if ( is_wp_error( $term ) ) {
					break;
				}
				array_unshift( $path, $term->name );
			}

			return implode( $separator, $path );
		}

		/**
		 * Calculate the depth of a term in the hierarchy.
		 *
		 * @param int    $term_id  The term ID.
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return int The depth of the term (0 for top-level terms).
		 */
		public static function get_depth( int $term_id, string $taxonomy ): int {
			$ancestors = get_ancestors( $term_id, $taxonomy );

			return count( $ancestors );
		}

		/**
		 * Get the root ancestor of a term.
		 *
		 * @param int    $term_id  The term ID.
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return array|WP_Term|\WP_Error|null
		 */
		public static function get_root_ancestor( int $term_id, string $taxonomy ) {
			$ancestors = get_ancestors( $term_id, $taxonomy );
			if ( empty( $ancestors ) ) {
				return get_term( $term_id, $taxonomy );
			}

			return get_term( end( $ancestors ), $taxonomy );
		}

		/** Relationship Checks *************************************************/

		/**
		 * Check if a term is a descendant of another term.
		 *
		 * @param int    $term_id     The term ID to check.
		 * @param int    $ancestor_id The potential ancestor term ID.
		 * @param string $taxonomy    The taxonomy name.
		 *
		 * @return bool True if the term is a descendant, false otherwise.
		 */
		public static function is_descendant( int $term_id, int $ancestor_id, string $taxonomy ): bool {
			$ancestors = get_ancestors( $term_id, $taxonomy );

			return in_array( $ancestor_id, $ancestors, true );
		}

		/**
		 * Check if a term has children.
		 *
		 * @param int    $term_id  The term ID.
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return bool True if the term has children, false otherwise.
		 */
		public static function has_children( int $term_id, string $taxonomy ): bool {
			$children = get_terms( [
				'taxonomy'   => $taxonomy,
				'parent'     => $term_id,
				'hide_empty' => false,
				'number'     => 1,
			] );

			return ! is_wp_error( $children ) && ! empty( $children );
		}

		/** Utility ************************************************************/

		/**
		 * Merge two terms, reassigning all posts from one term to another.
		 *
		 * @param int    $from_term_id The ID of the term to merge from.
		 * @param int    $to_term_id   The ID of the term to merge into.
		 * @param string $taxonomy     The taxonomy name.
		 *
		 * @return bool|WP_Error True on success, WP_Error on failure.
		 */
		public static function merge( int $from_term_id, int $to_term_id, string $taxonomy ) {
			$from_term = get_term( $from_term_id, $taxonomy );
			$to_term   = get_term( $to_term_id, $taxonomy );

			if ( is_wp_error( $from_term ) || is_wp_error( $to_term ) ) {
				return new \WP_Error( 'invalid_term', 'Invalid term ID provided.' );
			}

			$posts = get_posts( [
				'numberposts' => - 1,
				'tax_query'   => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $from_term_id,
					],
				],
			] );

			foreach ( $posts as $post ) {
				wp_remove_object_terms( $post->ID, $from_term_id, $taxonomy );
				wp_add_object_terms( $post->ID, $to_term_id, $taxonomy );
			}

			return wp_delete_term( $from_term_id, $taxonomy );
		}

	}

endif;