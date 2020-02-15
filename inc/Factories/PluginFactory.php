<?php
/**
 * PluginFactory class definition.
 *
 * @package PluginName.
 */
namespace PluginName;

defined( 'ABSPATH' ) || exit;

use PluginName\Plugin;

/**
 * Plugin class factory.
 */
class PluginFactory {
	/**
	 * Create and return a shared instance of the plugin.
	 *
	 * @return Plugin Plugin instance.
	 */
	public static function get_plugin() {
		static $plugin = null;

		if ( null === $plugin ) {
			$plugin = new Plugin();
		}

		return $plugin;
	}
}