<?php
/**
 * Plugger class definition.
 *
 * @package Klaviyo for SUMO.
 */
namespace EmpireArtist\KlaviyoSumo\Subscribers;

defined( 'ABSPATH' ) || exit;

use EmpireArtist\KlaviyoSumo\Interfaces\HasActions;
use EmpireArtist\KlaviyoSumo\Plugged\RSFunctionForMessage;
use function EmpireArtist\KlaviyoSumo\get_plugin_file_path;

/**
 * Subscribe to WordPress and WooCommerce hooks to plug pluggable plugin classes
 * and functions.
 */
class Plugger implements HasActions {
	/**
	 * @see HasActions
	 */
	public function get_actions() {
		return [
			'plugins_loaded' => [ 'plug_rs_function_for_message', 5 ],
		];
	}

	/**
	 * Plug SUMO Rewards Points' RSFunctionForMessage class.
	 *
	 * @return void
	 */
	public function plug_rs_function_for_message( $user_id ) {
		require_once get_plugin_file_path( 'plugs/RSFunctionForMessage.php' );
	}
}