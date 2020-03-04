<?php
/**
 * Klaviyo class definition.
 *
 * @package Klaviyo for SUMO
 */
namespace EmpireArtist\KlaviyoSumo\Clients;

defined( 'ABSPATH' ) || exit;

/**
 * Klaviyo API client.
 */
class Klaviyo {
	/**
	 * Klaviyo public API key.
	 * @var string
	 */
	protected $api_key;

	/**
	 * Klaviyo API URL.
	 * @var string
	 */
	protected $api_url = 'https://a.klaviyo.com/api/';

	/**
	 * Constructor.
	 *
	 * @param string $api_key Klaviyo public API key.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Track when someone takes an action or does something.
	 *
	 * @param string $event               Name of the event you want to track.
	 * @param array  $customer_properties Custom information about the person
	 *                                    who did this event. You must identify
	 *                                    the person by their email, using a
	 *                                    $email key, or a unique identifier,
	 *                                    using a $id.
	 * @param array  $properties          Custom information about this event.
	 * @param int    $timestamp           When this event occurred.
	 * @param bool   $once                Whether to track the first occurrance
	 *                                    of an event and ignore subsequent events.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function track( string $event, array $customer_properties = [], array $properties = [], int $timestamp = 0, bool $once = false ) {
		if ( empty( $customer_properties['$email'] ) && empty( $customer_properties['$id'] ) ) {
			return;
		}

		$data = [
			'token'               => $this->api_key,
			'event'               => $event,
			'properties'          => $properties,
			'customer_properties' => $customer_properties,
		];

		if ( $timestamp > 0 ) {
			$data['time'] = $timestamp;
		}

		$endpoint = $once ? 'track-once' : 'track';

		return $this->make_request( $endpoint, $data );
	}

	/**
	 * Track the first occurrance of an event and ignore subsequent events.
	 *
	 * @param string $event               Name of the event you want to track.
	 * @param array  $customer_properties Custom information about the person
	 *                                    who did this event. You must identify
	 *                                    the person by their email, using a
	 *                                    $email key, or a unique identifier,
	 *                                    using a $id.
	 * @param array  $properties          Custom information about this event.
	 * @param int    $timestamp           When this event occurred.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function track_once( string $event, array $customer_properties = [], array $properties = [], int $timestamp = 0 ) {
		return $this->track( $event, $customer_properties, $properties, $timestamp, true );
	}

	/**
	 * Track properties about an individual without tracking an associated event.
	 *
	 * @param array $properties Custom information about the person who did this
	 *                          event. You must identify the person by their email,
	 *                          using a $email key, or a unique identifier,
	 *                          using a $id.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function identify( array $properties ) {
		if ( empty( $properties['$email'] ) && empty( $properties['$id'] ) ) {
			return;
		}

		$data = [
			'token'      => $this->api_key,
			'properties' => $properties,
		];

		return $this->make_request( 'identify', $data );
	}

	/**
	 * Make the API request.
	 *
	 * @param string $endpoint API endpoint to use.
	 * @param array  $data     Data to send.
	 *
	 * @return bool True on success, false on failure.
	 */
	protected function make_request( string $endpoint, array $data ) {
		$data = base64_encode( json_encode( $data ) );
		$url  = add_query_arg( 'data', urlencode( $data ), $this->api_url . $endpoint );

		$request  = wp_remote_get( $url );
		$response = wp_remote_retrieve_body( $request );

		return '1' === $response;
	}
};