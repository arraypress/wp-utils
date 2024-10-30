<?php
/**
 * WordPress Table Name Utilities
 *
 * This class provides utility functions for retrieving WordPress table names.
 * It includes methods for getting both standard and metadata table names for
 * different object types in WordPress.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Database;

/**
 * Check if the class `Table` is defined, and if not, define it.
 */
if ( ! class_exists( 'Table' ) ) :

	/**
	 * WordPress Table Name Utility Class
	 *
	 * Provides static methods for retrieving WordPress table names.
	 */
	class Table {

		/**
		 * Get the table name for a given object type.
		 *
		 * @param string $object_type The type of object (e.g., 'post', 'user', 'term', 'comment', 'product', 'order').
		 *
		 * @return string|null The table name. Null on failure.
		 */
		public static function get_table_name( string $object_type ): ?string {
			global $wpdb;

			// Check for standard WordPress tables
			$standard_tables = [
				'post'    => $wpdb->posts,
				'user'    => $wpdb->users,
				'term'    => $wpdb->terms,
				'comment' => $wpdb->comments,
			];

			if ( isset( $standard_tables[ $object_type ] ) ) {
				return $standard_tables[ $object_type ];
			}

			// Array of possible variations to check
			$variations = [
				$object_type,
				rtrim( $object_type, 's' ),
				$object_type . 's',
				$object_type . 'es',
				substr( $object_type, 0, - 1 ) . 'ies', // For plurals like "category" -> "categories"
			];

			// Check for custom registered tables
			foreach ( $variations as $variation ) {
				if ( isset( $wpdb->$variation ) ) {
					return $wpdb->$variation;
				}
			}

			// If no table is found, check if there's a constant defined for this table
			$constant_name = strtoupper( $wpdb->prefix . $object_type );
			if ( defined( $constant_name ) ) {
				return constant( $constant_name );
			}

			return null;
		}

		/**
		 * Get the name of the metadata table for a given object type.
		 *
		 * @param string $meta_type The type of object metadata is for (e.g., 'post', 'user', 'term', 'comment').
		 *
		 * @return string The name of the metadata table. Null on failure.
		 */
		public static function get_meta_table_name( string $meta_type ): ?string {
			global $wpdb;

			// Check for standard WordPress metadata tables
			$standard_meta_tables = [
				'post'    => $wpdb->postmeta,
				'user'    => $wpdb->usermeta,
				'term'    => $wpdb->termmeta,
				'comment' => $wpdb->commentmeta,
			];

			if ( isset( $standard_meta_tables[ $meta_type ] ) ) {
				return $standard_meta_tables[ $meta_type ];
			}

			// Array of possible variations to check for custom meta tables
			$variations = [
				$meta_type . 'meta',
				$meta_type . '_meta',
				rtrim( $meta_type, 's' ) . 'meta',
				rtrim( $meta_type, 's' ) . '_meta',
			];

			// Check for custom registered meta tables
			foreach ( $variations as $variation ) {
				if ( isset( $wpdb->$variation ) ) {
					return $wpdb->$variation;
				}
			}

			// If no table is found, check if there's a constant defined for this meta table
			$constant_name = strtoupper( $wpdb->prefix . $meta_type . '_META' );
			if ( defined( $constant_name ) ) {
				return constant( $constant_name );
			}

			return null;
		}

	}

endif;