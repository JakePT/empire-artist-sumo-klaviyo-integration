<?php
/**
 * HasFilters interface definition.
 *
 * @package Klaviyo for SUMO
 */
namespace EmpireArtist\KlaviyoSumo\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface for Subscribers that register filters.
 */
interface HasFilters {
	/**
	 * Return the list of filters this class subscribes to.
	 *
	 * @return array Array indexed by the hook names, where each item is an
	 *               array containing the class method callback, priority, and
	 *               number of accepted arguments.
	 */
	public function get_filters();
}
