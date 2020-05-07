<?php
/**
 * Plugin class definition.
 *
 * @package Klaviyo for SUMO
 */
namespace EmpireArtist\KlaviyoSumo;

defined( 'ABSPATH' ) || exit;

use EmpireArtist\KlaviyoSumo\Clients\Klaviyo;
use EmpireArtist\KlaviyoSumo\Interfaces\HasActions;
use EmpireArtist\KlaviyoSumo\Interfaces\HasFilters;
use EmpireArtist\KlaviyoSumo\Subscribers\Plugger;
use EmpireArtist\KlaviyoSumo\Subscribers\QRReferralLink;
use EmpireArtist\KlaviyoSumo\Subscribers\TrackRewardPoints;
use EmpireArtist\KlaviyoSumo\Subscribers\TrackMemberships;

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
		$settings = (array) get_option( 'klaviyo_settings' );
		$klaviyo  = new Klaviyo( $settings['public_api_key'] ?? '' );

		$this->subscribers = [
			'plugger'             => new Plugger(),
			'track_memberships'   => new TrackMemberships( $klaviyo ),
			'track_reward_points' => new TrackRewardPoints( $klaviyo ),
			'qr_referral_link'    => new QRReferralLink(),
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
			add_action(
				$action,
				[
					$subscriber,
					$args[0]
				],
				isset( $args[1] ) ? $args[1] : 10,
				isset( $args[2] ) ? $args[2] : 1
			);
		}
	}

	/**
	 * Register filters for dependencies.
	 *
	 * @return void
	 */
	private function subscribe_filters( HasFilters $subscriber ) {
		foreach ( $subscriber->get_filters() as $filter => $args ) {
			add_filter(
				$filter,
				[
					$subscriber,
					$args[0]
				],
				isset( $args[1] ) ? $args[1] : 10,
				isset( $args[2] ) ? $args[2] : 1
			);
		}
	}

	/**
	 * Get subscriber instance. Can be used to remove hooks.
	 *
	 * @return object Subscriber instance.
	 */
	public function get_subscriber( $name ) {
		return isset( $this->subscribers[$name] ) ? $this->subscribers[$name] : false;
	}
}