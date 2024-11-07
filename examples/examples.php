<?php

namespace ArrayPress\Utils\Examples;

use ArrayPress\Utils\Debug\Debug;
use ArrayPress\Utils\Users\User;
use ArrayPress\Utils\Common\Anonymize;

/**
 * Register the tools page
 */
function register_field_demo_page() {
	add_management_page(
		'Field Demo',
		'Field Demo',
		'manage_options',
		'field-demo',
		__NAMESPACE__ . '\render_field_demo_page'
	);
}

add_action( 'admin_menu', __NAMESPACE__ . '\register_field_demo_page' );

/**
 * Render a demonstration page showing various Debug class methods
 */
function render_field_demo_page() {

	echo "<h2>Testing Anonymize Class Functions</h2>";

	// Test email anonymization
	$email = "john.doe@example.com";
	echo "<h3>Anonymize Email:</h3>";
	echo "Original: $email<br>";
	echo "Anonymized: " . Anonymize::email( $email ) . "<br>";

	// Test IP anonymization
	$ip = "192.168.1.1";
	echo "<h3>Anonymize IP:</h3>";
	echo "Original: $ip<br>";
	echo "Anonymized: " . Anonymize::ip( $ip ) . "<br>";

	// Test phone anonymization
	$phone = "+1-800-555-1234";
	echo "<h3>Anonymize Phone:</h3>";
	echo "Original: $phone<br>";
	echo "Anonymized: " . Anonymize::phone( $phone ) . "<br>";

	// Test name anonymization
	$name = "John Doe";
	echo "<h3>Anonymize Name:</h3>";
	echo "Original: $name<br>";
	echo "Anonymized: " . Anonymize::name( $name ) . "<br>";

	// Test credit card anonymization
	$creditCard = "4111 1111 1111 1111";
	echo "<h3>Anonymize Credit Card:</h3>";
	echo "Original: $creditCard<br>";
	echo "Anonymized: " . Anonymize::credit_card( $creditCard ) . "<br>";

	// Test address anonymization
	$address   = "123 Main St, Apartment 4B\nNew York, NY 10001\nUSA";
	$keepParts = [ "New York", "USA" ];
	echo "<h3>Anonymize Address:</h3>";
	echo "Original:<br>" . nl2br( $address ) . "<br>";
	echo "Anonymized:<br>" . nl2br( Anonymize::address( $address, $keepParts ) ) . "<br>";

	// Test date anonymization
	$date = "2024-11-06";
	echo "<h3>Anonymize Date:</h3>";
	echo "Original: $date<br>";
	echo "Anonymized: " . Anonymize::date( $date ) . "<br>";

	// Test text anonymization
	$text          = "Hello! My name is John Doe, and my email is john.doe@example.com.";
	$preserveChars = [ ' ', '.', '@', '-' ];
	echo "<h3>Anonymize Text:</h3>";
	echo "Original: $text<br>";
	echo "Anonymized: " . Anonymize::text( $text, $preserveChars ) . "<br>";

	// Test URL anonymization
	$url = "https://example.com/path/to/resource?param1=value1&param2=value2";
	echo "<h3>Anonymize URL:</h3>";
	echo "Original: $url<br>";
	echo "Anonymized: " . Anonymize::url( $url ) . "<br>";

	$zipcode = "12345";
	echo "Original: $zipcode<br>";
	echo "Anonymized: " . Anonymize::zipcode( $zipcode ) . "<br>";

	$postalCode = "AB1 2CD";
	echo "Original: $postalCode<br>";
	echo "Anonymized: " . Anonymize::zipcode( $postalCode ) . "<br>";

	// Test SSN anonymization
	$ssn = "123-45-6789";
	echo "<h3>Anonymize SSN:</h3>";
	echo "Original: $ssn<br>";
	echo "Anonymized: " . Anonymize::ssn( $ssn ) . "<br>";

	// Test Bank Account anonymization
	$account_number = "123456789012";
	echo "<h3>Anonymize Bank Account Number:</h3>";
	echo "Original: $account_number<br>";
	echo "Anonymized: " . Anonymize::bank_account( $account_number ) . "<br>";

	// Test License Plate anonymization
	$license_plate = "ABC-1234";
	echo "<h3>Anonymize License Plate:</h3>";
	echo "Original: $license_plate<br>";
	echo "Anonymized: " . Anonymize::license_plate( $license_plate ) . "<br>";

}