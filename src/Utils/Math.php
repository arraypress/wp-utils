<?php
/**
 * Helper function for mathematical expression evaluation
 *
 * @package       ArrayPress/Utils/Math
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Math;

use Exception;

if ( ! function_exists( __NAMESPACE__ . '\evaluate_expression' ) ):
	/**
	 * Evaluates a mathematical expression with specified precision
	 *
	 * @param string        $expression     The mathematical expression to evaluate
	 * @param int           $scale          Number of decimal places (default: 4)
	 * @param callable|null $error_callback Optional callback for error handling
	 *
	 * @return string|int|null The evaluated result or null if an error occurs
	 */
	function evaluate_expression( string $expression, int $scale = 4, ?callable $error_callback = null ) {
		try {
			static $parser = null;
			static $current_scale = null;

			// Create new parser instance if scale changes
			if ( $parser === null || $current_scale !== $scale ) {
				$parser        = new ExpressionParser( $scale );
				$current_scale = $scale;
			}

			return $parser->evaluate( $expression );

		} catch ( Exception $e ) {
			if ( is_callable( $error_callback ) ) {
				call_user_func( $error_callback, $e );
			}

			return null;
		}
	}
endif;