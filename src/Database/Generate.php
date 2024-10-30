<?php
/**
 * SQL Utilities for WordPress
 *
 * This class provides utility functions for generating SQL query components and executing
 * safe SQL queries within WordPress. It focuses on creating various SQL clauses and
 * handling placeholders for prepared statements.
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
 * Check if the class `Generate` is defined, and if not, define it.
 */
if ( ! class_exists( 'Generate' ) ) :

	/**
	 * SQL Generate Utilities
	 *
	 * Provides utility functions for generating SQL query components and executing
	 * safe SQL queries within WordPress.
	 */
	class Generate {

		/**
		 * Generate a SQL LIKE pattern based on a given pattern and match type.
		 *
		 * @param string $pattern The pattern to match against.
		 * @param string $type    The type of pattern matching: 'prefix', 'suffix', 'substring', or 'exact'.
		 *
		 * @return string The SQL LIKE pattern.
		 */
		public static function like_pattern( string $pattern, string $type = 'exact' ): string {
			global $wpdb;
			$escaped_pattern = $wpdb->esc_like( $pattern );

			switch ( $type ) {
				case 'prefix':
					return $escaped_pattern . '%';
				case 'suffix':
					return '%' . $escaped_pattern;
				case 'substring':
					return '%' . $escaped_pattern . '%';
				case 'exact':
				default:
					return $escaped_pattern;
			}
		}

		/**
		 * Generate placeholders for a prepared statement based on an array of values.
		 *
		 * @param array $values The array of values to generate placeholders for.
		 *
		 * @return string The generated placeholders for the prepared statement.
		 */
		public static function placeholders( array $values ): string {
			return implode( ', ', array_fill( 0, count( $values ), '%s' ) );
		}

		/**
		 * Generate an SQL IN or NOT IN clause for a given array of values.
		 *
		 * @param string $column The column name.
		 * @param array  $values The array of values.
		 * @param bool   $not    Whether to generate a NOT IN clause instead of IN.
		 *
		 * @return string The generated SQL IN or NOT IN clause.
		 */
		public static function in_clause( string $column, array $values, bool $not = false ): string {
			global $wpdb;

			if ( empty( $values ) ) {
				return $not ? '1=1' : '1=0';
			}

			$placeholders = self::placeholders( $values );
			$operator     = $not ? 'NOT IN' : 'IN';

			return $wpdb->prepare( "$column $operator ($placeholders)", $values );
		}

		/**
		 * Generate an SQL LIKE clause for a given string.
		 *
		 * @param string $column The column name.
		 * @param string $value  The value for the LIKE clause.
		 *
		 * @return string The generated SQL LIKE clause.
		 */
		public static function like_clause( string $column, string $value ): string {
			global $wpdb;

			return $wpdb->prepare( "$column LIKE %s", '%' . $wpdb->esc_like( $value ) . '%' );
		}

		/**
		 * Generate an SQL query condition for a range.
		 *
		 * @param string $column The column name.
		 * @param mixed  $min    The minimum value for the range.
		 * @param mixed  $max    The maximum value for the range.
		 *
		 * @return string The generated SQL range condition.
		 */
		public static function range_condition( string $column, $min, $max ): string {
			global $wpdb;

			return $wpdb->prepare( "$column BETWEEN %s AND %s", $min, $max );
		}

		/**
		 * Generate a DATE/DATETIME clause for SQL queries.
		 *
		 * @param string $column     The column name.
		 * @param string $date       The date string.
		 * @param string $comparison The comparison operator (e.g., '=', '>', '<', '>=', '<=').
		 *
		 * @return string The generated DATE/DATETIME clause.
		 */
		public static function date_clause( string $column, string $date, string $comparison = '=' ): string {
			global $wpdb;

			return $wpdb->prepare( "$column $comparison %s", $date );
		}

		/**
		 * Generate an ORDER BY clause for SQL queries.
		 *
		 * @param array $order_by An array of column => direction pairs.
		 *
		 * @return string The generated ORDER BY clause.
		 */
		public static function order_by_clause( array $order_by ): string {
			$clauses = [];
			foreach ( $order_by as $column => $direction ) {
				$direction = strtoupper( $direction ) === 'DESC' ? 'DESC' : 'ASC';
				$clauses[] = "`$column` $direction";
			}

			return 'ORDER BY ' . implode( ', ', $clauses );
		}

		/**
		 * Generate a LIMIT and OFFSET clause for SQL queries.
		 *
		 * @param int $limit  The maximum number of rows to return.
		 * @param int $offset The number of rows to skip.
		 *
		 * @return string The generated LIMIT and OFFSET clause.
		 */
		public static function limit_offset_clause( int $limit, int $offset = 0 ): string {
			return sprintf( 'LIMIT %d OFFSET %d', $limit, $offset );
		}

		/**
		 * Generate a JOIN clause for SQL queries.
		 *
		 * @param string $table     The table to join.
		 * @param string $condition The join condition.
		 * @param string $type      The type of join (INNER, LEFT, RIGHT).
		 *
		 * @return string The generated JOIN clause.
		 */
		public static function join_clause( string $table, string $condition, string $type = 'INNER' ): string {
			$type = strtoupper( $type );
			if ( ! in_array( $type, [ 'INNER', 'LEFT', 'RIGHT' ] ) ) {
				$type = 'INNER';
			}

			return "$type JOIN $table ON $condition";
		}

		/**
		 * Generate a GROUP BY clause for SQL queries.
		 *
		 * @param array $columns The columns to group by.
		 *
		 * @return string The generated GROUP BY clause.
		 */
		public static function group_by_clause( array $columns ): string {
			return 'GROUP BY ' . implode( ', ', array_map( function ( $column ) {
					return "`$column`";
				}, $columns ) );
		}

		/**
		 * Generate a HAVING clause for SQL queries.
		 *
		 * @param string $condition The HAVING condition.
		 *
		 * @return string The generated HAVING clause.
		 */
		public static function having_clause( string $condition ): string {
			return "HAVING $condition";
		}

		/**
		 * Generate SQL WHERE clause for comparing amounts.
		 *
		 * @param string $column  The column name to compare.
		 * @param mixed  $amount  The amount to compare against. Can be a single value or an array with 'min' and 'max'.
		 * @param string $compare The comparison operator (e.g., '=', '>', '<', '>=', '<=', '!=').
		 *
		 * @return string The generated SQL WHERE clause.
		 */
		public static function amount_query( string $column, $amount, string $compare = '=' ): string {
			global $wpdb;

			// Validate the comparison operator
			$valid_operators = [ '=', '!=', '>', '>=', '<', '<=' ];
			if ( ! in_array( $compare, $valid_operators, true ) ) {
				$compare = '=';
			}

			// Handle different amount formats
			if ( is_array( $amount ) && isset( $amount['min'] ) && isset( $amount['max'] ) ) {
				$minimum = self::sanitize_numeric( $amount['min'] );
				$maximum = self::sanitize_numeric( $amount['max'] );

				return $wpdb->prepare( "AND `$column` BETWEEN %f AND %f", $minimum, $maximum );
			} elseif ( ! empty( $amount ) ) {
				$sanitized_amount = self::sanitize_numeric( $amount );

				return $wpdb->prepare( "AND `$column` $compare %f", $sanitized_amount );
			}

			return '';
		}

		/**
		 * Sanitize a numeric value, preserving float precision.
		 *
		 * @param mixed $value The value to sanitize.
		 *
		 * @return float|int The sanitized numeric value.
		 */
		private static function sanitize_numeric( $value ) {
			if ( is_numeric( $value ) ) {
				return $value + 0; // This preserves float or int type
			}

			return 0; // Default to 0 if not numeric
		}

		/**
		 * Generate a subquery.
		 *
		 * @param string $subquery The subquery SQL.
		 * @param string $alias    The alias for the subquery.
		 *
		 * @return string The formatted subquery.
		 */
		public static function subquery( string $subquery, string $alias ): string {
			return "($subquery) AS $alias";
		}

	}

endif;