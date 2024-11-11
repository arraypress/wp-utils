<?php
/**
 * Trait: Term Utility
 *
 * This trait provides utility methods for WordPress terms, including
 * operations like term merging and other maintenance tasks.
 *
 * @package     ArrayPress\Utils\Traits\Term
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Term;

use WP_Error;

trait Utility {

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
			return new WP_Error( 'invalid_term', 'Invalid term ID provided.' );
		}

		$posts = get_posts( [
			'numberposts' => -1,
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