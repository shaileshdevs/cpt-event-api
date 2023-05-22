<?php
/**
 * The file to initialize.
 *
 * @package StoreApps
 */

namespace StoreApps;

if ( ! class_exists( 'Init' ) ) {
	/**
	 * The class to initialize.
	 */
	class Init {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->include_files();
		}

		/**
		 * Include the necessary files.
		 *
		 * @return void
		 */
		public function include_files() {
			// Register the custom post type Event.
			require_once dirname( STOREAPPS_EVENT_PLUGIN_FILE ) . '/includes/class-event-cpt-registration.php';

			// Include API files on frontend.
			if ( ! is_admin() ) {
				require_once dirname( STOREAPPS_EVENT_PLUGIN_FILE ) . '/includes/event-api/class-event-api.php';
			}
		}
	}
}
