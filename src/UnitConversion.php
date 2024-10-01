<?php
/**
 * Unit Conversion Utilities
 *
 * This class provides static methods for converting between various units of measurement.
 * It includes conversions for length, weight, volume, temperature, speed, data storage,
 * area, time, fuel efficiency, energy, pressure, angles, power, frequency, and more.
 * The class is designed to be easy to use and extend with additional conversion types.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

namespace ArrayPress\Utils;

/**
 * Check if the class `UnitConversion` is defined, and if not, define it.
 */
if ( ! class_exists( 'UnitConversion' ) ) :

	/**
	 * UnitConversion class for handling various unit conversions.
	 */
	class UnitConversion {

		/**
		 * Convert temperature between Celsius and Fahrenheit.
		 *
		 * @param float  $value The temperature value to convert.
		 * @param string $from  The unit to convert from ('C' or 'F').
		 * @param string $to    The unit to convert to ('C' or 'F').
		 *
		 * @return float|null The converted temperature, or null if conversion is not possible.
		 */
		public static function temperature( float $value, string $from, string $to ): ?float {
			$from = strtoupper( $from );
			$to   = strtoupper( $to );

			if ( $from === $to ) {
				return $value;
			}

			if ( $from === 'C' && $to === 'F' ) {
				return ( $value * 9 / 5 ) + 32;
			} elseif ( $from === 'F' && $to === 'C' ) {
				return ( $value - 32 ) * 5 / 9;
			}

			return null;
		}

		/**
		 * Convert length between different units.
		 *
		 * @param float  $value The length value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted length, or null if conversion is not possible.
		 */
		public static function length( float $value, string $from, string $to ): ?float {
			$units = [
				'km' => 1000,
				'm'  => 1,
				'cm' => 0.01,
				'mm' => 0.001,
				'mi' => 1609.34,
				'yd' => 0.9144,
				'ft' => 0.3048,
				'in' => 0.0254
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$meters = $value * $units[ $from ];

			return $meters / $units[ $to ];
		}

		/**
		 * Convert weight between different units.
		 *
		 * @param float  $value The weight value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted weight, or null if conversion is not possible.
		 */
		public static function weight( float $value, string $from, string $to ): ?float {
			$units = [
				'kg' => 1000,
				'g'  => 1,
				'mg' => 0.001,
				'lb' => 453.592,
				'oz' => 28.3495
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$grams = $value * $units[ $from ];

			return $grams / $units[ $to ];
		}

		/**
		 * Convert volume between different units.
		 *
		 * @param float  $value The volume value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted volume, or null if conversion is not possible.
		 */
		public static function volume( float $value, string $from, string $to ): ?float {
			$units = [
				'l'     => 1,
				'ml'    => 0.001,
				'gal'   => 3.78541,
				'qt'    => 0.946353,
				'pt'    => 0.473176,
				'cup'   => 0.24,
				'fl oz' => 0.0295735,
				'tbsp'  => 0.0147868,
				'tsp'   => 0.00492892
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$liters = $value * $units[ $from ];

			return $liters / $units[ $to ];
		}

		/**
		 * Convert speed between different units.
		 *
		 * @param float  $value The speed value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted speed, or null if conversion is not possible.
		 */
		public static function speed( float $value, string $from, string $to ): ?float {
			$units = [
				'km/h' => 1,
				'm/s'  => 3.6,
				'mph'  => 1.60934,
				'knot' => 1.852
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$kmh = $value * $units[ $from ];

			return $kmh / $units[ $to ];
		}

		/**
		 * Convert data storage between different units.
		 *
		 * @param float  $value The data storage value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted data storage, or null if conversion is not possible.
		 */
		public static function data_storage( float $value, string $from, string $to ): ?float {
			$units = [
				'b'  => 1,
				'kb' => 1024,
				'mb' => 1048576,
				'gb' => 1073741824,
				'tb' => 1099511627776,
				'pb' => 1125899906842624
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$bytes = $value * $units[ $from ];

			return $bytes / $units[ $to ];
		}

		/**
		 * Convert area between different units.
		 *
		 * @param float  $value The area value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted area, or null if conversion is not possible.
		 */
		public static function area( float $value, string $from, string $to ): ?float {
			$units = [
				'sq m'    => 1,
				'sq km'   => 1000000,
				'sq ft'   => 0.092903,
				'sq yd'   => 0.836127,
				'acre'    => 4046.86,
				'hectare' => 10000
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$sqMeters = $value * $units[ $from ];

			return $sqMeters / $units[ $to ];
		}

		/**
		 * Convert time between different units.
		 *
		 * @param float  $value The time value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted time, or null if conversion is not possible.
		 */
		public static function time( float $value, string $from, string $to ): ?float {
			$units = [
				's'     => 1,
				'min'   => 60,
				'h'     => 3600,
				'day'   => 86400,
				'week'  => 604800,
				'month' => 2592000, // Assuming 30-day month
				'year'  => 31536000 // Assuming 365-day year
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$seconds = $value * $units[ $from ];

			return $seconds / $units[ $to ];
		}

		/**
		 * Convert fuel efficiency between different units.
		 *
		 * @param float  $value The fuel efficiency value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted fuel efficiency, or null if conversion is not possible.
		 */
		public static function fuel_efficiency( float $value, string $from, string $to ): ?float {
			$to_l_per_100km = [
				'mpg'     => function ( $v ) {
					return 235.214583 / $v;
				},
				'km/l'    => function ( $v ) {
					return 100 / $v;
				},
				'l/100km' => function ( $v ) {
					return $v;
				},
			];

			$from_l_per_100km = [
				'mpg'     => function ( $v ) {
					return 235.214583 / $v;
				},
				'km/l'    => function ( $v ) {
					return 100 / $v;
				},
				'l/100km' => function ( $v ) {
					return $v;
				},
			];

			if ( ! isset( $to_l_per_100km[ $from ] ) || ! isset( $from_l_per_100km[ $to ] ) ) {
				return null;
			}

			$l_per_100km = $to_l_per_100km[ $from ]( $value );

			return $from_l_per_100km[ $to ]( $l_per_100km );
		}

		/**
		 * Convert energy between different units.
		 *
		 * @param float  $value The energy value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted energy, or null if conversion is not possible.
		 */
		public static function energy( float $value, string $from, string $to ): ?float {
			$units = [
				'j'    => 1,
				'kj'   => 1000,
				'cal'  => 4.184,
				'kcal' => 4184,
				'wh'   => 3600,
				'kwh'  => 3600000,
				'btu'  => 1055.06
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$joules = $value * $units[ $from ];

			return $joules / $units[ $to ];
		}

		/**
		 * Convert pressure between different units.
		 *
		 * @param float  $value The pressure value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted pressure, or null if conversion is not possible.
		 */
		public static function pressure( float $value, string $from, string $to ): ?float {
			$units = [
				'pa'  => 1,
				'kpa' => 1000,
				'bar' => 100000,
				'psi' => 6894.76,
				'atm' => 101325
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$pascals = $value * $units[ $from ];

			return $pascals / $units[ $to ];
		}

		/**
		 * Convert angles between different units.
		 *
		 * @param float  $value The angle value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted angle, or null if conversion is not possible.
		 */
		public static function angle( float $value, string $from, string $to ): ?float {
			$units = [
				'degree'  => 1,
				'radian'  => 57.2958,
				'gradian' => 0.9
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$degrees = $value * $units[ $from ];

			return $degrees / $units[ $to ];
		}

		/**
		 * Convert power between different units.
		 *
		 * @param float  $value The power value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted power, or null if conversion is not possible.
		 */
		public static function power( float $value, string $from, string $to ): ?float {
			$units = [
				'w'     => 1,
				'kw'    => 1000,
				'hp'    => 745.7,
				'btu/h' => 0.293071
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$watts = $value * $units[ $from ];

			return $watts / $units[ $to ];
		}

		/**
		 * Convert frequency between different units.
		 *
		 * @param float  $value The frequency value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted frequency, or null if conversion is not possible.
		 */
		public static function frequency( float $value, string $from, string $to ): ?float {
			$units = [
				'hz'  => 1,
				'khz' => 1000,
				'mhz' => 1000000,
				'ghz' => 1000000000
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$hertz = $value * $units[ $from ];

			return $hertz / $units[ $to ];
		}

		/**
		 * Convert digital image resolution.
		 *
		 * @param float  $value The resolution value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted resolution, or null if conversion is not possible.
		 */
		public static function digital_resolution( float $value, string $from, string $to ): ?float {
			$units = [
				'ppi' => 1,
				'dpi' => 1
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			// PPI and DPI are equivalent in digital context
			return $value;
		}

		/**
		 * Convert cooking measurements.
		 *
		 * @param float  $value The measurement value to convert.
		 * @param string $from  The unit to convert from.
		 * @param string $to    The unit to convert to.
		 *
		 * @return float|null The converted measurement, or null if conversion is not possible.
		 */
		public static function cooking( float $value, string $from, string $to ): ?float {
			$units = [
				'tsp'    => 1,
				'tbsp'   => 3,
				'fl oz'  => 6,
				'cup'    => 48,
				'pint'   => 96,
				'quart'  => 192,
				'gallon' => 768,
				'ml'     => 0.202884,
				'l'      => 202.884
			];

			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( ! isset( $units[ $from ] ) || ! isset( $units[ $to ] ) ) {
				return null;
			}

			$teaspoons = $value * $units[ $from ];

			return $teaspoons / $units[ $to ];
		}

		/**
		 * Convert shoe sizes between different systems.
		 *
		 * @param float  $value The shoe size to convert.
		 * @param string $from  The system to convert from.
		 * @param string $to    The system to convert to.
		 *
		 * @return float|null The converted shoe size, or null if conversion is not possible.
		 */
		public static function shoe_size( float $value, string $from, string $to ): ?float {
			$conversions = [
				'us-men-to-eu'   => function ( $size ) {
					return ( $size + 31 ) * 2 / 3;
				},
				'eu-to-us-men'   => function ( $size ) {
					return $size * 3 / 2 - 31;
				},
				'us-women-to-eu' => function ( $size ) {
					return ( $size + 30 ) * 2 / 3;
				},
				'eu-to-us-women' => function ( $size ) {
					return $size * 3 / 2 - 30;
				},
				'uk-to-eu'       => function ( $size ) {
					return $size * 2 / 3 + 33;
				},
				'eu-to-uk'       => function ( $size ) {
					return ( $size - 33 ) * 3 / 2;
				}
			];

			$key = strtolower( $from ) . '-to-' . strtolower( $to );

			if ( isset( $conversions[ $key ] ) ) {
				return $conversions[ $key ]( $value );
			}

			return null;
		}

		/**
		 * Get all possible conversions for a given value and unit.
		 *
		 * @param float  $value The value to convert.
		 * @param string $unit  The unit of the value.
		 *
		 * @return array An array of all possible conversions.
		 */
		public static function get_all_conversions( float $value, string $unit ): array {
			$conversionMethods = [
				'temperature',
				'length',
				'weight',
				'volume',
				'speed',
				'dataStorage',
				'area',
				'time',
				'fuelEfficiency',
				'energy',
				'pressure',
				'angle',
				'power',
				'frequency',
				'digitalResolution',
				'cooking'
			];

			$results = [];

			foreach ( $conversionMethods as $method ) {
				$conversions = self::get_conversions_for_method( $value, $unit, $method );
				if ( ! empty( $conversions ) ) {
					$results[ $method ] = $conversions;
					break; // We found the correct conversion type, no need to continue
				}
			}

			return $results;
		}

		/**
		 * Get conversions for a specific method.
		 *
		 * @param float  $value  The value to convert.
		 * @param string $unit   The unit of the value.
		 * @param string $method The conversion method to use.
		 *
		 * @return array An array of conversions for the given method.
		 */
		private static function get_conversions_for_method( float $value, string $unit, string $method ): array {
			$reflectionMethod = new \ReflectionMethod( __CLASS__, $method );
			$parameters       = $reflectionMethod->getParameters();
			$unitParameter    = $parameters[1]->getName();

			$conversions = [];
			$methodUnits = self::get_units_for_method( $method );

			foreach ( $methodUnits as $toUnit ) {
				if ( $unit !== $toUnit ) {
					$result = self::$method( $value, $unit, $toUnit );
					if ( $result !== null ) {
						$conversions[ $toUnit ] = $result;
					}
				}
			}

			return $conversions;
		}

		/**
		 * Get available units for a specific conversion method.
		 *
		 * @param string $method The conversion method.
		 *
		 * @return array An array of available units for the method.
		 */
		private static function get_units_for_method( string $method ): array {
			$units = [
				'temperature'       => [ 'C', 'F' ],
				'length'            => [ 'm', 'km', 'ft', 'mi' ],
				'weight'            => [ 'kg', 'g', 'lb', 'oz' ],
				'volume'            => [ 'l', 'ml', 'gal', 'fl oz' ],
				'speed'             => [ 'km/h', 'm/s', 'mph' ],
				'dataStorage'       => [ 'b', 'kb', 'mb', 'gb' ],
				'area'              => [ 'sq m', 'sq km', 'sq ft', 'acre' ],
				'time'              => [ 's', 'min', 'h', 'day' ],
				'fuelEfficiency'    => [ 'mpg', 'km/l', 'l/100km' ],
				'energy'            => [ 'j', 'kj', 'cal', 'kcal' ],
				'pressure'          => [ 'pa', 'kpa', 'bar', 'psi' ],
				'angle'             => [ 'degree', 'radian', 'gradian' ],
				'power'             => [ 'w', 'kw', 'hp' ],
				'frequency'         => [ 'hz', 'khz', 'mhz' ],
				'digitalResolution' => [ 'ppi', 'dpi' ],
				'cooking'           => [ 'tsp', 'tbsp', 'cup', 'ml' ]
			];

			return $units[ $method ] ?? [];
		}

		/**
		 * Convert color between HEX and RGB formats.
		 *
		 * @param string $value The color value to convert.
		 * @param string $from  The format to convert from ('hex' or 'rgb').
		 * @param string $to    The format to convert to ('hex' or 'rgb').
		 *
		 * @return string|null The converted color, or null if conversion is not possible.
		 */
		public static function color( string $value, string $from, string $to ): ?string {
			$from = strtolower( $from );
			$to   = strtolower( $to );

			if ( $from === $to ) {
				return $value;
			}

			if ( $from === 'hex' && $to === 'rgb' ) {
				$hex = ltrim( $value, '#' );
				if ( strlen( $hex ) === 3 ) {
					$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
				}
				if ( strlen( $hex ) !== 6 ) {
					return null;
				}
				$r = hexdec( substr( $hex, 0, 2 ) );
				$g = hexdec( substr( $hex, 2, 2 ) );
				$b = hexdec( substr( $hex, 4, 2 ) );

				return "rgb($r, $g, $b)";
			} elseif ( $from === 'rgb' && $to === 'hex' ) {
				if ( preg_match( '/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/', $value, $matches ) ) {
					$r = intval( $matches[1] );
					$g = intval( $matches[2] );
					$b = intval( $matches[3] );

					return sprintf( "#%02x%02x%02x", $r, $g, $b );
				}
			}

			return null;
		}
	}
endif;