<?php
/**
 * Date Query Builder for WordPress
 *
 * This class provides a fluent interface for constructing date queries in WordPress.
 * It supports both simple static queries and more complex chainable queries, allowing
 * developers to easily filter content based on various date-related criteria such as
 * before, after, between, and specific date components (year, month, day, etc.).
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Query;

/**
 * Check if the class `Date` is defined, and if not, define it.
 */
if ( ! class_exists( 'Date' ) ) :
	/**
	 * Date Query Helper Class
	 *
	 * This class provides a set of methods to easily construct date queries for WordPress.
	 * It supports both simple static queries and more complex chainable queries.
	 */
	class Date {

		/** @var array The constructed date query */
		private array $query = [];

		/**
		 * Create a new DateQuery instance.
		 *
		 * @return self
		 */
		public static function new(): self {
			return new self();
		}

		/**
		 * Add an 'after' condition to the date query.
		 *
		 * @param string|array $date      Date string or array of date parameters.
		 * @param bool         $inclusive Whether the comparison should be inclusive.
		 * @param string       $column    The database column to query against.
		 *
		 * @return self
		 */
		public function after( $date, bool $inclusive = true, string $column = 'post_date' ): self {
			$this->query[] = [
				'after'     => $date,
				'inclusive' => $inclusive,
				'column'    => $column,
			];

			return $this;
		}

		/**
		 * Add a 'before' condition to the date query.
		 *
		 * @param string|array $date      Date string or array of date parameters.
		 * @param bool         $inclusive Whether the comparison should be inclusive.
		 * @param string       $column    The database column to query against.
		 *
		 * @return self
		 */
		public function before( $date, bool $inclusive = true, string $column = 'post_date' ): self {
			$this->query[] = [
				'before'    => $date,
				'inclusive' => $inclusive,
				'column'    => $column,
			];

			return $this;
		}

		/**
		 * Add a date range (between) condition to the date query.
		 *
		 * @param string|array $after     After date.
		 * @param string|array $before    Before date.
		 * @param bool         $inclusive Whether the comparison should be inclusive.
		 * @param string       $column    The database column to query against.
		 *
		 * @return self
		 */
		public function between( $after, $before, bool $inclusive = true, string $column = 'post_date' ): self {
			$this->query[] = [
				'after'     => $after,
				'before'    => $before,
				'inclusive' => $inclusive,
				'column'    => $column,
			];

			return $this;
		}

		/**
		 * Add a year condition to the date query.
		 *
		 * @param int    $year   The year to query for.
		 * @param string $column The database column to query against.
		 *
		 * @return self
		 */
		public function year( int $year, string $column = 'post_date' ): self {
			$this->query[] = [
				'year'   => $year,
				'column' => $column,
			];

			return $this;
		}

		/**
		 * Add a month condition to the date query.
		 *
		 * @param int    $month  The month to query for (1-12).
		 * @param string $column The database column to query against.
		 *
		 * @return self
		 */
		public function month( int $month, string $column = 'post_date' ): self {
			$this->query[] = [
				'month'  => $month,
				'column' => $column,
			];

			return $this;
		}

		/**
		 * Add a day condition to the date query.
		 *
		 * @param int    $day    The day to query for (1-31).
		 * @param string $column The database column to query against.
		 *
		 * @return self
		 */
		public function day( int $day, string $column = 'post_date' ): self {
			$this->query[] = [
				'day'    => $day,
				'column' => $column,
			];

			return $this;
		}

		/**
		 * Add a week condition to the date query.
		 *
		 * @param int    $week   The week to query for (0-53).
		 * @param string $column The database column to query against.
		 *
		 * @return self
		 */
		public function week( int $week, string $column = 'post_date' ): self {
			$this->query[] = [
				'week'   => $week,
				'column' => $column,
			];

			return $this;
		}

		/**
		 * Add an hour condition to the date query.
		 *
		 * @param int    $hour   The hour to query for (0-23).
		 * @param string $column The database column to query against.
		 *
		 * @return self
		 */
		public function hour( int $hour, string $column = 'post_date' ): self {
			$this->query[] = [
				'hour'   => $hour,
				'column' => $column,
			];

			return $this;
		}

		/**
		 * Add a minute condition to the date query.
		 *
		 * @param int    $minute The minute to query for (0-59).
		 * @param string $column The database column to query against.
		 *
		 * @return self
		 */
		public function minute( int $minute, string $column = 'post_date' ): self {
			$this->query[] = [
				'minute' => $minute,
				'column' => $column,
			];

			return $this;
		}

		/**
		 * Add a second condition to the date query.
		 *
		 * @param int    $second The second to query for (0-59).
		 * @param string $column The database column to query against.
		 *
		 * @return self
		 */
		public function second( int $second, string $column = 'post_date' ): self {
			$this->query[] = [
				'second' => $second,
				'column' => $column,
			];

			return $this;
		}

		/**
		 * Add a custom date comparison to the date query.
		 *
		 * @param string $compare The comparison operator (=, !=, >, >=, <, <=, LIKE, NOT LIKE).
		 * @param string $column  The database column to query against.
		 * @param mixed  $value   The value to compare against.
		 *
		 * @return self
		 */
		public function compare( string $compare, string $column, $value ): self {
			$this->query[] = [
				'compare' => $compare,
				'column'  => $column,
				'value'   => $value,
			];

			return $this;
		}

		/**
		 * Set the relation between date queries.
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
		 * Get the constructed date query array.
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
		 * Static method to create a simple 'after' date query.
		 *
		 * @param string|array $date      Date string or array of date parameters.
		 * @param bool         $inclusive Whether the comparison should be inclusive.
		 * @param string       $column    The database column to query against.
		 *
		 * @return array
		 */
		public static function simpleAfter( $date, bool $inclusive = true, string $column = 'post_date' ): array {
			return ( new self() )->after( $date, $inclusive, $column )->get();
		}

		/**
		 * Static method to create a simple 'before' date query.
		 *
		 * @param string|array $date      Date string or array of date parameters.
		 * @param bool         $inclusive Whether the comparison should be inclusive.
		 * @param string       $column    The database column to query against.
		 *
		 * @return array
		 */
		public static function simpleBefore( $date, bool $inclusive = true, string $column = 'post_date' ): array {
			return ( new self() )->before( $date, $inclusive, $column )->get();
		}

		/**
		 * Static method to create a simple date range (between) query.
		 *
		 * @param string|array $after     After date.
		 * @param string|array $before    Before date.
		 * @param bool         $inclusive Whether the comparison should be inclusive.
		 * @param string       $column    The database column to query against.
		 *
		 * @return array
		 */
		public static function simpleBetween( $after, $before, bool $inclusive = true, string $column = 'post_date' ): array {
			return ( new self() )->between( $after, $before, $inclusive, $column )->get();
		}
	}
endif;