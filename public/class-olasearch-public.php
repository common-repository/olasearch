<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://olasearch.com
 * @since      1.0.0
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/public
 * @author     Ola Search <hello@olasearch.com>
 */
class OlaSearch_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_shortcode( 'ola_autosuggest', [ $this, 'add_auto_suggest_shortcode' ] );
		add_shortcode( 'ola_serp', [ $this, 'add_search_results_page_shortcode' ] );

	}

	/**
	 * @param int $id post/page id
	 */
	public function post_saved( $id ) {
		$post = is_object( $id ) ? $id : get_post( $id );

		if ( $post == null ) {
			return;
		}

		if ( $post->post_status == 'publish' ) {
			error_log( "post_saved: $post->post_status $post->ID" );
			$need_to_index = in_array( $post->post_type, OlaSearch_Indexer::post_types() );
			// update ola index meta key
			OlaSearch_Indexer::update_post_meta_ola_index( [ 'post__in' => [ $id ] ], ! $need_to_index );
			if ( $need_to_index ) {
				OlaSearch_Indexer::index( [ $id ], true );
			}
		}
	}

	/**
	 * @param int $id post/page id
	 */
	public function post_trashed( $id ) {
		$post = is_object( $id ) ? $id : get_post( $id );

		if ( $post == null ) {
			return;
		}

		error_log( "post_trashed: $post->post_status $post->ID" );
		$need_to_index = in_array( $post->post_type, OlaSearch_Indexer::post_types() ) && $post->post_status == 'trash';
		// update ola index meta key
		OlaSearch_Indexer::update_post_meta_ola_index( [ 'post__in' => [ $id ] ], ! $need_to_index );

		if ( $need_to_index ) {
			// index deletion
			OlaSearch_Indexer::delete( [ $id ] );
		}
	}

	/**
	 * @param int $id post/page id
	 */
	public function post_permanently_deleted( $id ) {
		$post = is_object( $id ) ? $id : get_post( $id );

		if ( $post == null ) {
			return;
		}

		error_log( "post_permanently_deleted: $post->post_status $post->ID" );
		if ( $post->post_status == 'trash' ) { // excluding 'inherit' since it is internal
			$options       = OlaSearch_Indexer::options();
			$need_to_index = in_array( $post->post_type, OlaSearch_Indexer::post_types() );


			// add the id to permanently_deleted_pending and update options
			$options['permanently_deleted'][] = $id;
			if ( $need_to_index ) {
				$options['permanently_deleted_pending'][] = $id;
			}
			OlaSearch_Indexer::update_options( $options );

			// update ola index meta key
			OlaSearch_Indexer::update_post_meta_ola_index( [ 'post__in' => [ $id ] ], ! $need_to_index );

			if ( $need_to_index ) {
				// index deletion
				$data = OlaSearch_Indexer::delete( [ $id ] );

				// if successfully indexed then move to permanently_deleted and update options
				if ( $data['code'] === 200 ) {
					$options['permanently_deleted_pending'] = array_diff( $options['permanently_deleted_pending'], [ $id ] );
					OlaSearch_Indexer::update_options( $options );
				}
			}
		}
	}

	/**
	 * @param string $new_status
	 * @param string $old_status
	 * @param WP_Post $post
	 */
	public function post_status_changed( $new_status, $old_status, $post ) {

		if ( ! in_array( $new_status, [ 'publish', 'trash' ] ) && $old_status == 'publish' ) {
			error_log( "post_status_changed NEW: $new_status OLD: $old_status $post->ID" );
			// update ola index meta key
			OlaSearch_Indexer::update_post_meta_ola_index( [ 'post__in' => [ $post->ID ] ], ! in_array( $post->post_type, OlaSearch_Indexer::post_types() ) );

			// index deletion
			OlaSearch_Indexer::delete( [ $post->ID ] );
		}
	}

	protected function is_search_enabled() {
		$options = OlaSearch_Indexer::options();

		return isset( $options['enable_search'] ) && $options['enable_search'];
	}

	public function add_auto_suggest_shortcode() {
		$this->enqueue_scripts();
		$this->enqueue_styles();

		return '<div id="ola-autosuggest"></div>';
	}

	public function add_search_results_page_shortcode() {
		$this->enqueue_scripts();
		$this->enqueue_styles();

		return '<div id="ola-serp"></div>';
	}

	public function add_chatbot_tag() {
		$this->enqueue_scripts();
		$this->enqueue_styles();

		echo '<div id="ola-chatbot"></div>';
	}

	public function search_template_redirect() {
		if ( self::is_search_enabled() && is_search() ) {
			$page = get_page_by_title( 'Search' );
			if ( ! empty( $page ) ) {
				$args = [];
				foreach ( $_GET as $key => $value ) {
					$args[$key === 's' ? 'q' : $key] = $value;
				}
				wp_redirect( esc_url_raw( add_query_arg( $args, get_permalink( $page->ID ) ) ) );
				die;
			}
		}
	}

	public function disable_canonical_redirect() {
		if ( self::is_search_enabled() && is_page( 'search' ) ) {
			remove_filter( 'template_redirect', 'redirect_canonical' );
		}
	}

	public function ola_search_form() {
		if ( self::is_search_enabled() ) {
			return self::add_auto_suggest_shortcode();
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			esc_url_raw( 'https://cdn.olasearch.com/assets/css/olasearch.core.min.css' )
		);

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			esc_url_raw( OlaSearch_Indexer::ola_asset_host() . '/' . OlaSearch_Indexer::ola_env() . '/' . OlaSearch_Indexer::api_key() . '/olasearch.min.js' )
		);
	}

}
