<?php
/**
 * Plugin Name:     Plugin_allow_list
 * Description:     Simple Allow list of plugins that can be activated by editors
 * Author:          Edmund Turbin
 * Author URI:      github.com/spicecadet
 * Text Domain:     plugin_allow_list
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Plugin_allow_list
 */

add_action( 'admin_init', 'restrict_plugin_activation');

function restrict_plugin_activation() {

	/** Store allow list in a transient */
	if ( false === ( $transient = get_transient( 'allow_listed_plugins' ) ) ) {
		load_allow_list_from_url();
	}

	/** Get active plugins */
    $active_plugins = get_option( 'active_plugins' );

	/** Scan active plugins against whitelist */
	foreach ($active_plugins as $plugin) {
        if ( !in_array( $plugin, $transient ) ) {
            deactivate_plugins( $plugin );
            add_action( 'admin_notices', 'restrict_plugin_activation_notice' );
        }
    }
}

function load_allow_list_from_url() {
	/** Load allow list from URL */
	$data = loadFile( 'https://gist.githubusercontent.com/spicecadet/3a2e1156a86a686aed34d2865703eb59/raw/595584b8dec8794ade12651f5acfe7e0425797db/plugin-whitelist.txt' );
	$allow_listed_plugins = explode( PHP_EOL, $data );
	set_transient( 'allow_listed_plugins', $allow_listed_plugins, DAY_IN_SECONDS );
}

function loadFile($url) {
	$ch = curl_init();
  
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
  
	$data = curl_exec($ch);
	curl_close($ch);
  
	return $data;
}

function restrict_plugin_activation_notice() {
	?>
	<div class="notice error restrict-plugin_activation is-dismissible" >
		<p><?php _e( 'The plugin you attempted to activate is not whitelisted and has been deactivated.', 'plugin_whitelist' ); ?></p>
	</div>

	<?php
}
