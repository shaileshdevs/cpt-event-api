<?php
/**
 * The file to handle API requests.
 *
 * @package StoreApps
 */

namespace StoreApps;

if ( ! class_exists( 'Event_API' ) ) {
	/**
	 * This class is responsible for Event API.
	 * It registers the route, checks security, performs validations, etc.
	 */
	class Event_API extends \WP_REST_Controller {
		/**
		 * The namespace.
		 *
		 * @var string
		 */
		protected $namespace = 'storeapps/v1';

		/**
		 * Route base.
		 *
		 * @var string
		 */
		protected $route_base = 'events';

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}

		/**
		 * Register the routes for the objects of the controller.
		 */
		public function register_routes() {
			// To get the Event by Event ID.
			register_rest_route(
				$this->namespace,
				'/' . $this->route_base . '/show',
				array(
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => array(
							'id' => array(
								'required'          => true,
								'validate_callback' => array( $this, 'validate_number' ),
								'sanitize_callback' => array( $this, 'sanitize_number' ),
							),
						),
					),
				),
			);

			// To get the Events by start date.
			register_rest_route(
				$this->namespace,
				'/' . $this->route_base . '/list',
				array(
					array(
						'methods'             => \WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_items_by_date' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => array(
							'start_date' => array(
								'required'          => true,
								'validate_callback' => array( $this, 'validate_date' ),
								'sanitize_callback' => array( $this, 'sanitize_string' ),
							),
						),
					),
				),
			);

			// To create Event.
			register_rest_route(
				$this->namespace,
				'/' . $this->route_base . '/create',
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array(
						'title'           => array(
							'required'          => false,
							'sanitize_callback' => array( $this, 'sanitize_string' ),
						),
						'start_date_time' => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_date_time' ),
							'sanitize_callback' => array( $this, 'sanitize_string' ),
						),
						'end_date_time'   => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_date_time' ),
							'sanitize_callback' => array( $this, 'sanitize_string' ),
						),
						'description'     => array(
							'required'          => false,
							'default'           => '',
							'sanitize_callback' => array( $this, 'sanitize_description' ),
						),
						'title'           => array(
							'category_slugs'    => false,
							'sanitize_callback' => array( $this, 'sanitize_string' ),
						),
					),
				),
			);

			// To update Event.
			register_rest_route(
				$this->namespace,
				'/' . $this->route_base . '/update',
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => array(
						'id'              => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_number' ),
							'sanitize_callback' => array( $this, 'sanitize_number' ),
						),
						'title'           => array(
							'required'          => false,
							'sanitize_callback' => array( $this, 'sanitize_string' ),
						),
						'start_date_time' => array(
							'required'          => false,
							'validate_callback' => array( $this, 'validate_date_time' ),
							'sanitize_callback' => array( $this, 'sanitize_string' ),
						),
						'end_date_time'   => array(
							'required'          => false,
							'validate_callback' => array( $this, 'validate_date_time' ),
							'sanitize_callback' => array( $this, 'sanitize_string' ),
						),
						'description'     => array(
							'required'          => false,
							'sanitize_callback' => array( $this, 'sanitize_description' ),
						),
						'title'           => array(
							'category_slugs'    => false,
							'sanitize_callback' => array( $this, 'sanitize_string' ),
						),
					),
				),
			);

			// To delete Event by Event ID.
			register_rest_route(
				$this->namespace,
				'/' . $this->route_base . '/delete',
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'validate_callback' => array( $this, 'validate_number' ),
							'sanitize_callback' => array( $this, 'sanitize_number' ),
						),
					),
				),
			);
		}

		/**
		 * Get one Event item.
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response Return WP_REST_Response on success, WP_Error otherwise.
		 */
		public function get_item( $request ) {
			$response = array();
			$post_id  = $request->get_param( 'id' );
			$post     = get_post( $post_id );

			if ( $post instanceof \WP_Post && 'event' === $post->post_type ) {
				$start_date_time = get_post_meta( $post->ID, 'start_date_time', true );
				$end_date_time   = get_post_meta( $post->ID, 'end_date_time', true );
				$terms           = get_the_terms(
					$post->ID,
					'event_category',
				);
				$categories      = array();

				if ( is_array( $terms ) ) {
					foreach ( $terms as $term ) {
						$categories[] = $term->slug;
					}
				}

				$event_posts_data = array(
					'event_id'        => $post->ID,
					'title'           => $post->post_title,
					'description'     => $post->post_content,
					'start_date_time' => $start_date_time,
					'end_date_time'   => $end_date_time,
					'category_slugs'  => implode( ', ', $categories ),
				);

				$response = array(
					'status'  => 'Success',
					'message' => 'Data Found',
					'data'    => $event_posts_data,
				);
				return new \WP_REST_Response( $response, 200 );
			} else {
				$response = array(
					'status'  => 'Success',
					'message' => 'No data found',
				);
				return new \WP_REST_Response( $response, 200 );
			}
		}

		/**
		 * Get Event items by date.
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response Return WP_REST_Response on success, WP_Error otherwise.
		 */
		public function get_items_by_date( $request ) {
			$response   = array();
			$start_date = $request->get_param( 'start_date' );
			$args       = array(
				'post_type'      => 'event',
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => 'start_date_time',
						'value'   => $start_date,
						'compare' => 'LIKE',
					),
				),
				'posts_per_page' => -1, // Retrieve all matching posts.
			);
			$posts      = get_posts( $args );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$start_date_time = get_post_meta( $post->ID, 'start_date_time', true );
					$end_date_time   = get_post_meta( $post->ID, 'end_date_time', true );
					$terms           = get_the_terms(
						$post->ID,
						'event_category',
					);
					$categories      = array();

					if ( is_array( $terms ) ) {
						foreach ( $terms as $term ) {
							$categories[] = $term->slug;
						}
					}

					$event_posts_data[] = array(
						'event_id'        => $post->ID,
						'title'           => $post->post_title,
						'description'     => $post->post_content,
						'start_date_time' => $start_date_time,
						'end_date_time'   => $end_date_time,
						'category_slugs'  => implode( ', ', $categories ),
					);
				}

				$response = array(
					'status'  => 'Success',
					'message' => 'Data Found',
					'data'    => $event_posts_data,
				);
				return new \WP_REST_Response( $response, 200 );
			} else {
				$response = array(
					'status'  => 'Success',
					'message' => 'No data found',
				);
				return new \WP_REST_Response( $response, 200 );
			}
		}

		/**
		 * Create one Event item.
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response Return WP_REST_Response on success, WP_Error otherwise.
		 */
		public function create_item( $request ) {
			$response        = array();
			$title           = $request->get_param( 'title' );
			$start_date_time = $request->get_param( 'start_date_time' );
			$end_date_time   = $request->get_param( 'end_date_time' );
			$description     = $request->get_param( 'description' );
			$category_slugs  = $request->get_param( 'category_slugs' ); // Category slugs should be comma separated list of category slugs.

			$postarr = array(
				'post_title'   => $title,
				'post_content' => $description,
				'post_status'  => 'publish',
				'post_type'    => 'event',
				'meta_input'   => array(
					'start_date_time' => $start_date_time,
					'end_date_time'   => $end_date_time,
				),
				'tax_input'    => array(
					'event_category' => $category_slugs,
				),
			);

			$post_id = wp_insert_post( $postarr, true );

			if ( is_wp_error( $post_id ) ) {
				return new \WP_Error( $post_id->get_error_code(), $post_id->get_error_message(), array( 'status' => 500 ) );
			}

			$response = array(
				'status'   => 'Success',
				'message'  => 'Event created',
				'event_id' => $post_id,
			);

			return new \WP_REST_Response( $response, 200 );
		}

		/**
		 * Update one Event item.
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response Return WP_Rest_Response on success, WP_Error otherwise.
		 */
		public function update_item( $request ) {
			$response = array();
			$postarr  = array();

			if ( $request->has_param( 'title' ) ) {
				$postarr['post_title'] = $request->get_param( 'title' );
			}

			if ( $request->has_param( 'description' ) ) {
				$postarr['post_content'] = $request->get_param( 'description' );
			}

			if ( $request->has_param( 'start_date_time' ) ) {
				$postarr['meta_input']['start_date_time'] = $request->get_param( 'start_date_time' );
			}

			if ( $request->has_param( 'end_date_time' ) ) {
				$postarr['meta_input']['end_date_time'] = $request->get_param( 'end_date_time' );
			}

			if ( $request->has_param( 'category_slugs' ) ) {
				$postarr['tax_input']['event_category'] = $request->get_param( 'category_slugs' ); // Category slugs should be comma separated list of category slugs.
			}

			if ( empty( $postarr ) ) {
				return new \WP_Error( 'too_few_arguments', __( 'You must provide atleast one data to be updated in the Event.', 'storeapps-event' ), array( 'status' => 500 ) );
			} else {
				$postarr['ID']        = $request->get_param( 'id' );
				$postarr['post_type'] = 'event';

				// Update if post id exists, create post otherwise.
				$post_id = wp_insert_post(
					$postarr,
					true
				);

				if ( is_wp_error( $post_id ) ) {
					return new \WP_Error( $post_id->get_error_code(), $post_id->get_error_message(), array( 'status' => 500 ) );
				} else {
					$response = array(
						'status'   => 'Success',
						'message'  => 'Event updated',
						'event_id' => $post_id,
					);

					return new \WP_REST_Response( $response, 200 );
				}
			}
		}

		/**
		 * Delete one item from the collection
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response Return WP_Rest_Response on success, WP_Error otherwise.
		 */
		public function delete_item( $request ) {
			$post_id = $request->get_param( 'id' );
			if ( 'event' !== get_post_type( $post_id ) ) {
				return new \WP_Error( 'invalid_event_id', __( 'The Event doesn\'t exist with this ID.', 'storeapps-event' ), array( 'status' => 500 ) );
			}

			$result = wp_delete_post( $post_id );

			if ( empty( $result ) ) {
				return new \WP_Error( 'cant_delete', __( 'There is error while deleting the event.', 'storeapps-event' ), array( 'status' => 500 ) );
			} else {
				$response = array(
					'status'   => 'Success',
					'message'  => 'Event deleted',
					'event_id' => $post_id,
				);

				return new \WP_REST_Response( $response, 200 );
			}
		}

		/**
		 * Check if a given request has access to get a specific item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return bool Return whether the current user has the specified capability.
		 */
		public function get_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Check if a given request has access to create items
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return bool Return whether the current user has the specified capability.
		 */
		public function create_item_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Check if a given request has access to update a specific item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return bool Return whether the current user has the specified capability.
		 */
		public function update_item_permissions_check( $request ) {
			return $this->create_item_permissions_check( $request );
		}

		/**
		 * Check if a given request has access to delete a specific item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return bool Return whether the current user has the specified capability.
		 */
		public function delete_item_permissions_check( $request ) {
			return $this->create_item_permissions_check( $request );
		}

		/**
		 * Validate number.
		 *
		 * @param string $param The data in API request.
		 * @return bool Return true if number is valid, false otherwise.
		 */
		public function validate_number( $param ) {
			return is_numeric( $param );
		}

		/**
		 * Validate date time.
		 * The valid format for start date time and end date time is d/m/Y H:i.
		 * Visit the site https://www.php.net/manual/en/datetime.format.php for details on the format.
		 *
		 * @param string $param The data in API request.
		 * @return bool Return true if date time is valid, false otherwise.
		 */
		public function validate_date_time( $param ) {
			$format = 'd/m/Y H:i';
			$d      = \DateTime::createFromFormat( $format, $param );
			return $d && $d->format( $format ) === $param;
		}

		/**
		 * Validate date for /storeapps/v1/events/list API request.
		 * The valid format for the date is d/m/Y.
		 * Visit the site https://www.php.net/manual/en/datetime.format.php for details on the format.
		 *
		 * @param string $param The data in API request.
		 * @return bool Return true if date time is valid, false otherwise.
		 */
		public function validate_date( $param ) {
			$format = 'd/m/Y';
			$d      = \DateTime::createFromFormat( $format, $param );
			return $d && $d->format( $format ) === $param;
		}

		/**
		 * Sanitize the data in API request.
		 *
		 * @param string $param The data in API request.
		 * @return int Return the int value after sanitization.
		 */
		public function sanitize_number( $param ) {
			return absint( $param );
		}

		/**
		 * Sanitize the data in API request.
		 *
		 * @param string $param The data in API request.
		 * @return string Return the string after sanitization.
		 */
		public function sanitize_string( $param ) {
			return sanitize_text_field( $param );
		}

		/**
		 * Sanitize the descrition in API request.
		 *
		 * @param string $param The data in API request.
		 * @return string Return the description after sanitization.
		 */
		public function sanitize_description( $param ) {
			return sanitize_textarea_field( $param );
		}
	}

	new Event_API();
}

