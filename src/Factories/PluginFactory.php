<?php
/**
 * PluginFactory class definition.
 *
 * @package Klaviyo for SUMO
 */
namespace EmpireArtist\KlaviyoSumo\Factories;

defined( 'ABSPATH' ) || exit;

use EmpireArtist\KlaviyoSumo\Plugin;

/**
 * Plugin class factory.
 */
class PluginFactory {
	/**
	 * Create and return a shared instance of the plugin.
	 *
	 * @return Plugin Plugin instance.
	 */
	public static function create() {
		static $plugin = null;

		if ( null === $plugin ) {
			$plugin = new Plugin();
		}

		return $plugin;
	}
}