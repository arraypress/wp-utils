<?php
/**
 * Reflection Utilities for PHP
 *
 * @package       ArrayPress/Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Check if the class `Reflector` is defined, and if not, define it.
 */
if ( ! class_exists( 'Reflector' ) ) :
	/**
	 * Reflector Class
	 *
	 * Provides utility methods for object reflection.
	 */
	class Reflector {

		/**
		 * Get reflected properties from an object or class.
		 *
		 * @param object|string $object_or_class The object or class name.
		 * @param int           $filter          Optional. Filter for property types (default: all).
		 *
		 * @return array The array of property names and their values.
		 * @throws ReflectionException If the class cannot be reflected.
		 */
		public static function get_properties( $object_or_class, int $filter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE ): array {
			$properties = [];
			if ( is_object( $object_or_class ) || class_exists( $object_or_class ) ) {
				$reflectedClass      = new ReflectionClass( $object_or_class );
				$reflectedProperties = $reflectedClass->getProperties( $filter );
				foreach ( $reflectedProperties as $property ) {
					$propertyName = $property->getName();
					$property->setAccessible( true );
					if ( is_object( $object_or_class ) && isset( $object_or_class->{$propertyName} ) ) {
						$propertyValue               = $property->getValue( $object_or_class );
						$properties[ $propertyName ] = $propertyValue;
					} elseif ( is_string( $object_or_class ) && $property->isStatic() ) {
						$propertyValue               = $property->getValue();
						$properties[ $propertyName ] = $propertyValue;
					}
				}
			}

			return $properties;
		}

		/**
		 * Get a reflected property from an object or class.
		 *
		 * @param object|string $object_or_class The object or class name.
		 * @param string        $propertyName    The name of the property.
		 *
		 * @return mixed|null The value of the property, or null if it doesn't exist.
		 */
		public static function get_property( $object_or_class, string $propertyName ) {
			try {
				$reflectedClass = new ReflectionClass( $object_or_class );
				$property       = $reflectedClass->getProperty( $propertyName );
				$property->setAccessible( true );

				return $property->getValue( is_object( $object_or_class ) ? $object_or_class : null );
			} catch ( ReflectionException $e ) {
				return null;
			}
		}

		/**
		 * Set a reflected property on an object or class.
		 *
		 * @param object|string $object_or_class The object or class name.
		 * @param string        $propertyName    The name of the property.
		 * @param mixed         $value           The value to set.
		 *
		 * @return bool True if successful, false otherwise.
		 */
		public static function set_property( $object_or_class, string $propertyName, $value ): bool {
			try {
				$reflectedClass = new ReflectionClass( $object_or_class );
				$property       = $reflectedClass->getProperty( $propertyName );
				$property->setAccessible( true );
				$property->setValue( is_object( $object_or_class ) ? $object_or_class : null, $value );

				return true;
			} catch ( ReflectionException $e ) {
				return false;
			}
		}

		/**
		 * Get reflected methods from an object or class.
		 *
		 * @param object|string $object_or_class The object or class name.
		 * @param int           $filter          Optional. Filter for method types (default: all).
		 *
		 * @return array The array of method names and their ReflectionMethod objects.
		 * @throws ReflectionException If the class cannot be reflected.
		 */
		public static function get_methods( $object_or_class, int $filter = ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PRIVATE ): array {
			$methods = [];
			if ( is_object( $object_or_class ) || class_exists( $object_or_class ) ) {
				$reflectedClass   = new ReflectionClass( $object_or_class );
				$reflectedMethods = $reflectedClass->getMethods( $filter );
				foreach ( $reflectedMethods as $method ) {
					$methods[ $method->getName() ] = $method;
				}
			}

			return $methods;
		}

		/**
		 * Call a reflected method on an object or class.
		 *
		 * @param object|string $object_or_class The object or class name.
		 * @param string        $method_name     The name of the method.
		 * @param array         $parameters      Optional. The parameters to pass to the method.
		 *
		 * @return mixed The result of the method call.
		 * @throws ReflectionException If the method cannot be reflected or called.
		 */
		public static function call_method( $object_or_class, string $method_name, array $parameters = [] ) {
			$reflectedClass = new ReflectionClass( $object_or_class );
			$method         = $reflectedClass->getMethod( $method_name );
			$method->setAccessible( true );

			return $method->invokeArgs( is_object( $object_or_class ) ? $object_or_class : null, $parameters );
		}
	}
endif;