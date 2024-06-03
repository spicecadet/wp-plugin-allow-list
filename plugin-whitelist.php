<?php
/**
 * Plugin Name:     Plugin_whitelist
 * Description:     Simple Whitelist of plugins that can be activated by editors
 * Author:          Edmund Turbin
 * Author URI:      github.com/spicecadet
 * Text Domain:     plugin_whitelist
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Plugin_whitelist
 */



add_action( 'admin_init', 'restrict_plugin_activation');

function restrict_plugin_activation() {

	if ( is_plugin_active( 'plugin-directory/plugin-file.php' ) ) {
		//plugin is activated
	}

	/** Plugin Whitelist URL */
	$whitelist = 'https://gist.githubusercontent.com/spicecadet/3a2e1156a86a686aed34d2865703eb59/raw/b1223179f8387a1a0a0e49a3e47722b989c5c3a1/plugin-whitelist.txt';

	/** Store whitelist in a transient */
	if ( false === ( $transient_status = get_transient( 'whitelisted_plugins' ) ) ) {
		$file = file_get_contents( $whitelist, 'r' ); 
		$whitelisted_plugins = explode( PHP_EOL, $file );
		set_transient( 'whitelisted_plugins', $whitelisted_plugins, DAY_IN_SECONDS );
	}

	/** Get active plugins */
    $active_plugins = get_option( 'active_plugins' );
    
	/** Scan active plugins against whitelist */
	foreach ($active_plugins as $plugin) {
        if ( !in_array( $plugin, get_transient( 'whitelisted_plugins' ) ) ) {
			deactivate_plugins( $plugin );
            add_action( 'admin_notices', 'restrict_plugin_activation_notice' );
        }
    }
}

function restrict_plugin_activation_notice() {
	?>
	<div class="notice error restrict-plugin_activation is-dismissible" >
		<p><?php _e( 'The plugin you attempted to activate is not whitelisted and has been deactivated.', 'plugin_whitelist' ); ?></p>
	</div>

	<?php
}

