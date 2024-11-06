<?php
/**
 * Meta Query Builder for WordPress
 *
 * This class provides a fluent interface for constructing meta queries in WordPress.
 * It supports both simple static queries and more complex chainable queries, allowing
 * developers to easily filter content based on meta data using various comparison
 * operations such as exists, not exists, between, in, like, and custom comparisons.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Database\Query;

class Meta {

	/** @var array The constructed meta query */
	private array $query = [];

	/**
	 * Create a new MetaQuery instance.
	 *
	 * @return self
	 */
	public static function new(): self {
		return new self();
	}

	/**
	 * Add a condition to check if a key exists.
	 *
	 * @param string $key The meta key to check.
	 *
	 * @return self
	 */
	public function exists( string $key ): self {
		$this->query[] = [
			'key'     => $key,
			'compare' => 'EXISTS',
		];

		return $this;
	}

	/**
	 * Add a condition to check if a key does not exist.
	 *
	 * @param string $key The meta key to check.
	 *
	 * @return self
	 */
	public function notExists( string $key ): self {
		$this->query[] = [
			'key'     => $key,
			'compare' => 'NOT EXISTS',
		];

		return $this;
	}

	/**
	 * Add a condition to compare a key to a value.
	 *
	 * @param string $key     The meta key.
	 * @param mixed  $value   The value to compare against.
	 * @param string $compare The comparison operator. Default is '='.
	 * @param string $type    The data type. Default is 'CHAR'.
	 *
	 * @return self
	 */
	public function compare( string $key, $value, string $compare = '=', string $type = 'CHAR' ): self {
		$this->query[] = [
			'key'     => $key,
			'value'   => $value,
			'compare' => $compare,
			'type'    => $type,
		];

		return $this;
	}

	/**
	 * Add a condition for a value between two numbers.
	 *
	 * @param string $key The meta key.
	 * @param float  $min The minimum value.
	 * @param float  $max The maximum value.
	 *
	 * @return self
	 */
	public function between( string $key, float $min, float $max ): self {
		$this->query[] = [
			'key'     => $key,
			'value'   => [ $min, $max ],
			'compare' => 'BETWEEN',
			'type'    => 'NUMERIC',
		];

		return $this;
	}

	/**
	 * Add a condition for a value not between two numbers.
	 *
	 * @param string $key The meta key.
	 * @param float  $min The minimum value.
	 * @param float  $max The maximum value.
	 *
	 * @return self
	 */
	public function notBetween( string $key, float $min, float $max ): self {
		$this->query[] = [
			'key'     => $key,
			'value'   => [ $min, $max ],
			'compare' => 'NOT BETWEEN',
			'type'    => 'NUMERIC',
		];

		return $this;
	}

	/**
	 * Add a condition for a value in an array.
	 *
	 * @param string $key    The meta key.
	 * @param array  $values The array of values to check against.
	 *
	 * @return self
	 */
	public function in( string $key, array $values ): self {
		$this->query[] = [
			'key'     => $key,
			'value'   => $values,
			'compare' => 'IN',
		];

		return $this;
	}

	/**
	 * Add a condition for a value not in an array.
	 *
	 * @param string $key    The meta key.
	 * @param array  $values The array of values to check against.
	 *
	 * @return self
	 */
	public function notIn( string $key, array $values ): self {
		$this->query[] = [
			'key'     => $key,
			'value'   => $values,
			'compare' => 'NOT IN',
		];

		return $this;
	}

	/**
	 * Add a LIKE condition.
	 *
	 * @param string $key   The meta key.
	 * @param string $value The value to compare against.
	 *
	 * @return self
	 */
	public function like( string $key, string $value ): self {
		$this->query[] = [
			'key'     => $key,
			'value'   => $value,
			'compare' => 'LIKE',
		];

		return $this;
	}

	/**
	 * Add a NOT LIKE condition.
	 *
	 * @param string $key   The meta key.
	 * @param string $value The value to compare against.
	 *
	 * @return self
	 */
	public function notLike( string $key, string $value ): self {
		$this->query[] = [
			'key'     => $key,
			'value'   => $value,
			'compare' => 'NOT LIKE',
		];

		return $this;
	}

	/**
	 * Set the relation for the meta query.
	 *
	 * @param string $relation The relation between queries ('AND' or 'OR').
	 *
	 * @return self
	 */
	public function relation( string $relation ): self {
		$this->query['relation'] = strtoupper( $relation );

		return $this;
	}

	/**
	 * Get the constructed meta query array.
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
	 * Static method to create a simple meta query for checking if a key exists.
	 *
	 * @param string $key The meta key to check.
	 *
	 * @return array The meta query array.
	 */
	public static function simpleExists( string $key ): array {
		return ( new self() )->exists( $key )->get();
	}

	/**
	 * Static method to create a simple meta query for checking if a key does not exist.
	 *
	 * @param string $key The meta key to check.
	 *
	 * @return array The meta query array.
	 */
	public static function simpleNotExists( string $key ): array {
		return ( new self() )->notExists( $key )->get();
	}

	/**
	 * Static method to create a simple meta query for comparing a key to a value.
	 *
	 * @param string $key     The meta key.
	 * @param mixed  $value   The value to compare against.
	 * @param string $compare The comparison operator. Default is '='.
	 *
	 * @return array The meta query array.
	 */
	public static function simpleCompare( string $key, $value, string $compare = '=' ): array {
		return ( new self() )->compare( $key, $value, $compare )->get();
	}

	/**
	 * Static method to create a simple meta query for a value between two numbers.
	 *
	 * @param string $key The meta key.
	 * @param float  $min The minimum value.
	 * @param float  $max The maximum value.
	 *
	 * @return array The meta query array.
	 */
	public static function simpleBetween( string $key, float $min, float $max ): array {
		return ( new self() )->between( $key, $min, $max )->get();
	}

	/**
	 * Static method to create a simple meta query for a value in an array.
	 *
	 * @param string $key    The meta key.
	 * @param array  $values The array of values to check against.
	 *
	 * @return array The meta query array.
	 */
	public static function simpleIn( string $key, array $values ): array {
		return ( new self() )->in( $key, $values )->get();
	}
}