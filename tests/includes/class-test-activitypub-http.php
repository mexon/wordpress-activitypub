<?php
/**
 * Test file for Activitypub HTTP Class
 *
 * @package Activitypub
 */

namespace Activitypub\Tests;

use Activitypub\Http;

/**
 * Test class for ActivityPub HTTP Class
 *
 * @coversDefaultClass \Activitypub\Http
 */
class Test_Activitypub_Http extends \WP_UnitTestCase {

	/**
	 * Response code is 404 -> is_tombstone returns true
	 *
	 * @covers ::is_tombstone
	 */
	public function test_is_tombstone_404() {
		$fake_request = function () {
			return array(
				'response' => array( 'code' => 404 ),
			);
		};
		add_filter( 'pre_http_request', $fake_request, 10, 3 );
		$result = Http::is_tombstone( 'https://fake.test/object/123' );
		$this->assertEquals( true, $result );
		remove_filter( 'pre_http_request', $fake_request, 10 );
	}

	/**
	 * Response code is 410 -> is_tombstone returns true
	 *
	 * @covers ::is_tombstone
	 */
	public function test_is_tombstone_410() {
		$fake_request = function () {
			return array(
				'response' => array( 'code' => 410 ),
			);
		};
		add_filter( 'pre_http_request', $fake_request, 10, 3 );
		$result = Http::is_tombstone( 'https://fake.test/object/123' );
		$this->assertEquals( true, $result );
		remove_filter( 'pre_http_request', $fake_request, 10 );
	}

	/**
	 * Response code is 200, body is empty -> is_tombstone returns false
	 *
	 * @covers ::is_tombstone
	 */
	public function test_is_tombstone_empty_response() {
		$fake_request = function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => '',
			);
		};
		add_filter( 'pre_http_request', $fake_request, 10, 3 );
		$result = Http::is_tombstone( 'https://fake.test/object/123' );
		$this->assertEquals( false, $result );
		remove_filter( 'pre_http_request', $fake_request, 10 );
	}

	/**
	 * Response code is 200, body has no type -> is_tombstone returns false
	 *
	 * @covers ::is_tombstone
	 */
	public function test_is_tombstone_no_type() {
		$fake_request = function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => '{}',
			);
		};
		add_filter( 'pre_http_request', $fake_request, 10, 3 );
		$result = Http::is_tombstone( 'https://fake.test/object/123' );
		$this->assertEquals( false, $result );
		remove_filter( 'pre_http_request', $fake_request, 10 );
	}

	/**
	 * Response code is 200, type is not tombstone -> is_tombstone returns false
	 *
	 * @covers ::is_tombstone
	 */
	public function test_is_tombstone_type_not_tombstone() {
		$fake_request = function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => '{"type": "Note"}',
			);
		};
		add_filter( 'pre_http_request', $fake_request, 10, 3 );
		$result = Http::is_tombstone( 'https://fake.test/object/123' );
		$this->assertEquals( false, $result );
		remove_filter( 'pre_http_request', $fake_request, 10 );
	}

	/**
	 * Response code is 200, type is tombstone -> is_tombstone returns true
	 *
	 * @covers ::is_tombstone
	 */
	public function test_is_tombstone_type_tombstone() {
		$fake_request = function () {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => '{"type": "Tombstone"}',
			);
		};
		add_filter( 'pre_http_request', $fake_request, 10, 3 );
		$result = Http::is_tombstone( 'https://fake.test/object/123' );
		$this->assertEquals( true, $result );
		remove_filter( 'pre_http_request', $fake_request, 10 );
	}
}
