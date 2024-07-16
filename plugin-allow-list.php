<?php
/**
 * Plugin Name:     Plugin_allow_list
 * Description:     Simple allow list of plugins that can be activated by editors
 * Author:          Edmund Turbin
 * Author URI:      github.com/spicecadet
 * Text Domain:     plugin_allow_list
 * Domain Path:     /languages
 * Version:         1.0
 *
 * @package         Plugin_allow_list
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // disable direct access.
}
/**
 * Setup functions for the allow list
 *
 * @return array $allow_list
 * @since  1.0
 */
function plugin_allow_list_init() {

	set_allow_list_method();

	if( false === $allow_list_message_status = get_option( 'allow_list_message_status' ) ) {
		add_option( 'allow_list_message_status', 'init', '', false );		 
	}

	if( 'not_whitelisted' === $allow_list_message_status ) {
		add_action( 'admin_head', 'allow_list_admin_css' );
		plugin_allow_list_admin_notice( $allow_list_message_status );
		update_option( 'allow_list_message_status', 'init' );
	}
}

/**
 * CSS styles to hide plugin activated admin notices. 
 * 
 * Todo - Note: this could hide other notices with the #message id 
 * @since 1.0
 */

function allow_list_admin_css() {
	echo '<style id="plugin-allow-list-inline-css">#message { display: none }</style>';
}

add_action( 'admin_init', 'plugin_allow_list_init' );

// Restrict plugins based on allow list in the admin dashboard.

function set_allow_list_method() {
	// Set the default method to load the allow list from a URL
	
	if( false === $allow_list_method = get_option( 'allow_list_method' ) ) {
		add_option( 'allow_list_method', 'url', '', false );
		error_log( "Allow list method set: $allow_list_method" );
	} 
}

function get_allow_list( $allow_list_method ) {
	// Stores the allow list transient based on the allow_list_method option if the transient has expired or is unset
	if( false === $allow_list_transient = get_transient( 'allow_listed_plugins' ) ) {
		$allow_list_transient = set_allow_list_transient( $allow_list_method );
		error_log( print_r( $allow_list_transient, true ) );
	}

	return $allow_list_transient;
}

function detect_plugin_activation( $allow_list_method ) { 
	error_log( "Activated Plugin Hook Fired" );

	$active_plugins = get_option( 'active_plugins');
	$allow_list = get_allow_list( $allow_list_method );
	error_log( print_r( $active_plugins, true ) );

	/** Scan active plugins against whitelist */
	foreach ( $active_plugins as $plugin ) {
		if ( ! in_array( $plugin, $allow_list, true ) ) {
			error_log( "Plugin not on list: $plugin" );
			deactivate_plugins( $plugin );
			update_option( 'allow_list_message_status', 'not_whitelisted' );
		}
	}
}
add_action( 'activated_plugin', 'detect_plugin_activation', 'url' );

/**
 * Display admin notice in the dashboard.
 *
 * @param string $message_topic This is the type of message to be displayed.
 * @since  1.0
 */
function plugin_allow_list_admin_notice( $message_topic ) {

	?>
<div class="notice notice-info is-dismissible plugin-allow-list-admin-notice" >
		<p>
			<?php
			switch ( $message_topic ) {
				case 'not_whitelisted' === $message_topic:
					esc_html_e( 'The plugin you attempted to activate is not allowed and has been deactivated.', 'plugin_whitelist' );
					break;
				case 'list_refreshed' === $message_topic:
					esc_html_e( 'The allow list has been refreshed.', 'plugin_whitelist' );
					break;
				default:
					esc_html_e( 'There has been an error.', 'plugin_whitelist' );
					break;
			}
			?>
		</p>
	</div>
	<?php
}

/**
 * Store the allow list in a transient based on method
 *
 * @param string $method The method is used for loading the allow list - "url" or "file".
 * @since 1.0
 */
function set_allow_list_transient( $method ) {

	if ( 'url' === $method ) {
		load_allow_list_from_url();
	} else {
		load_allow_list_from_file();
	}
}

/**
 * Load the allow list from URL.
 *
 * @since 1.0
 */
function load_allow_list_from_url() {

	$data = load_file( 'https://gist.githubusercontent.com/spicecadet/3a2e1156a86a686aed34d2865703eb59/raw/f92960567ade23c077c1427ef17969b9f5e2ac60/plugin-whitelist.txt' );
	set_transient( 'allow_listed_plugins', $data, DAY_IN_SECONDS );
}
/**
 * Load the allow list from a local file.
 *
 * @since 1.0
 */
function load_allow_list_from_file() {
	$data                 = file_get_contents( plugin_dir_path( __FILE__ ) . '/allowed_plugins.txt' );
	$allow_listed_plugins = explode( PHP_EOL, $data );
	set_transient( 'allow_listed_plugins', $allow_listed_plugins, DAY_IN_SECONDS );
}

/**
 * Get the allow list file contents.
 *
 * @param string $url This is the url where the allow list file is loaded from.
 * @since  1.0
 * @return array
 */
function load_file( $url ) {
	$response = wp_remote_get( $url );
	if ( is_array( $response ) && ! is_wp_error( $response ) ) {
		$body = explode( PHP_EOL, $response['body'] ); // get the content.
	}
	return $body;
}

