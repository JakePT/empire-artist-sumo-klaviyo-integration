<?php
/**
 * TrackRewardPoints class definition.
 *
 * @package Klaviyo for SUMO.
 */
namespace EmpireArtist\KlaviyoSumo\Subscribers;

defined( 'ABSPATH' ) || exit;

use RS_Points_Data;
use EmpireArtist\KlaviyoSumo\Interfaces\HasActions;
use EmpireArtist\KlaviyoSumo\Clients\Klaviyo;

/**
 * Subscribe to WordPress and WooCommerce hooks to provide reward point tracking
 * functionality.
 */
class TrackRewardPoints implements HasActions {
	/**
	 * Klaviyo API Client.
	 * @var Klaviyo
	 */
	protected $klaviyo;

	/**
	 * Constructor.
	 *
	 * @param Klaviyo $klaviyo Klaviyo API client instance.
	 *
	 * @return void
	 */
	function __construct( Klaviyo $klaviyo ) {
		$this->klaviyo = $klaviyo;
	}

	/**
	 * @see HasActions
	 */
	public function get_actions() {
		// return [
		// 	'fp_reward_point_for_product_review'             => [],
		// 	'fp_redeem_reward_points_using_rewardgateway'    => [],
		// 	'fp_reward_point_for_buying_sumo_reward_points'  => [],
		// 	'fp_redeem_reward_points_manually'               => [],
		// 	'fp_redeem_reward_points_automatically'          => [],
		// 	'rs_delete_points_for_referral_simple'           => [],
		// 	'fp_reward_point_for_using_coupons'              => [],
		// 	'fp_reward_point_for_using_gateways'             => [],
		// 	'fp_reward_point_for_product_purchase'           => [],
		// 	'fp_reward_point_for_registration'               => [],
		// 	'fp_reward_point_for_login'                      => [],
		// 	'fp_reward_point_for_facebook_like'              => [],
		// 	'fp_reward_point_for_facebook_share'             => [],
		// 	'fp_reward_point_for_tweet'                      => [],
		// 	'fp_reward_point_for_twitter_follow'             => [],
		// 	'fp_reward_point_for_instagram_follow'           => [],
		// 	'fp_reward_point_for_vk_like'                    => [],
		// 	'fp_reward_point_for_okru_share'                 => [],
		// 	'fp_reward_point_for_using_gift_voucher'         => [],
		// ];
	}

	/**
	 * Track the points balances for a user.
	 *
	 * @param string $event   The reason the points balance is being updated.
	 * @param int    $user_id The user who's points balance to track.
	 *
	 * @return void
	 */
	protected function track_points( $event_name, $user_id = 0 ) {
		/**
		 * Get the user object for the given user, or the current user if no
		 * ID is provided.
		 */
		$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();

		/**
		 * If the user does not exist, or does not have an email, we cannot track
		 * anything for them.
		 */
		if ( ! $user || ! $user->user_email ) {
			return;
		}

		/**
		 * Get points data.
		 */
		$points_data = new RS_Points_Data( $user_id );

		/**
		 * Send tracking request.
		 */
		$this->klaviyo->track(
			$event_name,
			[
				'$email'          => $user->user_email,
				'Points Balance'  => $points_data->total_available_points(),
				'Points Earned'   => $points_data->total_earned_points(),
				'Points Redeemed' => $points_data->total_redeemed_points(),
				'Points Expired'  => $points_data->total_expired_points(),
			],
			[
				'New Points Balance'  => $points_data->total_available_points(),
				'New Points Earned'   => $points_data->total_earned_points(),
				'New Points Redeemed' => $points_data->total_redeemed_points(),
				'New Points Expired'  => $points_data->total_expired_points(),
			]
		);
	}
}