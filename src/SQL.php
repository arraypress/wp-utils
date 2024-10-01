<?php
/**
 * Meta Utilities for WordPress
 *
 * This class provides utility functions for managing WordPress metadata. It includes
 * methods for splitting key-value pairs, bulk updating meta values, retrieving meta data with defaults,
 * incrementing and decrementing numeric meta values, and managing array-based meta data.
 * Additionally, it offers functionality for deleting meta data based on patterns, prefixes, or suffixes.
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
 * Check if the class `SQL` is defined, and if not, define it.
 */
if ( ! class_exists( 'SQL' ) ) :

	/**
	 * Meta Utilities
	 *
	 * Provides utility functions for working with WordPress metadata, such as
	 * splitting key-value pairs, retrieving meta with defaults, incrementing and
	 * decrementing numeric values, managing array-based meta data, and bulk updating.
	 * It also includes functions for deleting meta entries based on patterns, prefixes,
	 * suffixes, or substrings.
	 */
	class SQL {

		/**
		 * Generate a SQL LIKE pattern based on a given pattern and match type.
		 *
		 * @param string $pattern The pattern to match against.
		 * @param string $type    The type of pattern matching: 'prefix', 'suffix', 'substring', or 'exact'.
		 *
		 * @return string The SQL LIKE pattern.
		 */
		public static function generate_like_pattern( string $pattern, string $type = 'exact' ): string {
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
		 * Prepare an IN clause for a SQL query.
		 *
		 * @param array $items An array of items to include in the IN clause.
		 *
		 * @return string The prepared IN clause.
		 */
		public static function prepare_in_clause( array $items ): string {
			global $wpdb;
			$placeholders = array_fill( 0, count( $items ), '%s' );

			return $wpdb->prepare( implode( ',', $placeholders ), $items );
		}

		/**
		 * Generate placeholders for a prepared statement based on an array of values.
		 *
		 * @param array $values The array of values to generate placeholders for.
		 *
		 * @return string The generated placeholders for the prepared statement.
		 */
		public static function generate_placeholders( array $values ): string {
			$sanitized_values = array_map( 'sanitize_text_field', $values );

			$placeholders = array_map( function ( $value ) {
				return is_numeric( $value ) ? '%d' : '%s';
			}, $sanitized_values );

			return implode( ', ', $placeholders );
		}

		/**
		 * Generate an SQL query condition based on a column and query variable.
		 *
		 * @param string $column The column name.
		 * @param mixed  $value  The value to compare.
		 * @param string $type   The type of the value ('numeric' or 'string').
		 *
		 * @return string The generated SQL query condition.
		 */
		public static function generate_query_condition( string $column, $value, string $type = 'numeric' ): string {
			global $wpdb;

			if ( is_null( $value ) ) {
				return '';
			}

			if ( $type === 'numeric' && is_numeric( $value ) ) {
				return $wpdb->prepare( "AND `$column` = %d", $value );
			} elseif ( $type === 'string' ) {
				return $wpdb->prepare( "AND `$column` = %s", sanitize_text_field( $value ) );
			}

			return '';
		}

		/**
		 * Generate SQL WHERE clause for comparing amounts.
		 *
		 * @param string $column     The column name to compare.
		 * @param mixed  $amount     The amount to compare against.
		 * @param string $compare    The comparison operator (e.g., '=', '>', '<', '>=', '<=', '!=').
		 * @param array  $query_vars The query variables (contains min and max for range comparison).
		 *
		 * @return string The generated SQL WHERE clause.
		 */
		public static function generate_amount_query_sql( string $column, $amount, string $compare = '=', array $query_vars = [] ): string {
			global $wpdb;

			$where = '';

			// Validate the comparison operator
			$valid_operators = [ '=', '!=', '>', '>=', '<', '<=' ];
			if ( ! in_array( $compare, $valid_operators, true ) ) {
				$compare = '=';
			}

			// Amount.
			if ( ! empty( $amount ) ) {
				if ( is_array( $amount ) && ! empty( $amount['min'] ) && ! empty( $amount['max'] ) ) {
					$minimum = absint( $amount['min'] );
					$maximum = absint( $amount['max'] );

					$where = $wpdb->prepare( "AND `$column` BETWEEN %d AND %d", $minimum, $maximum );
				} else {
					$amount = absint( $amount );
					$where  = $wpdb->prepare( "AND `$column` $compare %d", $amount );
				}
			}

			return $where;
		}

		/**
		 * Generate an SQL IN clause for a given array of values.
		 *
		 * @param string $column The column name.
		 * @param array  $values The array of values.
		 *
		 * @return string The generated SQL IN clause.
		 */
		public static function generate_in_clause( string $column, array $values ): string {
			global $wpdb;

			if ( empty( $values ) ) {
				return '1=0'; // Return a condition that will always be false
			}

			$placeholders = self::generate_placeholders( $values );

			return $wpdb->prepare( "$column IN ($placeholders)", ...$values );
		}

		/**
		 * Generate an SQL NOT IN clause for a given array of values.
		 *
		 * @param string $column The column name.
		 * @param array  $values The array of values.
		 *
		 * @return string The generated SQL NOT IN clause.
		 */
		public static function generate_not_in_clause( string $column, array $values ): string {
			global $wpdb;

			if ( empty( $values ) ) {
				return '1=1'; // Return a condition that will always be true
			}

			$placeholders = self::generate_placeholders( $values );

			return $wpdb->prepare( "$column NOT IN ($placeholders)", ...$values );
		}

		/**
		 * Generate an SQL LIKE clause for a given string.
		 *
		 * @param string $column The column name.
		 * @param string $value  The value for the LIKE clause.
		 *
		 * @return string The generated SQL LIKE clause.
		 */
		public static function generate_like_clause( string $column, string $value ): string {
			global $wpdb;

			$wildcard_value = self::generate_wildcard( $value );

			return $wpdb->prepare( "$column LIKE %s", $wildcard_value );
		}

		/**
		 * Execute a custom SQL query and return the results.
		 *
		 * @param string $query The SQL query to execute.
		 *
		 * @return array The query results.
		 */
		public static function get_results_by_query( string $query ): array {
			global $wpdb;

			return $wpdb->get_results( $query, ARRAY_A );
		}

		/**
		 * Generate an SQL query condition for a range.
		 *
		 * @param string $column The column name.
		 * @param mixed  $min    The minimum value for the range.
		 * @param mixed  $max    The maximum value for the range.
		 * @param string $type   The type of the values ('numeric' or 'string').
		 *
		 * @return string The generated SQL range condition.
		 */
		public static function generate_range_query_condition( string $column, $min, $max, string $type = 'numeric' ): string {
			global $wpdb;

			if ( is_null( $min ) || is_null( $max ) ) {
				return '';
			}

			if ( $type === 'numeric' && is_numeric( $min ) && is_numeric( $max ) ) {
				return $wpdb->prepare( "AND `$column` BETWEEN %d AND %d", $min, $max );
			} elseif ( $type === 'string' ) {
				return $wpdb->prepare( "AND `$column` BETWEEN %s AND %s", sanitize_text_field( $min ), sanitize_text_field( $max ) );
			}

			return '';
		}

		/**
		 * Execute a custom SQL query and return a single column of results.
		 *
		 * @param string $query The SQL query to execute.
		 *
		 * @return array The single column of query results.
		 */
		public static function get_single_column_results( string $query ): array {
			global $wpdb;

			return $wpdb->get_col( $query );
		}

		/**
		 * Execute a custom SQL query and return a single row of results.
		 *
		 * @param string $query The SQL query to execute.
		 *
		 * @return array|null The single row of query results, or null if no result.
		 */
		public static function get_single_row_result( string $query ): ?array {
			global $wpdb;

			return $wpdb->get_row( $query, ARRAY_A );
		}

		/**
		 * Escape and sanitize an array of values.
		 *
		 * @param array $values The array of values to escape and sanitize.
		 *
		 * @return array The escaped and sanitized array of values.
		 */
		public static function escape_and_sanitize_values( array $values ): array {
			global $wpdb;

			return array_map( function ( $value ) use ( $wpdb ) {
				return is_numeric( $value ) ? intval( $value ) : $wpdb->_escape( sanitize_text_field( $value ) );
			}, $values );
		}

		/**
		 * Count the number of format placeholders in a string.
		 *
		 * This method counts the number of format placeholders in a given string.
		 * Format placeholders are used in sprintf-style formatting and include
		 * types like %s, %d, and %f, as well as positional placeholders like %1$s.
		 *
		 * @param string $string The string to count placeholders in.
		 *
		 * @return int The number of format placeholders in the string.
		 */
		public static function count_placeholders( string $string ): int {
			// Regular expression to match format placeholders (e.g., %s, %d, %f, %1$s)
			$pattern = '/%(?:[0-9]+\$)?[dfs]/';

			// Count the number of matches
			preg_match_all( $pattern, $string, $matches );

			return count( $matches[0] );
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
		public static function generate_date_clause( string $column, string $date, string $comparison = '=' ): string {
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
		public static function generate_order_by_clause( array $order_by ): string {
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
		public static function generate_limit_offset_clause( int $limit, int $offset = 0 ): string {
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
		public static function generate_join_clause( string $table, string $condition, string $type = 'INNER' ): string {
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
		public static function generate_group_by_clause( array $columns ): string {
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
		public static function generate_having_clause( string $condition ): string {
			return "HAVING $condition";
		}

		/**
		 * Execute a query safely and handle errors.
		 *
		 * @param string $query The SQL query to execute.
		 *
		 * @return array|null The query results or null on error.
		 */
		public static function safe_query( string $query ) {
			global $wpdb;
			$result = $wpdb->get_results( $query, ARRAY_A );
			if ( $wpdb->last_error ) {
				error_log( "SQL Error: " . $wpdb->last_error . " in query: $query" );

				return null;
			}

			return $result;
		}

		/**
		 * Generate a subquery.
		 *
		 * @param string $subquery The subquery SQL.
		 * @param string $alias    The alias for the subquery.
		 *
		 * @return string The formatted subquery.
		 */
		public static function generate_subquery( string $subquery, string $alias ): string {
			return "($subquery) AS $alias";
		}

		/**
		 * Sanitize a table or column name.
		 *
		 * @param string $name The name to sanitize.
		 *
		 * @return string The sanitized name.
		 */
		public static function sanitize_sql_name( string $name ): string {
			return preg_replace( '/[^a-zA-Z0-9_]/', '', $name );
		}

		/**
		 * Generate a wildcard string for LIKE clauses.
		 *
		 * @param string $value The value to generate a wildcard for.
		 *
		 * @return string The wildcard string.
		 */
		private static function generate_wildcard( string $value ): string {
			global $wpdb;

			return '%' . $wpdb->esc_like( $value ) . '%';
		}

	}
endif;