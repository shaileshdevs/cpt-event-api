<?php
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
								'validate_callback' => function( $param ) {
									return is_numeric( $param );
								},
								'sanitize_callback' => function( $param ) {
									return intval( $param );
								},
							),
						),
					),
				),
			);

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
								// 'validate_callback' => function( $param ) {
								// 	$format = 'dd/mm/yyyy';
								// 	$d = \DateTime::createFromFormat( $format, $param );
								// 	return $d && $d->format( $format ) === $param;
								// },
								'sanitize_callback' => function( $param ) {
									return sanitize_text_field( $param );
								},
							),
						),
					),
				),
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->route_base . '/create',
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				),
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->route_base . '/update',
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( false ),
				),
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->route_base . '/delete',
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					// 'args'                => array(
					// 	'force' => array(
					// 		'default' => false,
					// 	),
					// ),
					// 'args'                => $this->get_endpoint_args_for_item_schema( false ),
				),
			);

			// register_rest_route(
			// 	$this->namespace,
			// 	'/' . $this->route_base . '/schema',
			// 	array(
			// 		'methods'  => WP_REST_Server::READABLE,
			// 		'callback' => array( $this, 'get_public_item_schema' ),
			// 	)
			// );
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
			$args     = array(
				'post_type' => 'event',
				'post__in'  => array( $post_id ),
			);
			$posts    = get_posts( $args );

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$start_date_time = get_post_meta( $post->ID, 'start_date_time', true );
					$end_date_time   = get_post_meta( $post->ID, 'end_date_time', true );
					$terms           = get_the_terms(
						$post->ID,
						'event_category',
					);
					$categories      = array();

					foreach ( $terms as $term ) {
						$categories[] = $term->slug;
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

					foreach ( $terms as $term ) {
						$categories[] = $term->slug;
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
			$description     = empty( $request->get_param( 'description' ) ) ? '' : $request->get_param( 'description' );
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
				$postarr['description'] = $request->get_param( 'description' );
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
				$postarr['ID']        = $request->get_param( 'ID' );
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
			$post_id      = $request->get_param( 'ID' );
			// $force_delete = $request->get_param( 'force' );

			// $result = wp_delete_post( $post_id, $force_delete );
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
		 * Check if a given request has access to get items
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return bool Return whether the current user has the specified capability.
		 */
		public function get_items_permissions_check( $request ) {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Check if a given request has access to get a specific item
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return bool Return whether the current user has the specified capability.
		 */
		public function get_item_permissions_check( $request ) {
			return $this->get_items_permissions_check( $request );
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
		 * Prepare the item for create or update operation
		 *
		 * @param WP_REST_Request $request Request object
		 * @return WP_Error|object $prepared_item
		 */
		protected function prepare_item_for_database( $request ) {
			return array();
		}

		/**
		 * Prepare the item for the REST response
		 *
		 * @param mixed $item WordPress representation of the item.
		 * @param WP_REST_Request $request Request object.
		 * @return mixed
		 */
		public function prepare_item_for_response( $item, $request ) {
			return array();
		}

		/**
		 * Get the query params for collections
		 *
		 * @return array
		 */
		public function get_collection_params() {
			return array(
			'page'     => array(
				'description'       => 'Current page of the collection.',
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'description'       => 'Maximum number of items to be returned in result set.',
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
			'search'   => array(
				'description'       => 'Limit results to those matching a string.',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			);
		}
	}
}

new Event_API();
