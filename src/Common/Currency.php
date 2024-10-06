<?php
/**
 * Currency Utilities
 *
 * This class provides comprehensive utility functions for handling currency-related operations,
 * including conversions, formatting, and basic calculations. It supports operations such as
 * converting to cents, formatting values with currency symbols, and applying discounts or tax.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.2.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

/**
 * Check if the class `Currency` is defined, and if not, define it.
 */
if ( ! class_exists( 'Currency' ) ) :

	/**
	 * Currency Utilities
	 *
	 * Provides comprehensive utility functions for handling currency-related operations.
	 * This includes currency conversions, formatting with currency symbols, rounding,
	 * and tax or discount calculations. The class also supports various international
	 * currencies and provides methods for parsing and formatting large amounts.
	 */
	class Currency {

		/** United States Dollar - The official currency of the United States */
		public const USD = 'USD';

		/** Euro - The official currency of the European Union */
		public const EUR = 'EUR';

		/** British Pound Sterling - The official currency of the United Kingdom */
		public const GBP = 'GBP';

		/** Japanese Yen - The official currency of Japan */
		public const JPY = 'JPY';

		/** Australian Dollar - The official currency of Australia */
		public const AUD = 'AUD';

		/** Canadian Dollar - The official currency of Canada */
		public const CAD = 'CAD';

		/** Swiss Franc - The official currency of Switzerland and Liechtenstein */
		public const CHF = 'CHF';

		/** Chinese Yuan Renminbi - The official currency of the People's Republic of China */
		public const CNY = 'CNY';

		/** Swedish Krona - The official currency of Sweden */
		public const SEK = 'SEK';

		/** New Zealand Dollar - The official currency of New Zealand */
		public const NZD = 'NZD';

		/** Mexican Peso - The official currency of Mexico */
		public const MXN = 'MXN';

		/** Singapore Dollar - The official currency of Singapore */
		public const SGD = 'SGD';

		/** Hong Kong Dollar - The official currency of Hong Kong */
		public const HKD = 'HKD';

		/** Norwegian Krone - The official currency of Norway */
		public const NOK = 'NOK';

		/** South Korean Won - The official currency of South Korea */
		public const KRW = 'KRW';

		/** Turkish Lira - The official currency of Turkey */
		public const TRY = 'TRY';

		/** Russian Ruble - The official currency of Russia */
		public const RUB = 'RUB';

		/** Indian Rupee - The official currency of India */
		public const INR = 'INR';

		/** Brazilian Real - The official currency of Brazil */
		public const BRL = 'BRL';

		/** South African Rand - The official currency of South Africa */
		public const ZAR = 'ZAR';

		// Currency symbols
		private const SYMBOLS = [
			self::USD => '$',
			self::EUR => '€',
			self::GBP => '£',
			self::JPY => '¥',
			self::AUD => 'A$',
			self::CAD => 'C$',
			self::CHF => 'Fr',
			self::CNY => '¥',
			self::SEK => 'kr',
			self::NZD => 'NZ$',
			self::MXN => '$',
			self::SGD => 'S$',
			self::HKD => 'HK$',
			self::NOK => 'kr',
			self::KRW => '₩',
			self::TRY => '₺',
			self::RUB => '₽',
			self::INR => '₹',
			self::BRL => 'R$',
			self::ZAR => 'R',
		];

		// Currencies with no decimal places
		private const ZERO_DECIMAL_CURRENCIES = [ self::JPY, self::KRW ];

		/**
		 * Convert a monetary or percentage value to an integer representation in cents.
		 *
		 * @param mixed $amount The value to be converted.
		 *
		 * @return int The integer representation of the value in cents, or 0 if invalid.
		 */
		public static function to_cents( $amount ): int {
			$amount = (string) $amount;

			if ( trim( $amount ) === '' ) {
				return 0;
			}

			$is_negative = strpos( $amount, '-' ) === 0;
			$amount      = ltrim( $amount, '-' );

			$amount = preg_replace( '/[^0-9.,]/', '', $amount );
			$amount = str_replace( ',', '.', $amount );

			if ( ! is_numeric( $amount ) ) {
				return 0;
			}

			$cents = (int) round( floatval( $amount ) * 100 );

			return $is_negative ? - $cents : $cents;
		}

		/**
		 * Convert cents to a float value.
		 *
		 * @param int $cents The amount in cents.
		 *
		 * @return float The amount as a float.
		 */
		public static function from_cents( int $cents ): float {
			return $cents / 100;
		}

		/**
		 * Format a monetary value as a string with currency symbol.
		 *
		 * @param float  $amount   The amount to format.
		 * @param string $currency The currency code (e.g., 'USD', 'EUR').
		 * @param string $locale   The locale to use for formatting (default: 'en_US').
		 *
		 * @return string Formatted currency string.
		 */
		public static function format( float $amount, string $currency = self::USD, string $locale = 'en_US' ): string {
			$formatter = new \NumberFormatter( $locale, \NumberFormatter::CURRENCY );

			return $formatter->formatCurrency( $amount, $currency );
		}

		/**
		 * Convert an amount from one currency to another.
		 *
		 * @param float  $amount        The amount to convert.
		 * @param string $from_currency The currency to convert from.
		 * @param string $to_currency   The currency to convert to.
		 * @param float  $exchange_rate The exchange rate to use (1 unit of from_currency in to_currency).
		 *
		 * @return float The converted amount. Returns the original amount if conversion cannot be performed.
		 */
		public static function convert( float $amount, string $from_currency, string $to_currency, float $exchange_rate ): float {
			if ( ! self::is_valid_currency_code( $from_currency ) || ! self::is_valid_currency_code( $to_currency ) ) {
				return $amount;
			}

			if ( $from_currency === $to_currency ) {
				return $amount;
			}

			if ( $exchange_rate <= 0 ) {
				return $amount;
			}

			$converted_amount = $amount * $exchange_rate;

			$from_decimals = self::get_decimal_places( $from_currency );
			$to_decimals   = self::get_decimal_places( $to_currency );

			if ( $from_decimals === 0 && $to_decimals > 0 ) {
				// When converting from a zero-decimal currency to a currency with decimals,
				// we round to the nearest cent
				$converted_amount = round( $converted_amount, $to_decimals );
			} else {
				$converted_amount = round( $converted_amount, $to_decimals );
			}

			return $converted_amount;
		}

		/**
		 * Get the currency symbol for a given currency code.
		 *
		 * @param string $currency_code The ISO 4217 currency code.
		 *
		 * @return string The currency symbol.
		 */
		public static function get_symbol( string $currency_code ): string {
			return self::SYMBOLS[ $currency_code ] ?? $currency_code;
		}

		/**
		 * Round a monetary value to the nearest cent.
		 *
		 * @param float $amount The amount to round.
		 *
		 * @return float The rounded amount.
		 */
		public static function round_to_cent( float $amount ): float {
			return round( $amount, 2 );
		}

		/**
		 * Calculate the tax amount for a given base amount.
		 *
		 * @param float $amount       The base amount.
		 * @param float $tax_rate     The tax rate as a percentage.
		 * @param bool  $is_inclusive Whether the tax is inclusive (true) or exclusive (false). Default is false (exclusive).
		 * @param int   $precision    The number of decimal places to round to. Default is 2.
		 *
		 * @return float The tax amount.
		 */
		public static function calculate_tax( float $amount, float $tax_rate, bool $is_inclusive = false, int $precision = 2 ): float {
			$amount   = max( 0, $amount );
			$tax_rate = max( 0, $tax_rate );

			if ( $is_inclusive ) {
				$tax_amount = $amount - ( $amount / ( 1 + ( $tax_rate / 100 ) ) );
			} else {
				$tax_amount = $amount * ( $tax_rate / 100 );
			}

			return round( $tax_amount, $precision );
		}

		/**
		 * Calculate the total amount including tax.
		 *
		 * @param float $amount       The base amount.
		 * @param float $tax_rate     The tax rate as a percentage.
		 * @param bool  $is_inclusive Whether the tax is inclusive (true) or exclusive (false). Default is false (exclusive).
		 * @param int   $precision    The number of decimal places to round to. Default is 2.
		 *
		 * @return float The total amount including tax.
		 */
		public static function calculate_total_with_tax( float $amount, float $tax_rate, bool $is_inclusive = false, int $precision = 2 ): float {
			$amount   = max( 0, $amount );
			$tax_rate = max( 0, $tax_rate );

			if ( $is_inclusive ) {
				$total_amount = $amount;
			} else {
				$tax_amount   = self::calculate_tax( $amount, $tax_rate, false, $precision );
				$total_amount = $amount + $tax_amount;
			}

			return round( $total_amount, $precision );
		}

		/**
		 * Apply a discount to an amount.
		 *
		 * @param float $amount        The original amount.
		 * @param float $discount_rate The discount rate as a percentage.
		 * @param bool  $is_inclusive  Whether the discount is inclusive (true) or exclusive (false). Default is false (exclusive).
		 * @param int   $precision     The number of decimal places to round to. Default is 2.
		 *
		 * @return float The discounted amount.
		 */
		public static function apply_discount( float $amount, float $discount_rate, bool $is_inclusive = false, int $precision = 2 ): float {
			$amount        = max( 0, $amount );
			$discount_rate = max( 0, min( 100, $discount_rate ) );

			if ( $is_inclusive ) {
				$discounted_amount = $amount / ( 1 + ( $discount_rate / 100 ) );
			} else {
				$discounted_amount = $amount * ( 1 - ( $discount_rate / 100 ) );
			}

			return round( $discounted_amount, $precision );
		}

		/**
		 * Format a large monetary value with abbreviations (K, M, B, T).
		 *
		 * @param float $amount   The amount to format.
		 * @param int   $decimals The number of decimal places to show.
		 *
		 * @return string Formatted string with abbreviation.
		 */
		public static function format_large_amount( float $amount, int $decimals = 1 ): string {
			$suffixes    = [ '', 'K', 'M', 'B', 'T' ];
			$suffixIndex = 0;

			while ( $amount >= 1000 && $suffixIndex < count( $suffixes ) - 1 ) {
				$amount /= 1000;
				$suffixIndex ++;
			}

			return number_format( $amount, $decimals ) . $suffixes[ $suffixIndex ];
		}

		/**
		 * Parse a currency string into a float value.
		 *
		 * @param string $currency_string The currency string to parse.
		 *
		 * @return float The parsed amount.
		 */
		public static function parse_currency_string( string $currency_string ): float {
			$cleaned = preg_replace( '/[^0-9,.-]/', '', $currency_string );
			$cleaned = str_replace( ',', '.', $cleaned );

			return floatval( $cleaned );
		}

		/**
		 * Check if a currency code is valid according to ISO 4217.
		 *
		 * @param string $currency_code The currency code to check.
		 *
		 * @return bool True if valid, false otherwise.
		 */
		public static function is_valid_currency_code( string $currency_code ): bool {
			return isset( self::SYMBOLS[ $currency_code ] );
		}

		/**
		 * Get the number of decimal places typically used for a currency.
		 *
		 * @param string $currency_code The ISO 4217 currency code.
		 *
		 * @return int The number of decimal places.
		 */
		public static function get_decimal_places( string $currency_code ): int {
			return in_array( strtoupper( $currency_code ), self::ZERO_DECIMAL_CURRENCIES ) ? 0 : 2;
		}

		/**
		 * Strip currency symbols and formatting from a string.
		 *
		 * @param string $amount The amount string to strip.
		 *
		 * @return string The stripped amount string.
		 */
		public static function strip_currency_symbols( string $amount ): string {
			$amount = trim( $amount );
			$amount = preg_replace( '/[^\d.,+-]/', '', $amount );

			return preg_replace( '/^[.,]|[.,]$/', '', $amount );
		}

		/**
		 * Get all supported currency codes.
		 *
		 * @return array An array of all supported currency codes.
		 */
		public static function get_supported_currencies(): array {
			return array_keys( self::SYMBOLS );
		}

		/**
		 * Format a monetary value without currency symbol.
		 *
		 * @param float  $amount   The amount to format.
		 * @param string $currency The currency code (for decimal places).
		 *
		 * @return string Formatted amount string.
		 */
		public static function format_numeric( float $amount, string $currency = self::USD ): string {
			$decimals = self::get_decimal_places( $currency );

			return number_format( $amount, $decimals, '.', ',' );
		}

		/**
		 * Compare two monetary values.
		 *
		 * @param float $amount1 The first amount.
		 * @param float $amount2 The second amount.
		 *
		 * @return int Returns -1 if $amount1 < $amount2, 0 if equal, 1 if $amount1 > $amount2.
		 */
		public static function compare( float $amount1, float $amount2 ): int {
			$epsilon = 0.00001; // To handle floating point imprecision
			if ( abs( $amount1 - $amount2 ) < $epsilon ) {
				return 0;
			}

			return $amount1 < $amount2 ? - 1 : 1;
		}

	}

endif;