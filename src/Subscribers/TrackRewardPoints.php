<?php
/**
 * TrackRewardPoints class definition.
 *
 * @package Klaviyo for SUMO.
 */
namespace EmpireArtist\KlaviyoSumo\Subscribers;

defined( 'ABSPATH' ) || exit;

use EmpireArtist\KlaviyoSumo\Interfaces\HasActions;
use EmpireArtist\KlaviyoSumo\Clients\Klaviyo;

/**
 * Subscribe to WordPress and WooCommerce hooks to provide reward point tracking
 * functionality.
 */
class TrackRewardPoints implements HasActions {
	/**
	 * @see HasActions
	 */
	public function get_actions() {
		return [
			'fp_reward_point_for_product_review'             => [],
			'fp_redeem_reward_points_using_rewardgateway'    => [],
			'fp_reward_point_for_buying_sumo_reward_points'  => [],
			'fp_redeem_reward_points_manually'               => [],
			'fp_redeem_reward_points_automatically'          => [],
			'rs_delete_points_for_referral_simple'           => [],
			'fp_reward_point_for_using_coupons'              => [],
			'fp_reward_point_for_using_gateways'             => [],
			'fp_reward_point_for_product_purchase'           => [],
			'fp_reward_point_for_registration'               => [],
			'fp_reward_point_for_login'                      => [],
			'fp_reward_point_for_facebook_like'              => [],
			'fp_reward_point_for_facebook_share'             => [],
			'fp_reward_point_for_tweet'                      => [],
			'fp_reward_point_for_twitter_follow'             => [],
			'fp_reward_point_for_instagram_follow'           => [],
			'fp_reward_point_for_vk_like'                    => [],
			'fp_reward_point_for_okru_share'                 => [],
			'fp_reward_point_for_using_gift_voucher'         => [],
		];
	}
}