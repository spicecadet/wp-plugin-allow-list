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
 * Restrict activation for plugins that are not on the allow list.
 *
 * @since  1.0
 */
function restrict_plugin_activation() {

	$transient = store_allow_list_transient( 'file' );
	// error_log( 'Transient: ' . print_r( $transient, true ) );

	/** Get active plugins */
	$active_plugins = get_option( 'active_plugins' );
	// error_log( 'Active Plugins: ' . print_r( $active_plugins, true ) );

	/** Scan active plugins against whitelist */
	foreach ( $active_plugins as $plugin ) {
		// error_log( 'Plugin Loop: ' . $plugin );
		if ( ! in_array( $plugin, $transient, true ) ) {

			deactivate_plugins( $plugin );
			do_action( restrict_plugin_activation_notice( 'not_whitelisted' ) );
		}
	}
}

// Restrict plugins based on allow list in the admin dashboard.
add_action( 'admin_init', 'restrict_plugin_activation' );

/**
 * Display notice in WP admin.
 *
 * @param string $message_topic This is the type of message to be displayed.
 * @since  1.0
 */
function restrict_plugin_activation_notice( $message_topic ) {

	?>
	<div class="notice error restrict-plugin_activation is-dismissible" >
		<p>
			<?php
			switch ( $message_topic ) {
				case 'not_whitelisted' === $message_topic:
					esc_html_e( 'The plugin you attempted to activate is not whitelisted and has been deactivated.', 'plugin_whitelist' );
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
 * Test function to understand how return works
 *
 * @param string $method This is the method which is used for loading the allow list - "url" or "file".
 * @return array $transient
 * @since 1.0
 */
function store_allow_list_transient( $method ) {

	$transient = get_transient( 'allow_listed_plugins' );

	if ( false === ( $transient ) ) {

		if ( 'url' === $method ) {
			load_allow_list_from_url();
		} else {
			load_allow_list_from_file();
		}
	}
	return $transient;
}

/**
 * Load the allow list from URL.
 *
 * @since 1.0
 */
function load_allow_list_from_url() {

	$data = load_file( 'https://gist.githubusercontent.com/spicecadet/3a2e1156a86a686aed34d2865703eb59/raw/64e7a6c1ab4d75f75b8d96cd126245fabb48a0e2/plugin-whitelist.txt' );
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

	wp_localize_script(
		'plugin_allow_list_script',
		'ajax_object',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'refresh-plugin-allow-list-nonce' ),
		)
	);
}

// enqueue admin scripts.
add_action( 'admin_enqueue_scripts', 'enqueue_plugin_allow_list_script' );

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
		<h2>Plugin allow list configuration</h2>
		<p><button class="button button-primary" id="refresh-plugin-allow-list-button" type="submit">Refresh Plugin Allow List</button></p>
		<p><input type="checkbox" class="checkbox" id="load-allow-list-from-file-button" type="submit">Load Allow List From File</input></p>
	</div>
	<?php
}

// Add Plugin Allow List menu to admin dashboard.
add_action( 'admin_menu', 'plugin_allow_list_menu' );

/**
 * Refresh plugin allow list by deleting the transient via admin-ajax
 *
 * @since  1.0
 */
function refresh_plugin_allow_list() {

	wp_verify_nonce( 'security', 'refresh-plugin-allow-list-nonce' );

	do_action( delete_transient( 'allow_listed_plugins' ) );
	do_action( load_allow_list_from_url() ); // loading the list directly from the file for now.
	$response = array( 'message' => 'Transient Deleted' );

	// Send the response.
	wp_send_json( $response );

	// Exit to prevent extra output.
	exit();
}

// refresh plugin allow list via admin-ajax.
add_action( 'wp_ajax_refresh_plugin_allow_list', 'refresh_plugin_allow_list' );
