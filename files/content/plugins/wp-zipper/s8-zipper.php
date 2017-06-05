<?php
/*
Plugin Name: Zipper
Plugin URI:
Description: A backup utility for WordPress.
Author: Sideways8
Version: 0.0.1
Author URI:
*/

// Define plugin constants.
define( 'S8_ZIPPER_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'S8_ZIPPER_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

// Load the plugin bootstrapper file.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/code/Bootstrapper.php';

// Initialize plugin.
new S8\Zipper\Bootstrapper();

$archive = new \S8\Zipper\Archivers\ArchiverCleanup();
$archive->perform_cleanup();