/**
 * Enqueue scripts.
 *
 * @since  1.0
 */
function enqueue_plugin_allow_list_script() {
	wp_register_script( 'plugin_allow_list_script', plugin_dir_path( __FILE__ ) . '/js/wp_plugin_allow_list.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'plugin_allow_list_script' );

	// Todo: change to wp_add_inline_script().
	wp_localize_script(
		'plugin_allow_list_script',
		'ajax_object',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'refresh-plugin-allow-list-nonce' ),
		)
	);
	wp_register_script( 'allow_list_method_toggle', plugin_dir_path( __FILE__ ) . '/js/wp_plugin_allow_list_method_toggle.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'allow_list_method_toggle' );

	// Todo: change to wp_add_inline_script().
	wp_localize_script(
		'allow_list_method_toggle',
		'ajax_object2',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'allow-list-method-toggle-nonce' ),
		)
	);
}

// enqueue admin scripts.
add_action( 'admin_enqueue_scripts', 'enqueue_plugin_allow_list_script' );

/**
 * Register and enqueue styles for the admin panel in the dashboard.
 */
function enqueue_plugin_allow_list_admin_styles() {
	wp_register_style( 'plugin_allow_list_wp_admin_css', plugin_dir_path( __FILE__ )  . '/css/plugin-allow-list-admin-panel.css', false, '1.0.0' );
	wp_enqueue_style( 'plugin_allow_list_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'enqueue_plugin_allow_list_admin_styles' );

/**
 * Add admin menu page.
 *
 * @since  1.0
 */
function plugin_allow_list_menu() {
	add_menu_page( 'WP Plugin Allow List', 'WP Plugin Allow List', 'manage_options', 'wp_plugin_allow_list', 'wp_plugin_allow_list_admin_page' );
}

/**
 * Plugin allow list admin page content.
 *
 * @since  1.0
 */
function wp_plugin_allow_list_admin_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html_e( 'You do not have permission to access this page.' ) );
	}

	?>
	<div class="wrap">
		<h2>Plugin Allow List Settings</h2>
	</div>
	<div class="wrap">
		<label><h3>Refresh the Plugin Allow List</h3></label>
		<p>The Plugin Allow List has an expiration of 24 hours and will be refreshed automatically. This button provides a way to manually update the Plugin Allow List if changes are made.</p>
		<p><button class="button button-primary" id="refresh-plugin-allow-list-button" type="submit">Refresh</button></p>

	</div>
	<div class="wrap">
		<label><h3>Plugin Allow List loading method</h3></label>
		<p>The Plugin Allow List can be loaded from either a remote url or a local file. The allow list is required to be text file with plugins listed on their own line. The local file can be added to the main plugin directory and named allowed_plugins.txt. An example file is included.</p>
		<p>These loading options allow for the Plugin Allow List to be either centrally managed in a single file via URL or stored locallay with your WordPress codebase. The URL option is meant to manage multiple sites with the same allow list, while the file option allows a unique list to be applied to a single site.</p>   
		<p>
			<input name="allow-list-method" type="checkbox" class="checkbox" id="allow-list-method-toggle" type="submit"/><label id="allow-list-method-toggle-label">Allow List loaded from URL</label>
		</p>
		<p id="ajax-response"></p>
	</div>
	<?php
}

// Add Plugin Allow List menu to admin dashboard.
add_action( 'admin_menu', 'plugin_allow_list_menu' );

/**
 * Refresh the allow list by deleting the transient via admin-ajax
 *
 * @since  1.0
 */
function refresh_plugin_allow_list() {

	wp_verify_nonce( 'security', 'refresh-plugin-allow-list-nonce' );

	do_action( delete_transient( 'allow_listed_plugins' ) );
	$allow_list_method = get_option( 'allow_list_method' );
	set_allow_list_transient( $allow_list_method );

	$response = array( 'message' => 'Transient Deleted' );

	// Send the response.
	wp_send_json( $response );
	
	// Exit to prevent extra output.
	exit();
}

// refresh plugin allow list via admin-ajax.
add_action( 'wp_ajax_refresh_plugin_allow_list', 'refresh_plugin_allow_list' );

/**
 * Refresh plugin allow list by deleting the transient via admin-ajax
 *
 * @since  1.0
 */
function allow_list_method_toggle() {
	
	wp_verify_nonce( 'security', 'allow-list-method-toggle-nonce' );
	$allow_list_method= $_POST['value'];
	
	// Set allow list method to file if it's not been set since this is triggered by a click to toggle method to file.
	if ( 'url' === ( $allow_list_method ) ) {
		do_action( update_option( 'allow_list_method', 'url', false ) );
		error_log( 'Allow lit method changed to url' );
	} else {
		do_action( update_option( 'allow_list_method', 'file', false ) );
		error_log( 'Allow lit method changed to file' );
	}
	
	$response = array( 'message' => 'Allow List Method Changed' );

	// Send the response.
	wp_send_json( $response );

	// Exit to prevent extra output.
	exit();
}

// Toggle the allow list method loading method.
add_action( 'wp_ajax_allow_list_method_toggle', 'allow_list_method_toggle' );
