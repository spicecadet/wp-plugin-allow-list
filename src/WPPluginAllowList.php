<?php
/**
 * Plugin Class
 *
 * @package WPPluginAllowList
 */

namespace ET\WPPluginAllowList;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Plugin
 */
class WPPluginAllowList {

	/**
	 * The method used to load the allow list - URL or file
	 *
	 * @var string
	 */
	public $allow_list_load_method;

	/**
	 * The status that is used to determine the type of message.
	 *
	 * @var string
	 */
	public $allow_list_message_status;

	/**
	 * A transient where the allow list is stored
	 *
	 * @var array
	 */
	public $allow_list_transient;

	/**
	 * The plugin allow list which is loaded via URL or file
	 *
	 * @var string
	 */
	public $allow_list;

	/**
	 * A list of currently active plugins
	 *
	 * @var array
	 */
	public $active_plugins = array();

	/**
	 * The URL where the allow list is loaded from
	 *
	 * @var string
	 */
	public $allow_list_url;

	/**
	 * The http response from loading the list via URL
	 *
	 * @var array
	 */
	public $http_response;

	/**
	 * The data that is fetched from loading a file
	 *
	 * @var string
	 */
	public $load_file_contents;

	/**
	 * The data that is fetched from loading a url
	 *
	 * @var string
	 */
	public $load_url_contents;

	/**
	 * The class for admin notices
	 *
	 * @var class
	 */
	public $admin_notices;

	/**
	 * Setup variables
	 */
	public function __construct() {

		$this->allow_list_url = 'https://gist.githubusercontent.com/spicecadet/3a2e1156a86a686aed34d2865703eb59/raw/f92960567ade23c077c1427ef17969b9f5e2ac60/plugin-whitelist.txt';
	}

	/**
	 * Setup functions for the allow list
	 *
	 * @since  1.0
	 */
	public function init() {

		$admin_panel = new AdminPanel();
		$admin_panel->init();

		add_action( 'activated_plugin', array( $this, 'detect_plugin_activation' ) );

		$this->set_allow_list_load_method();
		$this->set_allow_list_message_status();
		$this->load_allow_list();
	}

	// Restrict plugins based on allow list in the admin dashboard.
	/**
	 * Set_allow_list_method
	 *
	 * @return void
	 */
	public function set_allow_list_load_method() {
		// Set the default method to load the allow list from a URL.

		$this->allow_list_load_method = get_option( 'allow_list_method' );
		if ( false === $this->allow_list_load_method ) {
			add_option( 'allow_list_method', 'url', '', false );
		}
	}
	/**
	 * Set the allow list message status to init if it's not been set already
	 *
	 * @return void
	 */
	private function set_allow_list_message_status() {
		$this->allow_list_message_status = get_option( 'allow_list_message_status' );
		if ( false === $this->allow_list_message_status ) {

			add_option( 'allow_list_message_status', 'init', '', false );
		}

		// Check to see if the message status is 'not_whitelisted'.
		if ( 'not_whitelisted' === $this->allow_list_message_status ) {
			$admin_notices = new AdminNotices();
			$admin_notices->init();
			$admin_notices->plugin_allow_list_admin_notice( $this->allow_list_message_status );

			update_option( 'allow_list_message_status', 'init' );
		}
	}

	/**
	 * Load the allow list into a transient based on the allow list load method.
	 *
	 * @return void
	 */
	public function load_allow_list() {

		if ( 'url' === $this->allow_list_load_method ) {
			// Load allow list from url.
			$this->http_response = wp_remote_get( $this->allow_list_url );
			if ( is_array( $this->http_response ) && ! is_wp_error( $this->http_response ) ) {
				$this->load_url_contents = explode( PHP_EOL, $this->http_response['body'] ); // get the content.
			}

			set_transient( 'allow_listed_plugins', $this->load_url_contents, DAY_IN_SECONDS );

		} else {
			// Load allow list from file.
			$this->load_file_contents = file_get_contents( plugin_dir_path( __DIR__ ) . 'allowed_plugins.txt' );
			$this->load_file_contents = explode( PHP_EOL, $this->load_file_contents );

			set_transient( 'allow_listed_plugins', $this->load_file_contents, DAY_IN_SECONDS );
		}
	}

	/**
	 * Compare the plugin being activated to the allow list and restrict activation if it is not found.
	 *
	 * @return void
	 */
	public function detect_plugin_activation() {

		$this->active_plugins       = get_option( 'active_plugins' );
		$this->allow_list_transient = get_transient( 'allow_listed_plugins' );

		// Compare active plugins against whitelist.
		foreach ( $this->active_plugins as $plugin ) {
			if ( ! in_array( $plugin, $this->allow_list_transient, true ) ) {
				deactivate_plugins( $plugin );
				update_option( 'allow_list_message_status', 'not_whitelisted' );
			}
		}
	}
}
