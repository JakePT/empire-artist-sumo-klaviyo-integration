<?php
/**
 * HasShortcodes interface definition.
 *
 * @package PluginName
 */
namespace PluginName\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface for Subscribers that register shortcodes.
 */
interface HasShortcodes {
	/**
	 * Return the list of filters this class subscribes to.
	 *
	 * @return array Array indexed by the shortcode tags, where each item is an
	 *               array containing the class method callback.
	 */
	public function get_shortcodes();
}
