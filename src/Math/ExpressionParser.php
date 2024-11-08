<?php
/**
 * Expression Handler Class
 *
 * Handles mathematical expression evaluation using the Shunting Yard algorithm
 * and Reverse Polish Notation (RPN). Supports arbitrary precision arithmetic
 * using PHP's BC Math functions.
 *
 * @package     ArrayPress/Utils
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Math;

use Exception;

class ExpressionParser {

	/**
	 * Defines supported operators with their properties
	 *
	 * Each operator has:
	 * - precedence: Operator precedence (higher number = higher precedence)
	 * - associativity: 'L' for left-associative, 'R' for right-associative
	 * - function: BC Math function to use for the operation
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private const OPERATORS = [
		'+' => [
			'precedence'    => 1,
			'associativity' => 'L',
			'function'      => 'bcadd'
		],
		'-' => [
			'precedence'    => 1,
			'associativity' => 'L',
			'function'      => 'bcsub'
		],
		'*' => [
			'precedence'    => 2,
			'associativity' => 'L',
			'function'      => 'bcmul'
		],
		'/' => [
			'precedence'    => 2,
			'associativity' => 'L',
			'function'      => 'bcdiv'
		],
		'^' => [
			'precedence'    => 3,
			'associativity' => 'R',
			'function'      => 'bcpow'
		]
	];

	/**
	 * Number of decimal places for calculations
	 *
	 * @var int
	 */
	private int $scale = 4;

	/**
	 * Constructor
	 *
	 * @param int $scale Number of decimal places for calculations (default: 4)
	 */
	public function __construct( int $scale = 4 ) {
		$this->scale = $scale;
	}

	/**
	 * Evaluates a mathematical expression
	 *
	 * Converts infix notation to postfix (RPN) and evaluates the result.
	 *
	 * @param string $expression Mathematical expression to evaluate
	 *
	 * @return string|int Evaluated result
	 * @throws Exception If the expression is invalid or contains errors
	 */
	public function evaluate( string $expression ) {
		// Validate the expression
		$this->validate( $expression );

		// Convert to postfix and evaluate
		$output_queue = $this->infix_to_postfix( $expression );

		return $this->evaluate_rpn( $output_queue );
	}

	/**
	 * Validates the expression for basic syntax errors
	 *
	 * @param string $expression Expression to validate
	 *
	 * @throws Exception If expression contains invalid characters or syntax
	 */
	private function validate( string $expression ): void {
		// Remove whitespace for validation
		$expression = trim( $expression );

		if ( empty( $expression ) ) {
			throw new Exception( "Expression cannot be empty." );
		}

		// Check for valid characters
		$valid_chars = array_merge(
			array_keys( self::OPERATORS ),
			[ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', '(', ')', ' ' ]
		);
		$invalid     = str_replace( $valid_chars, '', $expression );

		if ( ! empty( $invalid ) ) {
			throw new Exception( "Invalid characters in expression: " . $invalid );
		}

		// Check parentheses matching
		$this->validate_parentheses( $expression );
	}

	/**
	 * Validates parentheses matching in the expression
	 *
	 * @param string $expression Expression to validate
	 *
	 * @throws Exception If parentheses are mismatched
	 */
	private function validate_parentheses( string $expression ): void {
		$count = 0;
		for ( $i = 0; $i < strlen( $expression ); $i ++ ) {
			if ( $expression[ $i ] === '(' ) {
				$count ++;
			} elseif ( $expression[ $i ] === ')' ) {
				$count --;
			}
			if ( $count < 0 ) {
				throw new Exception( "Mismatched parentheses: unexpected ')'" );
			}
		}
		if ( $count > 0 ) {
			throw new Exception( "Mismatched parentheses: missing ')'" );
		}
	}

	/**
	 * Converts infix notation to postfix (RPN) using Shunting Yard algorithm
	 *
	 * @param string $expression Expression in infix notation
	 *
	 * @return array Expression tokens in postfix notation
	 * @throws Exception If expression contains syntax errors
	 */
	private function infix_to_postfix( string $expression ): array {
		$output_queue   = [];
		$operator_stack = [];

		// Remove spaces and tokenize
		$expression = str_replace( ' ', '', $expression );
		$tokens     = $this->tokenize_expression( $expression );

		foreach ( $tokens as $token ) {
			if ( is_numeric( $token ) ) {
				$output_queue[] = $token;
			} elseif ( isset( self::OPERATORS[ $token ] ) ) {
				$this->process_operator( $token, $operator_stack, $output_queue );
			} elseif ( $token === '(' ) {
				$operator_stack[] = $token;
			} elseif ( $token === ')' ) {
				$this->process_right_parenthesis( $operator_stack, $output_queue );
			}
		}

		// Empty remaining operators to output queue
		while ( ! empty( $operator_stack ) ) {
			$operator = array_pop( $operator_stack );
			if ( $operator === '(' || $operator === ')' ) {
				throw new Exception( "Mismatched parentheses" );
			}
			$output_queue[] = $operator;
		}

		return $output_queue;
	}

	/**
	 * Tokenizes the expression into individual elements
	 *
	 * @param string $expression Expression to tokenize
	 *
	 * @return array Array of tokens
	 */
	private function tokenize_expression( string $expression ): array {
		return preg_split(
			'/(?<=[\d)])(?=[^0-9.])|(?<=[^0-9.])(?=[\d(])/',
			$expression,
			- 1,
			PREG_SPLIT_NO_EMPTY
		);
	}

	/**
	 * Processes an operator according to the Shunting Yard algorithm
	 *
	 * @param string $operator Current operator
	 * @param array  $stack    Operator stack
	 * @param array  $output   Output queue
	 */
	private function process_operator( string $operator, array &$stack, array &$output ): void {
		while ( ! empty( $stack ) && end( $stack ) !== '(' && isset( self::OPERATORS[ end( $stack ) ] ) ) {
			$top_operator = end( $stack );
			if ( $this->should_pop_operator( $operator, $top_operator ) ) {
				$output[] = array_pop( $stack );
				continue;
			}
			break;
		}
		$stack[] = $operator;
	}

	/**
	 * Determines if the top operator should be popped based on precedence and associativity
	 *
	 * @param string $currentOp Current operator
	 * @param string $topOp     Top operator on stack
	 *
	 * @return bool True if top operator should be popped
	 */
	private function should_pop_operator( string $currentOp, string $topOp ): bool {
		$current_op_info = self::OPERATORS[ $currentOp ];
		$top_op_info     = self::OPERATORS[ $topOp ];

		return ( $current_op_info['associativity'] === 'L' &&
		         $current_op_info['precedence'] <= $top_op_info['precedence'] ) ||
		       ( $current_op_info['associativity'] === 'R' &&
		         $current_op_info['precedence'] < $top_op_info['precedence'] );
	}

	/**
	 * Processes a right parenthesis in the expression
	 *
	 * @param array $stack  Operator stack
	 * @param array $output Output queue
	 *
	 * @throws Exception If parentheses are mismatched
	 */
	private function process_right_parenthesis( array &$stack, array &$output ): void {
		$found_left_parens = false;
		while ( ! empty( $stack ) ) {
			$operator = array_pop( $stack );
			if ( $operator === '(' ) {
				$found_left_parens = true;
				break;
			}
			$output[] = $operator;
		}

		if ( ! $found_left_parens ) {
			throw new Exception( "Mismatched parentheses" );
		}
	}

	/**
	 * Evaluates expression in Reverse Polish Notation (RPN)
	 *
	 * @param array $postfix Expression in postfix notation
	 *
	 * @return string|int Evaluated result
	 * @throws Exception If expression contains errors
	 */
	private function evaluate_rpn( array $postfix ) {
		$stack = [];

		foreach ( $postfix as $token ) {
			if ( is_numeric( $token ) ) {
				$stack[] = $token;
				continue;
			}

			if ( ! isset( self::OPERATORS[ $token ] ) ) {
				throw new Exception( "Unknown operator: $token" );
			}

			if ( count( $stack ) < 2 ) {
				throw new Exception( "Insufficient operands for operator: $token" );
			}

			$right = array_pop( $stack );
			$left  = array_pop( $stack );

			if ( $token === '/' && $right == 0 ) {
				throw new Exception( "Division by zero" );
			}

			$function = self::OPERATORS[ $token ]['function'];
			$result   = $function( $left, $right, $this->scale );
			$stack[]  = $result;
		}

		if ( count( $stack ) !== 1 ) {
			throw new Exception( "Invalid expression: too many operands" );
		}

		return $this->format_result( array_pop( $stack ) );
	}

	/**
	 * Formats the final result
	 *
	 * Removes unnecessary decimal zeros and converts to integer if possible.
	 *
	 * @param string $result Result to format
	 *
	 * @return string|int Formatted result
	 */
	private function format_result( string $result ) {
		// Remove trailing zeros and decimal point if unnecessary
		$result = rtrim( rtrim( $result, '0' ), '.' );

		// Convert to integer if no decimal part
		if ( strpos( $result, '.' ) === false ) {
			return (int) $result;
		}

		return $result;
	}

}