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

	/**
	 * Generate a prepared condition for different data types.
	 *
	 * @param string $column    The column name.
	 * @param mixed  $value     The value to compare against.
	 * @param string $operator  The comparison operator. Default '='.
	 * @param string $data_type The data type ('string', 'int', 'float', 'datetime'). Default 'string'.
	 *
	 * @return string The prepared SQL condition.
	 */
	public static function prepare_condition( string $column, $value, string $operator = '=', string $data_type = 'string' ): string {
		global $wpdb;

		// Handle NULL values
		if ( is_null( $value ) ) {
			return $operator === '=' ? "$column IS NULL" : "$column IS NOT NULL";
		}

		// Handle empty string special case
		if ( $data_type === 'string' && $value === '' ) {
			return $operator === '=' ? "$column = ''" : "$column != ''";
		}

		// Map data types to MySQL placeholder types
		$placeholders = [
			'string'   => '%s',
			'int'      => '%d',
			'float'    => '%f',
			'datetime' => '%s'
		];

		$placeholder = $placeholders[ $data_type ] ?? '%s';

		// Format value for datetime if needed
		if ( $data_type === 'datetime' && ! is_string( $value ) ) {
			$value = date( 'Y-m-d H:i:s', $value );
		}

		return $wpdb->prepare( "{$column} {$operator} {$placeholder}", $value );
	}

	/**
	 * Generate a WHERE clause from an array of conditions.
	 *
	 * @param array  $conditions Array of condition strings.
	 * @param string $operator   The operator to join conditions ('AND' or 'OR'). Default 'AND'.
	 *
	 * @return string The complete WHERE clause.
	 */
	public static function where_clause( array $conditions, string $operator = 'AND' ): string {
		if ( empty( $conditions ) ) {
			return '';
		}

		$operator = strtoupper( $operator );
		if ( ! in_array( $operator, [ 'AND', 'OR' ] ) ) {
			$operator = 'AND';
		}

		return 'WHERE ' . implode( " $operator ", array_filter( $conditions ) );
	}

	/**
	 * Execute a query and return a single value with type casting.
	 *
	 * @param string $query     The SQL query to execute.
	 * @param string $cast_type The type to cast the result to ('int', 'float', 'string'). Default 'float'.
	 * @param mixed  $default   The default value if the result is null. Default 0.0.
	 * @param int    $decimals  Number of decimal places for float results. Default 2.
	 *
	 * @return mixed The typed query result or default value.
	 */
	public static function get_var_cast( string $query, string $cast_type = 'float', $default = 0.0, int $decimals = 2 ) {
		global $wpdb;

		$result = $wpdb->get_var( $query );

		if ( is_null( $result ) ) {
			return $default;
		}

		switch ( $cast_type ) {
			case 'int':
				return (int) $result;
			case 'float':
				return round( (float) $result, $decimals );
			case 'string':
				return (string) $result;
			default:
				return $result;
		}
	}

	/**
	 * Generate a simple NOT EMPTY condition for a column.
	 *
	 * @param string $column The column name.
	 *
	 * @return string The NOT EMPTY condition.
	 */
	public static function not_empty( string $column ): string {
		return sprintf( "%s != '' AND %s IS NOT NULL", $column, $column );
	}

	/**
	 * Generate a SELECT clause with count and optional grouping.
	 *
	 * @param string $column   The column to count.
	 * @param string $alias    The alias for the count.
	 * @param string $distinct Whether to use DISTINCT. Default empty.
	 *
	 * @return string The generated SELECT COUNT clause.
	 */
	public static function count_select( string $column, string $alias, string $distinct = '' ): string {
		$distinct = strtoupper( $distinct ) === 'DISTINCT' ? 'DISTINCT ' : '';

		return "COUNT({$distinct}{$column}) as {$alias}";
	}

	/**
	 * Generate a date/time extraction SELECT clause.
	 *
	 * @param string $column The date column.
	 * @param string $part   The part to extract ('MONTH', 'HOUR', 'DOW', etc).
	 * @param bool   $name   Whether to include the name (MONTHNAME, DAYNAME).
	 *
	 * @return string The generated date extraction clause.
	 */
	public static function date_extract_select( string $column, string $part, bool $name = false ): string {
		$extracts = [];
		$part     = strtoupper( $part );

		// Add numeric extraction
		$extracts[] = "{$part}({$column}) as {$part}_num";

		// Add name if requested
		if ( $name ) {
			$name_func  = $part . 'NAME';
			$extracts[] = "{$name_func}({$column}) as {$part}_name";
		}

		return implode( ", ", $extracts );
	}

	/**
	 * Generate an aggregate function clause.
	 *
	 * @param string $function The aggregate function (AVG, SUM, etc).
	 * @param string $column   The column to aggregate.
	 * @param string $alias    The alias for the result.
	 *
	 * @return string The generated aggregate clause.
	 */
	public static function aggregate( string $function, string $column, string $alias ): string {
		$function = strtoupper( $function );

		return "{$function}({$column}) as {$alias}";
	}

	/**
	 * Generate a complete SELECT query from components.
	 *
	 * @param array $components Array of query components.
	 *
	 * @return string The complete SQL query.
	 */
	public static function build_select( array $components ): string {
		$required = [ 'select', 'from' ];
		$optional = [ 'join', 'where', 'group', 'having', 'order', 'limit' ];

		// Validate required components
		foreach ( $required as $req ) {
			if ( empty( $components[ $req ] ) ) {
				throw new \InvalidArgumentException( "Missing required component: {$req}" );
			}
		}

		$query_parts   = [];
		$query_parts[] = $components['select'];
		$query_parts[] = $components['from'];

		// Add optional components in correct order
		foreach ( $optional as $opt ) {
			if ( ! empty( $components[ $opt ] ) ) {
				$query_parts[] = $components[ $opt ];
			}
		}

		return implode( ' ', array_filter( $query_parts ) );
	}

	/**
	 * Format a result value based on type.
	 *
	 * @param mixed  $value The value to format.
	 * @param string $type  The type of formatting ('int', 'float', 'time', 'percentage').
	 * @param array  $args  Optional formatting arguments.
	 *
	 * @return mixed The formatted value.
	 */
	public static function format_result( $value, string $type, array $args = [] ) {
		if ( is_null( $value ) ) {
			return null;
		}

		switch ( $type ) {
			case 'int':
				return absint( $value );
			case 'float':
				$decimals = $args['decimals'] ?? 2;

				return round( (float) $value, $decimals );
			case 'percentage':
				$decimals = $args['decimals'] ?? 2;

				return round( (float) $value, $decimals ) . '%';
			case 'time':
				// Time periods in seconds
				$periods = [
					'mo' => MONTH_IN_SECONDS,
					'w'  => WEEK_IN_SECONDS,
					'd'  => DAY_IN_SECONDS,
					'h'  => HOUR_IN_SECONDS,
					'm'  => MINUTE_IN_SECONDS
				];

				$seconds = absint( $value );
				foreach ( $periods as $suffix => $period ) {
					if ( $seconds >= $period ) {
						return round( $seconds / $period ) . $suffix;
					}
				}

				return '1m';
			default:
				return $value;
		}
	}

	/**
	 * Generate a window function clause.
	 *
	 * @param string $function    The window function.
	 * @param string $partitionBy The PARTITION BY column(s).
	 * @param string $orderBy     The ORDER BY column(s).
	 *
	 * @return string The generated window function clause.
	 */
	public static function window_function( string $function, string $partitionBy = '', string $orderBy = '' ): string {
		$parts = [ $function ];

		if ( ! empty( $partitionBy ) ) {
			$parts[] = "PARTITION BY {$partitionBy}";
		}

		if ( ! empty( $orderBy ) ) {
			$parts[] = "ORDER BY {$orderBy}";
		}

		return implode( ' ', $parts ) . ' OVER ()';
	}

}