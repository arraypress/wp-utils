<?php
/**
 * E-commerce Math Utility Class
 *
 * This class provides a set of utility methods for performing calculations
 * commonly used in e-commerce, affiliation, and related applications.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

/**
 * Check if the class `Math` is defined, and if not, define it.
 */
if ( ! class_exists( 'Math' ) ) :

	/**
	 * Math Utility Class
	 */
	class Math {

		/**
		 * Calculates a percentage or flat amount from a base value.
		 *
		 * @param float $base_value       The base value to calculate from.
		 * @param float $rate             The rate to apply (percentage or flat amount).
		 * @param bool  $is_percentage    Whether the rate is a percentage (true) or flat amount (false).
		 * @param bool  $return_remainder Whether to return the remainder instead of the calculated amount.
		 * @param int   $precision        The number of decimal places to round to.
		 *
		 * @return float The calculated amount or remainder.
		 */
		public static function apply_rate( float $base_value, float $rate, bool $is_percentage = true, bool $return_remainder = false, int $precision = 2 ): float {
			if ( $is_percentage && $rate === 0.0 ) {
				return 0.0;
			}

			$amount = $is_percentage
				? $base_value * ( $rate / 100 )
				: $rate;

			$result = $return_remainder
				? $base_value - $amount
				: $amount;

			return round( $result, $precision );
		}

		/**
		 * Calculates the break-even point.
		 *
		 * @param float $fixed_costs   Total fixed costs.
		 * @param float $price         Price per unit.
		 * @param float $variable_cost Variable cost per unit.
		 *
		 * @return float The break-even point in units.
		 */
		public static function break_even_point( float $fixed_costs, float $price, float $variable_cost ): float {
			if ( $price <= $variable_cost ) {
				throw new \InvalidArgumentException( "Price must be greater than variable cost." );
			}

			return $fixed_costs / ( $price - $variable_cost );
		}

		/**
		 * Calculates the profit margin.
		 *
		 * @param float $revenue       Total revenue.
		 * @param float $cost          Total cost.
		 * @param bool  $as_percentage Whether to return the result as a percentage.
		 * @param int   $precision     The number of decimal places to round to.
		 *
		 * @return float The profit margin as a decimal or percentage.
		 */
		public static function profit_margin( float $revenue, float $cost, bool $as_percentage = true, int $precision = 2 ): float {
			if ( $revenue <= 0 || $revenue < $cost ) {
				return 0.0;
			}

			$margin = ( $revenue - $cost ) / $revenue;

			return $as_percentage
				? round( $margin * 100, $precision )
				: round( $margin, $precision );
		}

		/**
		 * Calculates the percentage change from one value to another.
		 *
		 * @param float $from_value The initial value.
		 * @param float $to_value   The final value.
		 * @param bool  $format     Whether to format the result as a percentage string.
		 *
		 * @return float|string The percentage change, or a formatted string if $format is true.
		 */
		public static function percentage_change( float $from_value, float $to_value, bool $format = false ) {
			if ( $from_value != 0 ) {
				$diff = round( ( ( $to_value - $from_value ) / $from_value * 100 ), 2 );
			} else {
				$diff = INF;
			}

			return $format ? ( ! is_infinite( $diff ) ? $diff . '%' : '-' ) : $diff;
		}

		/**
		 * Calculates the discount amount based on the original price and discount percentage.
		 *
		 * @param float $original_price      The original price.
		 * @param float $discount_percentage The discount percentage.
		 * @param int   $precision           The number of decimal places to round to.
		 *
		 * @return float The discount amount.
		 */
		public static function discount_amount( float $original_price, float $discount_percentage, int $precision = 2 ): float {
			return round( $original_price * ( $discount_percentage / 100 ), $precision );
		}

		/**
		 * Calculates the final price after applying a discount.
		 *
		 * @param float $original_price      The original price.
		 * @param float $discount_percentage The discount percentage.
		 * @param int   $precision           The number of decimal places to round to.
		 *
		 * @return float The final price after discount.
		 */
		public static function discounted_price( float $original_price, float $discount_percentage, int $precision = 2 ): float {
			$discount_amount = self::discount_amount( $original_price, $discount_percentage, $precision );

			return round( $original_price - $discount_amount, $precision );
		}

		/**
		 * Calculates the tax amount based on the price and tax rate.
		 *
		 * @param float $price     The price before tax.
		 * @param float $tax_rate  The tax rate as a percentage.
		 * @param int   $precision The number of decimal places to round to.
		 *
		 * @return float The tax amount.
		 */
		public static function tax_amount( float $price, float $tax_rate, int $precision = 2 ): float {
			return round( $price * ( $tax_rate / 100 ), $precision );
		}

		/**
		 * Calculates the final price including tax.
		 *
		 * @param float $price     The price before tax.
		 * @param float $tax_rate  The tax rate as a percentage.
		 * @param int   $precision The number of decimal places to round to.
		 *
		 * @return float The final price including tax.
		 */
		public static function price_with_tax( float $price, float $tax_rate, int $precision = 2 ): float {
			$tax_amount = self::tax_amount( $price, $tax_rate, $precision );

			return round( $price + $tax_amount, $precision );
		}

		/**
		 * Calculates the commission amount based on the sale amount and commission rate.
		 *
		 * @param float $sale_amount     The total sale amount.
		 * @param float $commission_rate The commission rate as a percentage.
		 * @param int   $precision       The number of decimal places to round to.
		 *
		 * @return float The commission amount.
		 */
		public static function commission( float $sale_amount, float $commission_rate, int $precision = 2 ): float {
			return round( $sale_amount * ( $commission_rate / 100 ), $precision );
		}

		/**
		 * Calculates the conversion rate between two values.
		 *
		 * @param float $value1    The first value (e.g., number of conversions).
		 * @param float $value2    The second value (e.g., number of visitors).
		 * @param int   $precision The number of decimal places to round to.
		 *
		 * @return float The conversion rate as a percentage.
		 */
		public static function conversion_rate( float $value1, float $value2, int $precision = 2 ): float {
			if ( $value2 == 0 ) {
				return 0;
			}

			return round( ( $value1 / $value2 ) * 100, $precision );
		}

		/**
		 * Calculates the average order value.
		 *
		 * @param float $total_revenue    The total revenue.
		 * @param int   $number_of_orders The number of orders.
		 * @param int   $precision        The number of decimal places to round to.
		 *
		 * @return float The average order value.
		 */
		public static function average_order_value( float $total_revenue, int $number_of_orders, int $precision = 2 ): float {
			if ( $number_of_orders == 0 ) {
				return 0;
			}

			return round( $total_revenue / $number_of_orders, $precision );
		}

		/**
		 * Calculates the customer lifetime value.
		 *
		 * @param float $average_order_value The average order value.
		 * @param float $purchase_frequency  The average number of purchases per customer per year.
		 * @param float $customer_lifespan   The average customer lifespan in years.
		 * @param int   $precision           The number of decimal places to round to.
		 *
		 * @return float The customer lifetime value.
		 */
		public static function customer_lifetime_value( float $average_order_value, float $purchase_frequency, float $customer_lifespan, int $precision = 2 ): float {
			return round( $average_order_value * $purchase_frequency * $customer_lifespan, $precision );
		}

		/**
		 * Calculates the average order value for a specific customer.
		 *
		 * @param float $customer_total_revenue The total revenue from the customer.
		 * @param int   $customer_order_count   The number of orders made by the customer.
		 * @param int   $precision              The number of decimal places to round to.
		 *
		 * @return float The customer's average order value.
		 */
		public static function customer_average_order_value( float $customer_total_revenue, int $customer_order_count, int $precision = 2 ): float {
			if ( $customer_order_count == 0 ) {
				return 0.0;
			}

			return round( $customer_total_revenue / $customer_order_count, $precision );
		}

		/**
		 * Calculates the return on investment (ROI).
		 *
		 * @param float $gain          The total gain from the investment.
		 * @param float $cost          The cost of the investment.
		 * @param bool  $as_percentage Whether to return the result as a percentage.
		 * @param int   $precision     The number of decimal places to round to.
		 *
		 * @return float The ROI as a decimal or percentage.
		 */
		public static function return_on_investment( float $gain, float $cost, bool $as_percentage = true, int $precision = 2 ): float {
			if ( $cost == 0 ) {
				return 0.0;
			}

			$roi = ( $gain - $cost ) / $cost;

			return $as_percentage
				? round( $roi * 100, $precision )
				: round( $roi, $precision );
		}

		/**
		 * Calculates the compound annual growth rate (CAGR).
		 *
		 * @param float $beginning_value The initial value of the investment.
		 * @param float $ending_value    The final value of the investment.
		 * @param int   $num_periods     The number of periods (usually years).
		 * @param int   $precision       The number of decimal places to round to.
		 *
		 * @return float The CAGR as a percentage.
		 */
		public static function compound_annual_growth_rate( float $beginning_value, float $ending_value, int $num_periods, int $precision = 2 ): float {
			if ( $beginning_value <= 0 || $num_periods == 0 ) {
				return 0.0;
			}

			$cagr = pow( ( $ending_value / $beginning_value ), ( 1 / $num_periods ) ) - 1;

			return round( $cagr * 100, $precision );
		}

		/**
		 * Calculates the cost per acquisition (CPA).
		 *
		 * @param float $total_cost         The total cost of marketing or sales efforts.
		 * @param int   $acquired_customers The number of customers acquired.
		 * @param int   $precision          The number of decimal places to round to.
		 *
		 * @return float The cost per acquisition.
		 */
		public static function cost_per_acquisition( float $total_cost, int $acquired_customers, int $precision = 2 ): float {
			if ( $acquired_customers == 0 ) {
				return 0.0;
			}

			return round( $total_cost / $acquired_customers, $precision );
		}

		/**
		 * Calculates the net promoter score (NPS).
		 *
		 * @param int $promoters  The number of promoters (9-10 score).
		 * @param int $passives   The number of passives (7-8 score).
		 * @param int $detractors The number of detractors (0-6 score).
		 * @param int $precision  The number of decimal places to round to.
		 *
		 * @return float The Net Promoter Score.
		 */
		public static function net_promoter_score( int $promoters, int $passives, int $detractors, int $precision = 2 ): float {
			$total_respondents = $promoters + $passives + $detractors;

			if ( $total_respondents == 0 ) {
				return 0.0;
			}

			$nps = ( ( $promoters - $detractors ) / $total_respondents ) * 100;

			return round( $nps, $precision );
		}

		/**
		 * Get the ordinal suffix for a number (st, nd, rd, th).
		 *
		 * @param int $number The number to get the suffix for.
		 *
		 * @return string The ordinal suffix.
		 */
		public static function ordinal_suffix( int $number ): string {
			$suffixes = [ 'th', 'st', 'nd', 'rd' ];
			$mod100   = $number % 100;

			return $number . ( $mod100 >= 11 && $mod100 <= 13 ? 'th' : $suffixes[ $number % 10 ] ?? 'th' );
		}

	}

endif;