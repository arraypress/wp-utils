<?php
/**
 * Author Query Builder for WordPress
 *
 * This class provides a fluent interface for constructing author queries in WordPress.
 * It supports both simple static queries and more complex chainable queries, allowing
 * developers to easily filter and retrieve authors based on various criteria such as
 * IDs, names, roles, capabilities, and meta data.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Database\Query;

class Author {

	/** @var array The constructed author query */
	private array $query = [];

	/**
	 * Create a new Author instance.
	 *
	 * @return self
	 */
	public static function new(): self {
		return new self();
	}

	/**
	 * Add an author ID or array of IDs to the query.
	 *
	 * @param int|array $author_ids Single author ID or array of author IDs.
	 *
	 * @return self
	 */
	public function id( $author_ids ): self {
		$this->query['author__in'] = is_array( $author_ids ) ? $author_ids : [ $author_ids ];

		return $this;
	}

	/**
	 * Exclude an author ID or array of IDs from the query.
	 *
	 * @param int|array $author_ids Single author ID or array of author IDs to exclude.
	 *
	 * @return self
	 */
	public function notId( $author_ids ): self {
		$this->query['author__not_in'] = is_array( $author_ids ) ? $author_ids : [ $author_ids ];

		return $this;
	}

	/**
	 * Add an author by name to the query.
	 *
	 * @param string $author_name The 'user_nicename' of the author.
	 *
	 * @return self
	 */
	public function name( string $author_name ): self {
		$this->query['author_name'] = $author_name;

		return $this;
	}

	/**
	 * Add multiple authors by name to the query.
	 *
	 * @param array $author_names Array of author names ('user_nicename').
	 *
	 * @return self
	 */
	public function names( array $author_names ): self {
		$this->query['author_name'] = implode( ',', $author_names );

		return $this;
	}

	/**
	 * Add authors by role to the query.
	 *
	 * @param string|array $roles Single role or array of roles.
	 *
	 * @return self
	 */
	public function role( $roles ): self {
		if ( ! isset( $this->query['author__in'] ) ) {
			$this->query['author__in'] = [];
		}
		$role_users                = get_users( [ 'role__in' => (array) $roles, 'fields' => 'ID' ] );
		$this->query['author__in'] = array_merge( $this->query['author__in'], $role_users );

		return $this;
	}

	/**
	 * Exclude authors by role from the query.
	 *
	 * @param string|array $roles Single role or array of roles to exclude.
	 *
	 * @return self
	 */
	public function notRole( $roles ): self {
		if ( ! isset( $this->query['author__not_in'] ) ) {
			$this->query['author__not_in'] = [];
		}
		$role_users                    = get_users( [ 'role__in' => (array) $roles, 'fields' => 'ID' ] );
		$this->query['author__not_in'] = array_merge( $this->query['author__not_in'], $role_users );

		return $this;
	}

	/**
	 * Add authors by capability to the query.
	 *
	 * @param string|array $capabilities Single capability or array of capabilities.
	 *
	 * @return self
	 */
	public function capability( $capabilities ): self {
		if ( ! isset( $this->query['author__in'] ) ) {
			$this->query['author__in'] = [];
		}
		$cap_users                 = get_users( [ 'capability' => (array) $capabilities, 'fields' => 'ID' ] );
		$this->query['author__in'] = array_merge( $this->query['author__in'], $cap_users );

		return $this;
	}

	/**
	 * Exclude authors by capability from the query.
	 *
	 * @param string|array $capabilities Single capability or array of capabilities to exclude.
	 *
	 * @return self
	 */
	public function notCapability( $capabilities ): self {
		if ( ! isset( $this->query['author__not_in'] ) ) {
			$this->query['author__not_in'] = [];
		}
		$cap_users                     = get_users( [ 'capability' => (array) $capabilities, 'fields' => 'ID' ] );
		$this->query['author__not_in'] = array_merge( $this->query['author__not_in'], $cap_users );

		return $this;
	}

	/**
	 * Add a meta query for authors.
	 *
	 * @param string $key     The meta key.
	 * @param mixed  $value   The meta value.
	 * @param string $compare The comparison operator (default: '=').
	 *
	 * @return self
	 */
	public function meta( string $key, $value, string $compare = '=' ): self {
		if ( ! isset( $this->query['meta_query'] ) ) {
			$this->query['meta_query'] = [];
		}
		$this->query['meta_query'][] = [
			'key'     => $key,
			'value'   => $value,
			'compare' => $compare,
		];

		return $this;
	}

	/**
	 * Set the meta query relation.
	 *
	 * @param string $relation The relation between meta queries ('AND' or 'OR').
	 *
	 * @return self
	 */
	public function metaRelation( string $relation ): self {
		if ( ! isset( $this->query['meta_query'] ) ) {
			$this->query['meta_query'] = [];
		}
		$this->query['meta_query']['relation'] = strtoupper( $relation );

		return $this;
	}

	/**
	 * Get the constructed author query array.
	 *
	 * @return array
	 */
	public function get(): array {
		return $this->query;
	}

	/**
	 * Static method to create a simple author ID query.
	 *
	 * @param int|array $author_ids Single author ID or array of author IDs.
	 *
	 * @return array
	 */
	public static function simpleId( $author_ids ): array {
		return ( new self() )->id( $author_ids )->get();
	}

	/**
	 * Static method to create a simple author name query.
	 *
	 * @param string $author_name The 'user_nicename' of the author.
	 *
	 * @return array
	 */
	public static function simpleName( string $author_name ): array {
		return ( new self() )->name( $author_name )->get();
	}

	/**
	 * Static method to create a simple author role query.
	 *
	 * @param string|array $roles Single role or array of roles.
	 *
	 * @return array
	 */
	public static function simpleRole( $roles ): array {
		return ( new self() )->role( $roles )->get();
	}

}