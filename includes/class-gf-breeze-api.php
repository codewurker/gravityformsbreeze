<?php

/**
 * Breeze API library for Gravity Forms integration.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2016, Rocketgenius
 */
class GF_Breeze_API {

	/**
	 * Defines the API token needed to access Breeze.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $api_token The Breeze API token.
	 */
	protected $api_token = null;

	/**
	 * Defines the base URL path for Breeze API requests.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $api_url_base The Breeze API URL base path.
	 */
	protected $api_url_base = 'https://api.breeze.pm/';

	/**
	 * Create a new Breeze API instance.
	 *
	 * @since  1.0
	 * @access public
	 * @param  string $api_token (default: null) The Breeze API token.
	 */
	public function __construct( $api_token = null ) {

		$this->api_token = $api_token;

	}

	/**
	 * Make a Breeze API request.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param  string $path API request path.
	 * @param  array  $options (default: array()) Request options.
	 * @param  string $method (default: 'GET') Request HTTP method.
	 * @param  int    $code (default: 200) Expected HTTP response code.
	 *
	 * @return array|WP_Error
	 */
	private function make_request( $path, $options = array(), $method = 'GET', $code = 200 ) {

		// Build request url.
		$url = $this->api_url_base . $path . '.json' . ( 'GET' === $method && ! empty( $options ) ? '?' . http_build_query( $options ) : null );

		// Prepare request arguments.
		$args = array(
			'body'    => empty( $options ) ? '' : json_encode( $options ),
			'method'  => $method,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->api_token . ':' ),
				'Content-Type'  => 'application/json',
			),
		);

		// Execute request.
		$response = wp_remote_request( $url, $args );

		// If API request returns a WordPress error, return.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response['body'] = gf_breeze()->maybe_decode_json( $response['body'] );

		$response_code = wp_remote_retrieve_response_code( $response );

		// If HTTP response code does not match expected code, return WP_Error.
		if ( $response_code !== $code ) {
			return new WP_Error( $response_code, rgars( $response, 'body/error', wp_remote_retrieve_response_message( $response ) ) );
		}

		// Return response.
		return $response['body'];

	}

	/**
	 * Create a Breeze card.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array  $card (default: array()) Card object.
	 * @param  string $project_id (default: null) Project ID.
	 *
	 * @return array|WP_Error
	 */
	public function create_card( $card = array(), $project_id = null ) {

		return rgblank( $project_id ) ? array() : $this->make_request( 'projects/' . $project_id . '/cards', $card, 'POST' );

	}

	/**
	 * Get available Breeze projects.
	 *
	 * @since  1.0
	 * @access public
	 * @return array|WP_Error
	 */
	public function get_projects() {

		return $this->make_request( 'projects' );

	}

	/**
	 * Get a specific Breeze Project.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $project_id (default: null) Project ID.
	 *
	 * @return array|WP_Error
	 */
	public function get_project( $project_id = null ) {

		return rgblank( $project_id ) ? array() : $this->make_request( 'projects/' . $project_id );

	}

	/**
	 * Get available lists for a Breeze project.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $project_id (default: null) Project to request lists for.
	 *
	 * @return array|WP_Error
	 */
	public function get_project_lists( $project_id = null ) {

		return rgblank( $project_id ) ? array() : $this->make_request( 'projects/' . $project_id . '/stages' );

	}

}
