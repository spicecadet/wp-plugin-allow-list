<?php
/**
 * Admin Panel Class
 *
 * @package WPPluginAllowList
 */

namespace ET\WPPluginAllowList;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin_panel
 */
class AdminPanel {

	/**
	 * The method used to load the allow list - URL or file.
	 *
	 * @var string
	 */
	public $allow_list_method;

	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_plugin_allow_list_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_plugin_allow_list_scripts' ) );
		add_action( 'wp_ajax_allow_list_refresh', array( $this, 'allow_list_refresh' ) );
		add_action( 'wp_ajax_allow_list_method_toggle', array( $this, 'allow_list_method_toggle' ) );
		add_action( 'wp_ajax_allow_list_method_default', array( $this, 'allow_list_method_default' ) );
		add_action( 'admin_menu', array( $this, 'plugin_allow_list_menu' ) );
		$this->allow_list_method = get_option( 'allow_list_method' );
	}

	/**
	 * Add admin menu page.
	 *
	 * @since  1.0
	 */
	public function plugin_allow_list_menu() {
		add_menu_page(
			'WP Plugin Allow List',
			'WP Plugin Allow List',
			'manage_options',
			'wp-plugin-allow-list',
			array( $this, 'wp_plugin_allow_list_admin_page' ),
			'dashicons-text-page'
		);
	}

	/**
	 * Plugin allow list admin page content.
	 *
	 * @since  1.0
	 */
	public function wp_plugin_allow_list_admin_page() {
		?>
		<div class="wrap">
			<h2>Plugin Allow List Settings</h2>
		</div>
		<div class="wrap">
			<label><h3>Refresh the Plugin Allow List</h3></label>
			<p>The Plugin Allow List has an expiration of 24 hours and will be refreshed automatically. This button provides a way to manually update the Plugin Allow List if changes are made.</p>
			<p><button class="button button-primary" id="refresh-plugin-allow-list-button" type="submit">Refresh</button></p>
			<label id="allow-list-refresh-label"></label>

		</div>
		<div class="wrap">
			<label><h3>Plugin Allow List loading method</h3></label>
			<p>The Plugin Allow List can be loaded from either a remote url or a local file. The allow list is required to be text file with plugins listed on their own line. The local file can be added to the main plugin directory and named allowed_plugins.txt. An example file is included.</p>
			<p>These loading options allow for the Plugin Allow List to be either centrally managed in a single file via URL or stored locallay with your WordPress codebase. The URL option is meant to manage multiple sites with the same allow list, while the file option allows a unique list to be applied to a single site.</p>   
			<p>
				<input name="allow-list-method" type="checkbox" class="checkbox" id="allow-list-method-toggle" type="submit"/>
				<label id="allow-list-method-toggle-label"></label>
			</p>
			<p id="ajax-response"></p>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts for admin-ajax methods.
	 *
	 * @since  1.0
	 */
	public function enqueue_plugin_allow_list_scripts() {
		// This uses $_GET and would ideally leverage a more WordPress specific way of getting the menu slug.
		// @codingStandardsIgnoreStart
		if ( 'wp-plugin-allow-list' === isset( $_GET['page'] ) && $_GET['page'] ) {
		// @codingStandardsIgnoreEnd

			wp_register_script( 'allow_list_refresh', plugin_dir_path( __FILE__ ) . 'assets/js/wp_plugin_allow_list_refresh.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'allow_list_refresh' );

			wp_localize_script(
				'allow_list_refresh',
				'ajax_object',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'allow-list-refresh-nonce' ),
				)
			);

			wp_register_script( 'allow_list_method_toggle', plugin_dir_path( __FILE__ ) . 'assets/js/wp_plugin_allow_list_method_toggle.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'allow_list_method_toggle' );

			wp_localize_script(
				'allow_list_method_toggle',
				'ajax_object2',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'allow-list-method-toggle-nonce' ),
				)
			);

			wp_register_script( 'allow_list_method_default', plugin_dir_path( __FILE__ ) . 'assets/js/wp_plugin_allow_list_method_default.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'allow_list_method_default' );

			wp_localize_script(
				'allow_list_method_default',
				'ajax_object3',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'allow-list-method-default-nonce' ),
				)
			);
		}
	}

	/**
	 * Register and enqueue styles for the admin panel in the dashboard.
	 */
	public function enqueue_plugin_allow_list_admin_styles() {
		wp_register_style( 'plugin_allow_list_wp_admin_css', plugin_dir_path( __FILE__ ) . 'assets/css/plugin-allow-list-admin-panel.css', false, '1.0.0' );
		wp_enqueue_style( 'plugin_allow_list_wp_admin_css' );
	}


	/**
	 * Refresh the allow list by deleting the transient via admin-ajax
	 *
	 * @since  1.0
	 */
	public function allow_list_refresh() {

		wp_verify_nonce( 'security', 'allow-list-refresh-nonce' );
		do_action( delete_transient( 'allow_listed_plugins' ) );

		// Load the allow list as a transient after deleting.
		$allow_list = new WPPluginAllowList();
		$allow_list->load_allow_list();

		$response = array( 'message' => 'Allow List Transient Deleted' );

		// Send the response.
		wp_send_json( $response );

		// Exit to prevent extra output.
		exit();
	}

	/**
	 * Set the allow list loading method to the value stored in the options table
	 *
	 * @since  1.0
	 */
	public function allow_list_method_default() {

		wp_verify_nonce( 'security', 'allow-list-method-default-nonce' );

		$response = array(
			'message'                   => 'Allow list method default has been updated on the page',
			'allow_list_method_default' => $this->allow_list_method,
		);

		// Send the response.
		wp_send_json( $response );

		// Exit to prevent extra output.
		exit();
	}

	/**
	 * Toggle the allow list loading method via checkbox using admin-ajax
	 *
	 * @since  1.0
	 */
	public function allow_list_method_toggle() {

		wp_verify_nonce( 'security', 'allow-list-method-toggle-nonce' );

		// Set allow list method to file if it's not been set since this is triggered by a click to toggle method to file.
		if ( 'url' === ( $this->allow_list_method ) ) {
			do_action( update_option( 'allow_list_method', 'file', false ) );

		} else {
			do_action( update_option( 'allow_list_method', 'url', false ) );
		}

		$this->allow_list_method = get_option( 'allow_list_method' );

		$response = array(
			'message'           => 'Allow List Method Changed',
			'allow_list_method' => $this->allow_list_method,
		);

		// Send the response.
		wp_send_json( $response );

		// Exit to prevent extra output.
		exit();
	}
}
