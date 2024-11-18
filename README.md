## Introduction
WP Plugin Allow List is a Must Use plugin for WordPress that restricts plugins to ensure that only approved plugins can be activated by content creators in WordPress. The allow list can either be hosted in the cloud for a central source of truth that will apply the same list to many instances of WordPress, or kept as a local text file. The plugin stores the allow list in a transient which expires after 24 hours. 

## Updating the allow list
If the allow list changes, the transient can be refreshed in the WP Plugin Allow List admin page which is accessible from the WordPress sidebar.

## Installation
Download or clone the repository and move the entire folder to your MU plugins directory: https://github.com/spicecadet/wp_plugin_allow_list

Add the following line to **/wp-content/mu-plugins/loader.php**

```
require WPMU_PLUGIN_DIR . '/wp-plugin-allow-list/wp-plugin-allow-list.php';
```
