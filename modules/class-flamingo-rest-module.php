<?php
/**
 * Makes Flamingo responses accessible via WordPress API
 *
 * @package modules
 */

/**
 * This class is responsible for the implementation of the WordPress REST API
 */
class Flamingo_REST_Module {

	/**
	 * The location of Flamingo
	 *
	 * @var string location of the Flamingo plugin
	 */
	private static $flamingo_plugin = 'flamingo/flamingo.php';

	/**
	 * Default constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'create_flamingo_enpoints' ) );
	}

	/**
	 * Checks if Flamingo is installed
	 */
	public function check_for_flamingo() {
		return is_plugin_active( self::$flamingo_plugin );
	}

	/**
	 * Creates the necessary endpoints for the Flamingo messages
	 */
	public function create_flamingo_enpoints() {
		// Register get messages.
		register_rest_route(
			'flamingoext/v1',
			'/messages',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_flamingo_messages' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Register get messages with filter.
		register_rest_route(
			'flamingoext/v1',
			'/messages/(?P<form>.+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_flamingo_messages_filtered' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Filters for all pages, that Flamingo made. Pagination enabled.
	 *
	 * @param WP_REST_Request $request the incoming user request.
	 * @param string          $filter if set, filter the messages to this string.
	 */
	public function get_flamingo_messages( WP_REST_Request $request, $filter = null ) {
		try {
			$params   = $request->get_query_params();
			$per_page = array_key_exists( 'per_page', $params ) ? $params['per_page'] : 0;
			$page     = array_key_exists( 'page', $params ) ? $params['page'] : 0;
			$order    = array_key_exists( 'order', $params ) ? $params['order'] : 'DESC';
			$orderby  = array_key_exists( 'orderby', $params ) ? $params['orderby'] : 'date';
			$nopaging = 0 === $per_page;

			$posts = get_posts(
				array(
					'post_type'   => 'flamingo_inbound',
					'numberposts' => $per_page,
					'nopaging'    => $nopaging,
					'order'       => $order,
					'orderby'     => $orderby,
					'page'        => $page,
				)
			);

			if ( $filter ) {
				$posts = array_filter(
					$posts,
					function( $p ) use ( $filter ) {
						return ( $p->post_title === $filter );
					},
				);
			}

			$messages = array_map( array( $this, 'convert_post' ), $posts );

			return new WP_REST_Response(
				array(
					'status'        => 200,
					'response'      => __( 'OK' ),
					'response'      => count( $messages ),
					'body_response' => $messages,
				)
			);
		} catch ( Exception $error ) {
			return new WP_Error( 500, __( 'Internal server error' ), $error->getMessage() );
		}

	}

	/**
	 * Queries the Flamingo messages with a pre-applied default filter
	 *
	 * @param WP_REST_Request $request the User Request.
	 */
	public function get_flamingo_messages_filtered( WP_REST_Request $request ) {
		return $this->get_flamingo_messages( $request, $request->get_param( 'form' ) );
	}

	/**
	 * Converts a post to a Flamingo DTO
	 *
	 * @param object $post the Flamingo post.
	 * @throws Exception If Flamingo is not installed or disabled.
	 */
	private function convert_post( $post ) {
		if ( class_exists( 'Flamingo_Inbound_Message' ) ) {
			$flamingo = new Flamingo_Inbound_Message( $post );

			$message_array = array(
				'ID'         => $flamingo->id(),
				'form_title' => $flamingo->subject,
				'remote_ip'  => $post->get_post_meta( $flamingo->id(), 'remote_ip', true ),
				'timestamp'  => $post->post_date,
				'last_edit'  => $post->post_modified,
				'name'       => $flamingo->from_name,
				'email'      => $flamingo->from_email,
				'response'   => $flamingo->fields,
			);

			return $message_array;
		} else {
			throw new Exception( 'Flamingo is not installed', 1 );
		}
	}

}
