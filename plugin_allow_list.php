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

	$transient = store_allow_list_transient( "file" );

	/** Get active plugins */
    $active_plugins = get_option( 'active_plugins' );

	/** Scan active plugins against whitelist */
	foreach ($active_plugins as $plugin) {

        if ( !in_array( $plugin, $transient ) ) {
           
			deactivate_plugins( $plugin );
            do_action( restrict_plugin_activation_notice( 'not_whitelisted' ) );

		}
    }
}

// Restrict plugins based on allow list in the admin dashboard
add_action( 'admin_init', 'restrict_plugin_activation' );

/**
 * Display notice in WP admin.
 *
 * @since  1.0
 */
function restrict_plugin_activation_notice( $message_topic ) {

	error_log( $message_topic );
	
	$messages = array(
		'not_whitelisted' => 'The plugin you attempted to activate is not whitelisted and has been deactivated.',
		'list_refreshed' => 'The allow list has been refreshed.',
		'error' => 'There has been an error.'
	);

	?>
	<div class="notice error restrict-plugin_activation is-dismissible" >
		<p><?php _e( $messages[$message_topic], 'plugin_whitelist' ); ?></p>
	</div>
	<?php
}

/**
 * Test function to understand how return works
 * 
 * @param string $method
 * @return array $transient
 * @since 1.0
 * 
 */
function store_allow_list_transient( $method ) {
	
	$transient = get_transient( 'allow_listed_plugins' );

	if ( false === ( $transient ) ) {
		
		if( $method == 'url' ) {
			load_allow_list_from_url();
			error_log("Transient loaded from url. ");
		} else {
			load_allow_list_from_file();
			error_log("Transient loaded from file. ");
		}
	} else {
		error_log("Transient already stored: ");
	}
	return $transient;
}

/**
 * Load allow list from URL.
 *
 * @since  1.0
 */
function load_allow_list_from_url() {
	
	$data = loadFile( 'https://gist.githubusercontent.com/spicecadet/3a2e1156a86a686aed34d2865703eb59/raw/0ff567c6dc2561e508f9e862a5f74d9151e9d07c/plugin-whitelist.txt' );
	$allow_listed_plugins = explode( PHP_EOL, $data );
	set_transient( 'allow_listed_plugins', $allow_listed_plugins, DAY_IN_SECONDS );
}

function load_allow_list_from_file() {
	$data = file_get_contents( plugin_dir_path(__FILE__) . '/allowed_plugins.txt' );
	$allow_listed_plugins = explode( PHP_EOL, $data );
	set_transient( 'allow_listed_plugins', $allow_listed_plugins, DAY_IN_SECONDS );
}

/**
 * Use cURL to get the allow list file contents.
 *
 * @param string $url
 * @return array $data
 * @since  1.0
 */
function loadFile( $url ) {

	$ch = curl_init();
  
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_URL, $url );
  
	$data = curl_exec( $ch );
	curl_close( $ch );
  
	return $data;
}

/**
 * Enqueue scripts.
 *
 * @since  1.0
 */
function enqueue_plugin_allow_list_script() {
	wp_register_script( 'plugin_allow_list_script', plugin_dir_path(__FILE__) . '/js/wp_plugin_allow_list.js', array('jquery'), '1.0.0', true);
	wp_enqueue_script( 'plugin_allow_list_script');

	wp_localize_script( 'plugin_allow_list_script', 'ajax_object', 
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'refresh-plugin-allow-list-nonce' )
		)
	);
}

// enqueue scripts
add_action( 'admin_enqueue_scripts', 'enqueue_plugin_allow_list_script' );

/**
 * Add admin menu page.
 *
 * @since  1.0
 */
/**  */
function plugin_allow_list_menu() {
  add_menu_page( 'WP Plugin Allow List', 'WP Plugin Allow List', 'manage_options', 'wp_plugin_allow_list', 'wp_plugin_allow_list_admin_page' );
}


/**
 * Plugin allow list admin page content.
 *
 * @since  1.0
 */
function wp_plugin_allow_list_admin_page() {

	if ( !current_user_can( 'manage_options' ))  {
		wp_die( __( 'You do not have permission to access this page.' ) );
	}

 	?>
 	<div class="wrap">
 		<h2>Plugin allow list configuration</h2>
 		<button class="button button-primary" id="refresh-plugin-allow-list-button" type="submit">Refresh Plugin Allow List</button>
 	</div>
	<?php
}

// Add Plugin Allow List menu to admin dashboard
add_action( 'admin_menu', 'plugin_allow_list_menu' );

/**
 * Refresh plugin allow list by deleting the transient via admin-ajax
 *
 * @since  1.0
 */
function refresh_plugin_allow_list() {
	
	wp_verify_nonce(  'security', 'refresh-plugin-allow-list-nonce' );

	do_action( delete_transient( 'allow_listed_plugins' ) );
	do_action( load_allow_list_from_file() ); // loading the list directly from the file for now.
	$response = array( 'message' => 'Transient Deleted' );
	error_log( "Transient Refreshed" );
	
	// Send the response
	wp_send_json($response);
	
	// Exit to prevent extra output
	exit();
}

// refresh plugin allow list via admin-ajax request
add_action( 'wp_ajax_refresh_plugin_allow_list', 'refresh_plugin_allow_list' );
