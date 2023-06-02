<?php
/**
 * The file to register Event custom post type and taxonomy Category.
 *
 * @package StoreApps
 */

namespace StoreApps;

if ( ! class_exists( 'Event_CPT_Registration' ) ) {
	/**
	 * The class to register custom post type Event.
	 */
	class Event_CPT_Registration {
		/**
		 * Instance of the class.
		 *
		 * @var Event_CPT_Registration
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register_event_cpt' ) );
		}

		/**
		 * Return the single instance of the class.
		 *
		 * @return Event_CPT_Registration Return the singleton instance of the class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Register the custom post type Event.
		 *
		 * @return void
		 */
		public function register_event_cpt() {
			$labels = array(
				'name'               => _x( 'Events', 'Post type general name', 'storeapps-event' ),
				'singular_name'      => _x( 'Event', 'Post type singular name', 'storeapps-event' ),
				'menu_name'          => _x( 'Events', 'Admin Menu text', 'storeapps-event' ),
				'name_admin_bar'     => _x( 'Event', 'Add New on Toolbar', 'storeapps-event' ),
				'add_new'            => __( 'Add New', 'storeapps-event' ),
				'add_new_item'       => __( 'Add New Event', 'storeapps-event' ),
				'new_item'           => __( 'New Event', 'storeapps-event' ),
				'edit_item'          => __( 'Edit Event', 'storeapps-event' ),
				'view_item'          => __( 'View Event', 'storeapps-event' ),
				'all_items'          => __( 'All Events', 'storeapps-event' ),
				'search_items'       => __( 'Search Events', 'storeapps-event' ),
				'parent_item_colon'  => __( 'Parent Events:', 'storeapps-event' ),
				'not_found'          => __( 'No events found.', 'storeapps-event' ),
				'not_found_in_trash' => __( 'No events found in Trash.', 'storeapps-event' ),
				'archives'           => _x( 'Event archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'storeapps-event' ),
			);

			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'event' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'show_in_rest'       => true,
				'supports'           => array( 'title', 'editor', 'revisions', 'author' ),
				'taxonomies'         => array( 'event_category' ),
			);

			register_post_type( 'event', $args );

			$this->register_event_category_taxonomy();
		}

		/**
		 * Register the Category Taxonomy.
		 *
		 * @return void
		 */
		public function register_event_category_taxonomy() {
			$labels = array(
				'name'                       => _x( 'Categories', 'taxonomy general name', 'storeapps-event' ),
				'singular_name'              => _x( 'Category', 'taxonomy singular name', 'storeapps-event' ),
				'search_items'               => __( 'Search Categories', 'storeapps-event' ),
				'popular_items'              => __( 'Popular Categories', 'storeapps-event' ),
				'all_items'                  => __( 'All Categories', 'storeapps-event' ),
				'parent_item'                => null,
				'parent_item_colon'          => null,
				'edit_item'                  => __( 'Edit Category', 'storeapps-event' ),
				'update_item'                => __( 'Update Category', 'storeapps-event' ),
				'add_new_item'               => __( 'Add New Category', 'storeapps-event' ),
				'new_item_name'              => __( 'New Category Name', 'storeapps-event' ),
				'separate_items_with_commas' => __( 'Separate categories with commas', 'storeapps-event' ),
				'add_or_remove_items'        => __( 'Add or remove categories', 'storeapps-event' ),
				'choose_from_most_used'      => __( 'Choose from the most used categories', 'storeapps-event' ),
				'not_found'                  => __( 'No category found.', 'storeapps-event' ),
				'menu_name'                  => __( 'Categories', 'storeapps-event' ),
			);

			$args = array(
				'hierarchical'          => false,
				'labels'                => $labels,
				'show_in_rest'          => true,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
			);

			register_taxonomy(
				'event_category',
				'event',
				$args
			);
		}
	}

	Event_CPT_Registration::get_instance();
}
