<?php
/**
 * Plugin Name:     WP Plugin Allow List
 * Description:     Simple allow list of plugins that can be activated by editors
 * Author:          Edmund Turbin
 * Author URI:      github.com/spicecadet
 * Text Domain:     wp-plugin-allow-list
 * Domain Path:     /languages
 * Version:         1.0
 *
 * @package         WPPluginAllowList
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use ET\WPPluginAllowList\WPPluginAllowList;

require __DIR__ . '/vendor/autoload.php';

$wp_plugin_allow_list = new WPPluginAllowList();
$wp_plugin_allow_list->init();
