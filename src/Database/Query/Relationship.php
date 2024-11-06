<?php
/**
 * Relationship Query Builder for WordPress
 *
 * This class provides a fluent interface for constructing relationship queries in WordPress.
 * It supports both simple static queries and more complex chainable queries, allowing
 * developers to easily filter posts based on their relationships with other posts,
 * including specific relationships, any relationships, or no relationships.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Database\Query;

class Relationship {

	/** @var array The constructed relationship query */
	private array $query = [];

	/**
	 * Create a new RelationshipQuery instance.
	 *
	 * @return self
	 */
	public static function new(): self {
		return new self();
	}

	/**
	 * Add a related post condition to the query.
	 *
	 * @param string    $relationship The name of the relationship.
	 * @param int|array $post_ids     The related post ID(s).
	 * @param string    $direction    The direction of the relationship ('to' or 'from').
	 *
	 * @return self
	 */
	public function related( string $relationship, $post_ids, string $direction = 'to' ): self {
		$this->query[] = [
			'relation'  => $relationship,
			'direction' => $direction,
			'post_ids'  => is_array( $post_ids ) ? $post_ids : [ $post_ids ],
		];

		return $this;
	}

	/**
	 * Add a condition for posts related to the given post(s).
	 *
	 * @param string    $relationship The name of the relationship.
	 * @param int|array $post_ids     The source post ID(s).
	 *
	 * @return self
	 */
	public function relatedTo( string $relationship, $post_ids ): self {
		return $this->related( $relationship, $post_ids, 'to' );
	}

	/**
	 * Add a condition for posts that the given post(s) are related to.
	 *
	 * @param string    $relationship The name of the relationship.
	 * @param int|array $post_ids     The source post ID(s).
	 *
	 * @return self
	 */
	public function relatedFrom( string $relationship, $post_ids ): self {
		return $this->related( $relationship, $post_ids, 'from' );
	}

	/**
	 * Add a condition for posts with any relationship to the given post(s).
	 *
	 * @param int|array $post_ids The related post ID(s).
	 *
	 * @return self
	 */
	public function anyRelation( $post_ids ): self {
		$this->query[] = [
			'relation' => 'any',
			'post_ids' => is_array( $post_ids ) ? $post_ids : [ $post_ids ],
		];

		return $this;
	}

	/**
	 * Add a condition for posts with no relationships.
	 *
	 * @param string|null $relationship The specific relationship to check, or null for any relationship.
	 *
	 * @return self
	 */
	public function noRelations( ?string $relationship = null ): self {
		$this->query[] = [
			'relation' => $relationship ?? 'any',
			'none'     => true,
		];

		return $this;
	}

	/**
	 * Set the relation between multiple relationship conditions.
	 *
	 * @param string $relation The relation type ('AND' or 'OR').
	 *
	 * @return self
	 */
	public function relation( string $relation ): self {
		$this->query['relation'] = strtoupper( $relation );

		return $this;
	}

	/**
	 * Get the constructed relationship query array.
	 *
	 * @return array
	 */
	public function get(): array {
		if ( count( $this->query ) > 1 && ! isset( $this->query['relation'] ) ) {
			$this->query['relation'] = 'AND';
		}

		return $this->query;
	}

	/**
	 * Static method to create a simple related posts query.
	 *
	 * @param string    $relationship The name of the relationship.
	 * @param int|array $post_ids     The related post ID(s).
	 * @param string    $direction    The direction of the relationship ('to' or 'from').
	 *
	 * @return array
	 */
	public static function simpleRelated( string $relationship, $post_ids, string $direction = 'to' ): array {
		return ( new self() )->related( $relationship, $post_ids, $direction )->get();
	}

	/**
	 * Static method to create a simple query for posts with any relationship.
	 *
	 * @param int|array $post_ids The related post ID(s).
	 *
	 * @return array
	 */
	public static function simpleAnyRelation( $post_ids ): array {
		return ( new self() )->anyRelation( $post_ids )->get();
	}

	/**
	 * Static method to create a simple query for posts with no relationships.
	 *
	 * @param string|null $relationship The specific relationship to check, or null for any relationship.
	 *
	 * @return array
	 */
	public static function simpleNoRelations( ?string $relationship = null ): array {
		return ( new self() )->noRelations( $relationship )->get();
	}

}