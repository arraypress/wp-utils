<?php
/**
 * Class GeoDistanceCalculator
 *
 * A simple utility class for calculating distances between two geographical points
 * using the Haversine formula. Supports calculations in miles and kilometers.
 *
 * Example usage:
 * ```php
 * $calculator = new GeoDistanceCalculator(
 * ['latitude' => 40.7128, 'longitude' => -74.0060],     // Point A
 * ['latitude' => 51.5074, 'longitude' => -0.1278],      // Point B
 * 'km'                                                   // Optional: unit (default: 'mi')
 * );
 * $distance = $calculator->getDistance();
 * ```
 *
 * @package     ArrayPress/Utils
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Math;

use InvalidArgumentException;

class GeoDistance {

	/**
	 * First point coordinates
	 *
	 * @var array
	 */
	private array $pointA;

	/**
	 * Second point coordinates
	 *
	 * @var array
	 */
	private array $pointB;

	/**
	 * Unit of measurement
	 *
	 * @var string
	 */
	private string $unit;

	/**
	 * Valid units and their Earth radius values
	 *
	 * @var array
	 */
	private const EARTH_RADIUS = [
		'mi' => 3959,
		'km' => 6371
	];

	/**
	 * GeoDistanceCalculator constructor.
	 *
	 * @param array  $pointA Array with 'latitude' and 'longitude' keys
	 * @param array  $pointB Array with 'latitude' and 'longitude' keys
	 * @param string $unit   Unit of measurement ('mi' or 'km')
	 *
	 * @throws InvalidArgumentException If coordinates are invalid
	 */
	public function __construct( array $pointA, array $pointB, string $unit = 'mi' ) {
		$this->validate_coordinates( $pointA, 'Point A' );
		$this->validate_coordinates( $pointB, 'Point B' );
		$this->validate_unit( $unit );

		$this->pointA = $pointA;
		$this->pointB = $pointB;
		$this->unit   = $unit;
	}

	/**
	 * Calculate the distance between the two points
	 *
	 * @return float The distance in the specified unit, rounded to 2 decimal places
	 */
	public function get_distance(): float {
		// Convert coordinates to radians
		$lat1 = deg2rad( $this->pointA['latitude'] );
		$lon1 = deg2rad( $this->pointA['longitude'] );
		$lat2 = deg2rad( $this->pointB['latitude'] );
		$lon2 = deg2rad( $this->pointB['longitude'] );

		// Calculate differences
		$latDiff = $lat2 - $lat1;
		$lonDiff = $lon2 - $lon1;

		// Haversine formula
		$a = sin( $latDiff / 2 ) * sin( $latDiff / 2 ) +
		     cos( $lat1 ) * cos( $lat2 ) *
		     sin( $lonDiff / 2 ) * sin( $lonDiff / 2 );

		$c = 2 * asin( sqrt( $a ) );

		return round( self::EARTH_RADIUS[ $this->unit ] * $c, 2 );
	}

	/**
	 * Validate coordinate array format and values
	 *
	 * @param array  $point     Coordinate array to validate
	 * @param string $pointName Name of the point for error messages
	 *
	 * @throws InvalidArgumentException
	 */
	private function validate_coordinates( array $point, string $pointName ): void {
		if ( ! isset( $point['latitude'] ) || ! isset( $point['longitude'] ) ) {
			throw new InvalidArgumentException(
				"$pointName must contain 'latitude' and 'longitude' keys"
			);
		}

		$lat = $point['latitude'];
		$lon = $point['longitude'];

		if ( ! is_numeric( $lat ) || $lat < - 90 || $lat > 90 ) {
			throw new InvalidArgumentException(
				"$pointName latitude must be between -90 and 90 degrees"
			);
		}

		if ( ! is_numeric( $lon ) || $lon < - 180 || $lon > 180 ) {
			throw new InvalidArgumentException(
				"$pointName longitude must be between -180 and 180 degrees"
			);
		}
	}

	/**
	 * Validate unit of measurement
	 *
	 * @param string $unit Unit to validate
	 *
	 * @throws InvalidArgumentException
	 */
	private function validate_unit( string $unit ): void {
		if ( ! array_key_exists( $unit, self::EARTH_RADIUS ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Invalid unit. Supported units are: %s', implode( ', ', array_keys( self::EARTH_RADIUS ) ) )
			);
		}
	}

}