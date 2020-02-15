<?php
/**
 * Plugin class definition.
 *
 * @package PluginNames
 */
namespace PluginName;

defined( 'ABSPATH' ) || exit;

use PluginName\Interfaces\HasActions;
use PluginName\Interfaces\HasFilters;
use PluginName\Interfaces\HasShortcodes;

/**
 * Plugin class.
 *
 * Registers subscribers' actions, filters and shortcodes to add functionality
 * to WordPress.
 */
class Plugin {
	public $subscribers;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->subscribers = [

		];
	}

	/**
	 * Register actions, filters and shortcodes for all dependencies.
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->subscribers as $subscriber ) {
			if ( $subscriber instanceof HasActions ) {
				$this->subscribe_actions( $subscriber );
			}

			if ( $subscriber instanceof HasFilters ) {
				$this->subscribe_filters( $subscriber );
			}

			if ( $subscriber instanceof HasShortcodes ) {
				$this->subscribe_shortcodes( $subscriber );
			}
		}
	}

	/**
	 * Peform actions on plugin activation.
	 *
	 * @return void
	 */
	public function activate() {

	}

	/**
	 * Peform actions on plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {

	}

	/**
	 * Register actions for dependencies.
	 *
	 * @return void
	 */
	private function subscribe_actions( HasActions $subscriber ) {
		foreach ( $subscriber->get_actions() as $action => $args ) {
			add_action( $action, [ $subscriber, $args[0] ], $args[1] ?? 10, $args[2] ?? 1 );
		}
	}

	/**
	 * Register filters for dependencies.
	 *
	 * @return void
	 */
	private function subscribe_filters( HasFilters $subscriber ) {
		foreach ( $subscriber->get_filters() as $filter => $args ) {
			add_filter( $filter, [ $subscriber, $args[0] ], $args[1] ?? 10, $args[2] ?? 1 );
		}
	}

	/**
	 * Register shortcodes for dependencies.
	 *
	 * @return void
	 */
	private function subscribe_shortcodes( HasShortcodes $subscriber ) {
		foreach ( $subscriber->get_shortcodes() as $tag => $method ) {
			add_shortcode( $tag, [ $subscriber, $method ] );
		}
	}

	/**
	 * Get subscriber instance. Can be used to remove hooks.
	 *
	 * @return object Subscriber instance.
	 */
	public function get_subscriber( $name ) {
		return $this->subscribers[$name] ?? false;
	}
}