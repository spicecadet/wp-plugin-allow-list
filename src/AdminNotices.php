<?php
/**
 * Notification Class
 *
 * @package WPPluginAllowList
 */

namespace ET\WPPluginAllowList;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Notifications
 */
class AdminNotices {

	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_head', array( $this, 'allow_list_admin_css' ) );
	}

	/**
	 * CSS styles to hide plugin activated admin notices.
	 *
	 * Note: this could hide other notices with the #message id.
	 *
	 * @since 1.0
	 */
	public function allow_list_admin_css() {
		echo '<style id="plugin-allow-list-inline-css">#message { display: none }</style>';
	}

	/**
	 * Display admin notice in the dashboard.
	 *
	 * @param string $message_topic This is the type of message to be displayed.
	 * @since  1.0
	 */
	public function plugin_allow_list_admin_notice( $message_topic ) {

		?>
		<div class="notice notice-error is-dismissible plugin-allow-list-admin-notice" >
			<p>
				<?php
				switch ( $message_topic ) {
					case 'not_whitelisted' === $message_topic:
						esc_html_e( 'The plugin you attempted to activate is not allowed and has been deactivated.', 'wp-plugin-allow-list' );
						break;
					case 'list_refreshed' === $message_topic:
						esc_html_e( 'The allow list has been refreshed.', 'wp-plugin-allow-list' );
						break;
					default:
						esc_html_e( 'There has been an error.', 'wp-plugin-allow-list' );
						break;
				}
				?>
			</p>
		</div>
		<?php
	}
}
