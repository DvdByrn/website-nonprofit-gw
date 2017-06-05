<?php
/*
 * FooBoxShare Bootstrapper
 * Includes all the files needed for the FooBoxShare library
 *
 * Version: 1.0.0
 * Author: Brad Vincent
 * Author URI: http://fooplugins.com
 * License: GPL2
*/

//include base classes
require_once( plugin_dir_path( __FILE__ ) . '/class-fooboxshare.php' );
require_once( plugin_dir_path( __FILE__ ) . '/functions.php' );
require_once( plugin_dir_path( __FILE__ ) . '/class-fooboxshare-data.php' );

//include network base class
require_once( plugin_dir_path( __FILE__ ) . '/class-fooboxshare-network-base.php' );

//include supported network class files
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-buffer.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-delicious.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-digg.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-facebook.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-google.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-linkedin.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-pinterest.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-reddit.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-stumbleupon.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-tumblr.php' );
require_once( plugin_dir_path( __FILE__ ) . '/networks/class-twitter.php' );
