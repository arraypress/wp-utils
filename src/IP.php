<?php
/**
 * IP Utility Class
 *
 * This class provides a comprehensive set of utility methods for working with IP addresses,
 * including validation, range checking, CIDR operations, and user IP detection.
 * It supports both IPv4 and IPv6 addresses.
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
 * Check if the class `IP` is defined, and if not, define it.
 */
if ( ! class_exists( 'IP' ) ) :

	/**
	 * Class IP
	 *
	 * A utility class for IP address operations and validations.
	 */
	class IP {

		/**
		 * Validate an IP address (IPv4 or IPv6).
		 *
		 * @param string $ip The IP address to validate.
		 *
		 * @return bool True if the IP address is valid, false otherwise.
		 */
		public static function is_valid( string $ip ): bool {
			return filter_var( $ip, FILTER_VALIDATE_IP ) !== false;
		}

		/**
		 * Validate an IPv4 address.
		 *
		 * @param string $ip The IP address to validate.
		 *
		 * @return bool True if the IP address is a valid IPv4 address, false otherwise.
		 */
		public static function is_valid_ipv4( string $ip ): bool {
			return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) !== false;
		}

		/**
		 * Validate an IPv6 address.
		 *
		 * @param string $ip The IP address to validate.
		 *
		 * @return bool True if the IP address is a valid IPv6 address, false otherwise.
		 */
		public static function is_valid_ipv6( string $ip ): bool {
			return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) !== false;
		}

		/**
		 * Checks if a given input is a valid IP address or IP range in CIDR format.
		 *
		 * @param string $ip_or_range The IP address or range to validate.
		 *
		 * @return bool True if the input is a valid IP address or IP range, false otherwise.
		 */
		public static function is_valid_ip_or_range( string $ip_or_range ): bool {
			if ( empty( $ip_or_range ) ) {
				return false;
			}

			return self::is_valid( $ip_or_range ) || self::is_valid_range( $ip_or_range );
		}

		/**
		 * Checks if an IP address or range is in a valid CIDR format.
		 *
		 * @param string $range The IP address or range to validate.
		 *
		 * @return bool True if the format is valid, false otherwise.
		 */
		public static function is_valid_range( string $range ): bool {
			if ( strpos( $range, '/' ) === false ) {
				return false;
			}

			list( $ip, $subnet ) = explode( '/', $range, 2 );

			if ( self::is_valid_ipv4( $ip ) ) {
				return is_numeric( $subnet ) && $subnet >= 0 && $subnet <= 32;
			} elseif ( self::is_valid_ipv6( $ip ) ) {
				return is_numeric( $subnet ) && $subnet >= 0 && $subnet <= 128;
			}

			return false;
		}

		/**
		 * Checks if an IP address is within a specified range in CIDR format.
		 *
		 * @param string $ip    The IP address to check.
		 * @param string $range The IP range in CIDR format to compare against.
		 *
		 * @return bool True if the IP address is within the range, false otherwise.
		 */
		public static function is_in_range( string $ip, string $range ): bool {
			if ( ! self::is_valid( $ip ) || ! self::is_valid_range( $range ) ) {
				return false;
			}

			list( $subnet, $bits ) = explode( '/', $range );
			$bits = intval( $bits );

			if ( self::is_valid_ipv4( $ip ) ) {
				return self::is_ipv4_in_range( $ip, $subnet, $bits );
			} elseif ( self::is_valid_ipv6( $ip ) ) {
				return self::is_ipv6_in_range( $ip, $subnet, $bits );
			}

			return false;
		}

		/**
		 * Checks if an IPv4 address is within a specified range.
		 *
		 * @param string $ip     The IPv4 address to check.
		 * @param string $subnet The subnet of the range.
		 * @param int    $bits   The number of network bits.
		 *
		 * @return bool True if the IPv4 address is within the range, false otherwise.
		 */
		private static function is_ipv4_in_range( string $ip, string $subnet, int $bits ): bool {
			$ip_long     = ip2long( $ip );
			$subnet_long = ip2long( $subnet );
			$mask        = - 1 << ( 32 - $bits );
			$subnet_long &= $mask;

			return ( $ip_long & $mask ) === $subnet_long;
		}

		/**
		 * Checks if an IPv6 address is within a specified range.
		 *
		 * @param string $ip     The IPv6 address to check.
		 * @param string $subnet The subnet of the range.
		 * @param int    $bits   The number of network bits.
		 *
		 * @return bool True if the IPv6 address is within the range, false otherwise.
		 */
		private static function is_ipv6_in_range( string $ip, string $subnet, int $bits ): bool {
			$ip_bin     = inet_pton( $ip );
			$subnet_bin = inet_pton( $subnet );
			$mask_bin   = self::create_ipv6_mask( $bits );

			return ( ( $ip_bin & $mask_bin ) === ( $subnet_bin & $mask_bin ) );
		}

		/**
		 * Creates an IPv6 mask based on the number of network bits.
		 *
		 * @param int $bits The number of network bits.
		 *
		 * @return string The binary representation of the IPv6 mask.
		 */
		private static function create_ipv6_mask( int $bits ): string {
			$mask = str_repeat( "\xFF", $bits >> 3 );
			$bits &= 7;
			if ( $bits ) {
				$mask .= chr( 0xFF << ( 8 - $bits ) );
			}

			return str_pad( $mask, 16, "\x00" );
		}

		/**
		 * Generate a CIDR notation from an IP address and prefix length.
		 *
		 * @param string $ip_address    The IP address (IPv4 or IPv6).
		 * @param int    $prefix_length The prefix length.
		 *
		 * @return string The CIDR notation, or an empty string on invalid input.
		 */
		public static function to_cidr( string $ip_address, int $prefix_length ): string {
			if ( ! self::is_valid( $ip_address ) ) {
				return '';
			}

			$max_prefix = self::is_valid_ipv4( $ip_address ) ? 32 : 128;
			if ( $prefix_length < 0 || $prefix_length > $max_prefix ) {
				return '';
			}

			return $ip_address . '/' . $prefix_length;
		}

		/**
		 * Get the current user's IP address.
		 *
		 * @return string|null The user's IP address, or null if it can't be determined.
		 */
		public static function get_user_ip(): ?string {
			$ip_keys = [
				'HTTP_CLIENT_IP',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_X_CLUSTER_CLIENT_IP',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'REMOTE_ADDR'
			];
			foreach ( $ip_keys as $key ) {
				if ( array_key_exists( $key, $_SERVER ) === true ) {
					foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
						$ip = trim( $ip );
						if ( self::is_valid( $ip ) ) {
							return $ip;
						}
					}
				}
			}

			return null;
		}

		/**
		 * Convert an IP address to its decimal representation.
		 *
		 * @param string $ip The IP address to convert.
		 *
		 * @return string The decimal representation of the IP, or an empty string on failure.
		 */
		public static function to_decimal( string $ip ): string {
			if ( self::is_valid_ipv4( $ip ) ) {
				return (string) ip2long( $ip );
			} elseif ( self::is_valid_ipv6( $ip ) ) {
				$binary = inet_pton( $ip );
				$hex    = unpack( 'H*hex', $binary )['hex'];
				$dec    = '0';
				for ( $i = 0; $i < strlen( $hex ); $i ++ ) {
					$dec = bcadd( bcmul( $dec, '16' ), (string) hexdec( $hex[ $i ] ) );
				}

				return $dec;
			}

			return '';
		}

		/**
		 * Convert a decimal representation back to an IP address.
		 *
		 * @param string $decimal The decimal representation of an IP.
		 * @param bool   $is_ipv6 Whether the decimal represents an IPv6 address.
		 *
		 * @return string The IP address, or an empty string on failure.
		 */
		public static function from_decimal( string $decimal, bool $is_ipv6 = false ): string {
			if ( $is_ipv6 ) {
				$hex = '';
				while ( bccomp( $decimal, '0' ) > 0 ) {
					$hex     = dechex( (int) bcmod( $decimal, '16' ) ) . $hex;
					$decimal = bcdiv( $decimal, '16', 0 );
				}
				$hex    = str_pad( $hex, 32, '0', STR_PAD_LEFT );
				$binary = pack( "H*", $hex );

				return inet_ntop( $binary ) ?: '';
			} else {
				return long2ip( (int) $decimal ) ?: '';
			}
		}

		/**
		 * Get the network address of a CIDR range.
		 *
		 * @param string $cidr The CIDR range.
		 *
		 * @return string The network address, or an empty string on failure.
		 */
		public static function get_network_address( string $cidr ): string {
			$parts = explode( '/', $cidr );
			if ( count( $parts ) !== 2 ) {
				return ''; // Invalid CIDR format
			}

			list( $ip, $prefix ) = $parts;
			$prefix = intval( $prefix );

			if ( self::is_valid_ipv4( $ip ) ) {
				$mask = - 1 << ( 32 - $prefix );

				return long2ip( ip2long( $ip ) & $mask ) ?: '';
			} elseif ( self::is_valid_ipv6( $ip ) ) {
				$mask    = self::create_ipv6_mask( $prefix );
				$network = inet_pton( $ip ) & $mask;

				return inet_ntop( $network ) ?: '';
			}

			return ''; // Invalid IP
		}

		/**
		 * Get the broadcast address of a CIDR range (IPv4 only).
		 *
		 * @param string $cidr The CIDR range.
		 *
		 * @return string The broadcast address, or an empty string on failure.
		 */
		public static function get_broadcast_address( string $cidr ): string {
			list( $ip, $prefix ) = explode( '/', $cidr );
			if ( self::is_valid_ipv4( $ip ) ) {
				$mask      = - 1 << ( 32 - intval( $prefix ) );
				$broadcast = ip2long( $ip ) | ~$mask;

				return long2ip( $broadcast ) ?: '';
			}

			return '';  // IPv6 doesn't use broadcast addresses
		}

		/**
		 * Calculate the number of available IP addresses in a CIDR range.
		 *
		 * @param string $cidr The CIDR range.
		 *
		 * @return int The number of available IP addresses, or 0 on failure.
		 */
		public static function get_address_count( string $cidr ): int {
			$parts = explode( '/', $cidr );
			if ( count( $parts ) !== 2 ) {
				return 0; // Invalid CIDR format
			}

			list( $ip, $prefix ) = $parts;
			$prefix = intval( $prefix );

			if ( self::is_valid_ipv4( $ip ) ) {
				return max( 0, pow( 2, 32 - $prefix ) - 2 );  // Subtract network and broadcast addresses
			} elseif ( self::is_valid_ipv6( $ip ) ) {
				if ( $prefix >= 64 ) {
					return min( PHP_INT_MAX, pow( 2, 128 - $prefix ) );
				} else {
					return PHP_INT_MAX; // For very large ranges
				}
			}

			return 0; // Invalid IP
		}

		/**
		 * Check if an IP address is a local (private) address.
		 *
		 * This method checks if the given IP address falls within the ranges reserved for private networks:
		 * - IPv4: 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, 169.254.0.0/16 (link-local)
		 * - IPv6: fc00::/7 (unique local addresses), fe80::/10 (link-local)
		 *
		 * @param string $ip The IP address to check.
		 *
		 * @return bool True if the IP is a local address, false otherwise.
		 */
		public static function is_local( string $ip ): bool {
			if ( self::is_valid_ipv4( $ip ) ) {
				// Check IPv4 private ranges
				$private_ranges = [
					[ '10.0.0.0', '10.255.255.255' ],
					[ '172.16.0.0', '172.31.255.255' ],
					[ '192.168.0.0', '192.168.255.255' ],
					[ '169.254.0.0', '169.254.255.255' ],  // Link-local
				];

				$ip_long = ip2long( $ip );
				foreach ( $private_ranges as $range ) {
					if ( $ip_long >= ip2long( $range[0] ) && $ip_long <= ip2long( $range[1] ) ) {
						return true;
					}
				}
			} elseif ( self::is_valid_ipv6( $ip ) ) {
				// Check IPv6 private ranges
				$ip_bin = inet_pton( $ip );

				// Unique local addresses (fc00::/7)
				$uladdr_prefix = inet_pton( 'fc00::' );
				$uladdr_mask   = inet_pton( 'fe00::' );

				// Link-local addresses (fe80::/10)
				$lladdr_prefix = inet_pton( 'fe80::' );
				$lladdr_mask   = inet_pton( 'ffc0::' );

				if ( ( $ip_bin & $uladdr_mask ) === $uladdr_prefix ||
				     ( $ip_bin & $lladdr_mask ) === $lladdr_prefix ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if an IP address matches any in a given list of IPs or IP ranges.
		 *
		 * @param string $ip             The IP address to check.
		 * @param array  $ip_list        List of IPs or IP ranges to check against.
		 * @param bool   $case_sensitive Whether the comparison should be case-sensitive. Default is false.
		 *
		 * @return bool Whether the IP address matches any in the list.
		 */
		public static function is_match( string $ip, array $ip_list, bool $case_sensitive = false ): bool {
			// Trim the IP address and check if it's valid
			$ip = trim( $ip );
			if ( empty( $ip ) || ! self::is_valid( $ip ) ) {
				return false;
			}

			// Convert IP to lowercase if not case-sensitive
			if ( ! $case_sensitive ) {
				$ip = strtolower( $ip );
			}

			// Loop through the IP list to check against provided IP
			foreach ( $ip_list as $list_ip ) {
				$list_ip = trim( $list_ip );
				if ( ! $case_sensitive ) {
					$list_ip = strtolower( $list_ip );
				}

				// Check if the list IP is an IP range (CIDR notation)
				if ( self::is_valid_range( $list_ip ) ) {
					if ( self::is_in_range( $ip, $list_ip ) ) {
						return true;
					}
				} else {
					// Check for an exact IP match
					if ( $list_ip === $ip ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Perform a DNS lookup for a given hostname or IP address.
		 *
		 * @param string $host The hostname or IP address to look up.
		 * @param int    $type The type of DNS record to retrieve (default is DNS_A).
		 *
		 * @return array|false An array of IP addresses or hostnames, or false on failure.
		 */
		public static function dns_lookup( string $host, int $type = DNS_A ) {
			// Trim the input
			$host = trim( $host );

			// Check if the input is empty
			if ( empty( $host ) ) {
				return false;
			}

			// Perform the DNS lookup
			$result = dns_get_record( $host, $type );

			// If no records found, try a reverse lookup if it's a valid IP
			if ( empty( $result ) && self::is_valid( $host ) ) {
				$hostname = gethostbyaddr( $host );
				if ( $hostname !== $host ) {
					return [ $hostname ];
				}

				return false;
			}

			// Extract the relevant information based on the record type
			$extracted = [];
			foreach ( $result as $record ) {
				switch ( $type ) {
					case DNS_A:
						$extracted[] = $record['ip'] ?? null;
						break;
					case DNS_AAAA:
						$extracted[] = $record['ipv6'] ?? null;
						break;
					case DNS_MX:
					case DNS_CNAME:
						$extracted[] = $record['target'] ?? null;
						break;
				}
			}

			// Remove any null values and return the result
			return array_filter( $extracted );
		}

	}
endif;