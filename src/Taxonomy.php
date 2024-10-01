<?php
/**
 * Taxonomy Utilities for WordPress
 *
 * This class provides utility functions for working with WordPress taxonomies. It includes
 * methods for retrieving terms based on various identifiers, fetching child terms, merging terms,
 * and obtaining term hierarchies. Additionally, it offers functionality for retrieving taxonomy-related
 * options, calculating term depth, and finding unused or most-used terms within a taxonomy.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Taxonomy` is defined, and if not, define it.
 */
if ( ! class_exists( 'Taxonomy' ) ) :

	/**
	 * Taxonomy Utilities
	 *
	 * Provides utility functions for working with WordPress taxonomies, such as
	 * retrieving terms by identifiers, fetching term options, obtaining child terms,
	 * managing term relationships, and getting term hierarchies. It also includes
	 * functions for finding related terms, calculating term depth, and determining
	 * whether a term has children.
	 */
	class Taxonomy {

		/**
		 * Check if a taxonomy exists.
		 *
		 * @param string $taxonomy The taxonomy name to check.
		 *
		 * @return bool True if the taxonomy exists, false otherwise.
		 */
		public static function exists( string $taxonomy ): bool {
			return taxonomy_exists( $taxonomy );
		}

		/**
		 * Get the capabilities for a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return object|null The capabilities object for the taxonomy, or null if not found.
		 */
		public static function get_capabilities( string $taxonomy ): ?object {
			$tax_obj = get_taxonomy( $taxonomy );

			return $tax_obj ? $tax_obj->cap : null;
		}

		/**
		 * Check if a taxonomy has a specific capability.
		 *
		 * @param string $taxonomy   The taxonomy name.
		 * @param string $capability The capability to check for.
		 *
		 * @return bool True if the taxonomy has the specified capability, false otherwise.
		 */
		public static function has_capability( string $taxonomy, string $capability ): bool {
			$tax_obj = get_taxonomy( $taxonomy );

			if ( ! $tax_obj || ! isset( $tax_obj->cap ) ) {
				return false;
			}

			return isset( $tax_obj->cap->$capability ) && $tax_obj->cap->$capability;
		}

		/**
		 * Check if a taxonomy is hierarchical.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return bool True if the taxonomy is hierarchical, false otherwise.
		 */
		public static function is_hierarchical( string $taxonomy ): bool {
			return is_taxonomy_hierarchical( $taxonomy );
		}

		/**
		 * Get the object types associated with a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return array An array of object types associated with the taxonomy.
		 */
		public static function get_object_types( string $taxonomy ): array {
			$tax_obj = get_taxonomy( $taxonomy );

			return $tax_obj ? $tax_obj->object_type : [];
		}

		/**
		 * Check if a taxonomy is registered for a specific post type.
		 *
		 * @param string $taxonomy  The taxonomy name.
		 * @param string $post_type The post type name.
		 *
		 * @return bool True if the taxonomy is registered for the post type, false otherwise.
		 */
		public static function is_registered_for_post_type( string $taxonomy, string $post_type ): bool {
			$taxonomies = get_object_taxonomies( $post_type, 'names' );

			return in_array( $taxonomy, $taxonomies, true );
		}

		/**
		 * Check if a taxonomy is associated with any post types.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return bool True if the taxonomy is associated with any post types, false otherwise.
		 */
		public static function is_in_use( string $taxonomy ): bool {
			$post_types = self::get_post_types( $taxonomy );

			return ! empty( $post_types );
		}

		/**
		 * Get post types associated with a specific taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return array An array of post type names associated with the taxonomy.
		 */
		public static function get_post_types( string $taxonomy ): array {
			$tax_obj = get_taxonomy( $taxonomy );

			return $tax_obj ? $tax_obj->object_type : [];
		}

		/**
		 * Get the labels for a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return object|null The labels object for the taxonomy, or null if not found.
		 */
		public static function get_labels( string $taxonomy ): ?object {
			$tax_obj = get_taxonomy( $taxonomy );

			return $tax_obj ? $tax_obj->labels : null;
		}

		/**
		 * Check if a taxonomy supports a specific feature.
		 *
		 * @param string $taxonomy The taxonomy name.
		 * @param string $feature  The feature to check for (e.g., 'hierarchical', 'public', 'show_ui').
		 *
		 * @return bool True if the taxonomy supports the feature, false otherwise.
		 */
		public static function supports( string $taxonomy, string $feature ): bool {
			$tax_obj = get_taxonomy( $taxonomy );

			return $tax_obj && isset( $tax_obj->$feature ) && $tax_obj->$feature;
		}

		/**
		 * Get the rewrite rules for a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return array|false The rewrite rules for the taxonomy, or false if not found or not using rewrite.
		 */
		public static function get_rewrite_rules( string $taxonomy ) {
			$tax_obj = get_taxonomy( $taxonomy );

			return $tax_obj && $tax_obj->rewrite ? $tax_obj->rewrite : false;
		}

		/**
		 * Get the default term for a taxonomy.
		 *
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return int|false The term ID of the default term, or false if not set.
		 */
		public static function get_default_term( string $taxonomy ) {
			return get_option( 'default_term_' . $taxonomy, false );
		}

	}

endif;