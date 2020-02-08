<?php
/**
 * General purpose functions.
 *
 * @package PluginName
 */
namespace PluginName;

defined( 'ABSPATH' ) || exit;

/**
 * Get URL to a plugin file.
 *
 * @return string The URL to a file in the plugin.
 */
function get_plugin_file_uri( $file = '' ) {
	return plugin_dir_url( __FILE__ ) . ltrim( $file, '/' );
}

/**
 * Get path to a plugin file.
 *
 * @return string The full path to a file in the plugin.
 */
function get_plugin_file_path( $file = '' ) {
	return plugin_dir_path( __FILE__ ) . ltrim( $file, '/' );
}