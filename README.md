## Introduction
WP Plugin Allow List is a Must Use plugin for WordPress that restricts plugins to ensure that only approved plugins can be activated by content creators in WordPress. The allow list can either be hosted in the cloud for a central source of truth that will apply the same list to many instances of WordPress, or kept as a local text file. The plugin stores the allow list in a transient which expires after 24 hours. 

## Updating the allow list
If the allow list changes, the transient can be refreshed in the WP Plugin Allow List admin page which is accessible from the WordPress sidebar.

## Installation
Download or clone the repository and move the entire folder to your MU plugins directory: https://github.com/spicecadet/wp-plugin-allow-list

Copy the file wp-plugin-allow-list-loader.php to the MU directory: **/wp-content/mu-plugins/**
