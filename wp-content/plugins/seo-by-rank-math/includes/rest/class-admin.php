<?php
/**
 * The Global functionality of the plugin.
 *
 * Defines the functionality loaded on admin.
 *
 * @since      1.0.15
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Rest;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Controller;
use RankMath\Helper;
use RankMath\Rest\Helper as RestHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = RestHelper::BASE;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/saveModule',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'save_module' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Helper', 'can_manage_options' ],
				'args'                => $this->get_save_module_args(),
			]
		);

		register_rest_route(
			$this->namespace,
			'/enableScore',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'enable_score' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Helper', 'can_manage_options' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/autoUpdate',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'auto_update' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Helper', 'can_manage_options' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/toolsAction',
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'tools_actions' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Helper', 'can_manage_options' ],
			]
		);
	}

	/**
	 * Save module state.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function save_module( WP_REST_Request $request ) {
		$module = $request->get_param( 'module' );
		$state  = $request->get_param( 'state' );

		Helper::update_modules( [ $module => $state ] );

		do_action( 'rank_math/module_changed', $module, $state );
		return true;
	}

	/**
	 * Enable SEO Score.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function enable_score( WP_REST_Request $request ) {
		$settings = wp_parse_args( rank_math()->settings->all_raw(), [
			'general' => '',
		]);

		$settings['general']['frontend_seo_score'] = 'true' === $request->get_param( 'enable' ) ? 'on' : 'off';
		Helper::update_all_settings( $settings['general'], null, null );
		return true;
	}

	/**
	 * Enable Auto update.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function auto_update( WP_REST_Request $request ) {
		$settings = wp_parse_args( rank_math()->settings->all_raw(), [
			'general' => '',
		]);

		$settings['general']['enable_auto_update'] = 'true' === $request->get_param( 'enable' ) ? 'on' : 'off';
		Helper::update_all_settings( $settings['general'], null, null );
		return true;
	}

	/**
	 * Get save module endpoint arguments.
	 *
	 * @return array
	 */
	private function get_save_module_args() {
		return [
			'module' => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Module slug', 'rank-math' ),
				'validate_callback' => [ '\\RankMath\\Rest\\Helper', 'is_param_empty' ],
			],
			'state'  => [
				'type'              => 'string',
				'required'          => true,
				'description'       => esc_html__( 'Module state either on or off', 'rank-math' ),
				'validate_callback' => [ '\\RankMath\\Rest\\Helper', 'is_param_empty' ],
			],
		];
	}

	/**
	 * Tools actions.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function tools_actions( WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );
		return apply_filters( 'rank_math/tools/' . $action, 'Something went wrong.' );
	}
}
