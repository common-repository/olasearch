<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://olasearch.com
 * @since      1.0.0
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/admin
 * @author     Ola Search <hello@olasearch.com>
 *
 * @property-read array options lazy loaded admin options
 */
class OlaSearch_Admin {

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
	 * @var OlaSearch_Admin
	 */
	private static $instance;

	/**
	 * Constants
	 */
	const OLA_HOST = 'https://admin.olasearch.com';
	const OLA_HOST_INTERNAL = 'https://admin-staging.olasearch.com';
	const OLA_HOST_LOCAL = 'http://localhost:3333';

	const OLA_ASSET_HOST = 'https://cdn.olasearch.com';
	const OLA_ASSET_HOST_INTERNAL = 'https://s3-ap-southeast-1.amazonaws.com/configs.internal.olasearch.com';
	const OLA_ASSET_HOST_LOCAL = 'https://s3-ap-southeast-1.amazonaws.com/configs.local.olasearch.com';

	const OLA_SERVER = 'production';
	const OLA_SERVER_INTERNAL = 'internal';
	const OLA_SERVER_LOCAL = 'local';

	const OLA_ENVIRONMENT = 'production';

	const API_KEY_LABEL = 'API Key';
	const SETTINGS_TAB = 'olasearch';
	const DEFAULT_FIELD_TYPE = 't';
	const DEFAULT_TAXONOMY_TYPE = 'taxo';
	const TAXONOMY_ENTITY_TYPE = 'taxo_entity';
	const SEARCH_PAGE_TITLE = 'Search';

	/**
	 * @var array admin menu sections
	 */
	protected static $sections = [
		self::SETTINGS_TAB => 'Settings',
		'post_types'       => 'Post Types',
		'taxonomies'       => 'Taxonomies',
		'post_fields'      => 'Post Fields',
	];

	/**
	 * @var array admin setting default values
	 */
	public static $section_defaults = [
		'post_types'                  => [ 'post', 'tribe_events', 'tribe_organizer', 'tribe_venue', ],
		'meta_fields'                 => [
			'tribe_events'    => [
				'tribe_events_allday'           => [ 'checked' => 1, 'type' => 's' ],
				'tribe_events_cost'             => [ 'checked' => 1, 'type' => 'd' ],
				'tribe_events_costmax'          => [ 'checked' => 1, 'type' => 'd' ],
				'tribe_events_costmin'          => [ 'checked' => 1, 'type' => 'd' ],
				'tribe_events_currencyposition' => [ 'checked' => 1, 'type' => 's' ],
				'tribe_events_currencysymbol'   => [ 'checked' => 1, 'type' => 's' ],
				'tribe_events_duration'         => [ 'checked' => 1, 'type' => 's' ],
				'tribe_events_enddate'          => [ 'checked' => 1, 'type' => 'tdt' ],
				'tribe_events_enddateutc'       => [ 'checked' => 1, 'type' => 'tdt' ],
				'tribe_events_hidefromupcoming' => [ 'checked' => 1, 'type' => 'b' ],
				'tribe_events_origin'           => [ 'checked' => 1, 'type' => 's' ],
				'tribe_events_phone'            => [ 'checked' => 1, 'type' => 's' ],
				'tribe_events_showmap'          => [ 'checked' => 1, 'type' => 'b' ],
				'tribe_events_showmaplink'      => [ 'checked' => 1, 'type' => 'b' ],
				'tribe_events_startdate'        => [ 'checked' => 1, 'type' => 'tdt' ],
				'tribe_events_startdateutc'     => [ 'checked' => 1, 'type' => 'tdt' ],
				'tribe_events_timezone'         => [ 'checked' => 1, 'type' => 's' ],
				'tribe_events_timezoneabbr'     => [ 'checked' => 1, 'type' => 's' ],
				'tribe_events_url'              => [ 'checked' => 1, 'type' => 's' ],
				'tribe_events_cat'              => [ 'checked' => 1, 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'tribe_events_cat' ],
				'tribe_events_organizer'        => [ 'checked' => 1, 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'tribe_organizer' ],
				'tribe_events_venue'            => [ 'checked' => 1, 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'tribe_venue' ],
			],
			'tribe_organizer' => [
				'tribe_organizer_email'   => [ 'checked' => 1, 'type' => 's' ],
				'tribe_organizer_phone'   => [ 'checked' => 1, 'type' => 's' ],
				'tribe_organizer_website' => [ 'checked' => 1, 'type' => 's' ],
			],
			'tribe_venue'     => [
				'tribe_venue_address'       => [ 'checked' => 1, 'type' => 's' ],
				'tribe_venue_city'          => [ 'checked' => 1, 'type' => 's' ],
				'tribe_venue_country'       => [ 'checked' => 1, 'type' => 's' ],
				'tribe_venue_phone'         => [ 'checked' => 1, 'type' => 's' ],
				'tribe_venue_province'      => [ 'checked' => 1, 'type' => 's' ],
				'tribe_venue_showmap'       => [ 'checked' => 1, 'type' => 'b' ],
				'tribe_venue_showmaplink'   => [ 'checked' => 1, 'type' => 'b' ],
				'tribe_venue_state'         => [ 'checked' => 1, 'type' => 's' ],
				'tribe_venue_stateprovince' => [ 'checked' => 1, 'type' => 's' ],
				'tribe_venue_url'           => [ 'checked' => 1, 'type' => 's' ],
				'tribe_venue_zip'           => [ 'checked' => 1, 'type' => 's' ],
			],
		],
		'taxonomies'                  => [
			'category'         => [ 'type' => 'taxo' ],
			'post_tag'         => [ 'type' => 'taxo' ],
			'user'             => [ 'type' => 'entity' ],
			'post_format'      => [ 'type' => 'taxo' ],
			'tribe_events_cat' => [ 'type' => 'taxo' ],
			'tribe_organizer'  => [ 'type' => 'entity' ],
			'tribe_venue'      => [ 'type' => 'entity' ],
		],
		'post_fields'                 => [
			'post_url'           => [ 'checked' => 1, 'type' => 's' ],
			'post_thumbnail_url' => [ 'checked' => 1, 'type' => 's' ],
			'blog_id'            => [ 'checked' => 1, 'type' => 'i' ],
			'post_title'         => [ 'checked' => 1, 'type' => 't' ],
			'post_type'          => [ 'checked' => 1, 'type' => 's' ],
			'post_excerpt'       => [ 'checked' => 1, 'type' => 't' ],
			'post_content'       => [ 'checked' => 1, 'type' => 't' ],
			'post_status'        => [ 'checked' => 1, 'type' => 's' ],
			'post_date'          => [ 'checked' => 1, 'type' => 'tdt' ],
			'post_date_gmt'      => [ 'checked' => 1, 'type' => 'tdt' ],
			'post_modified'      => [ 'checked' => 1, 'type' => 'tdt' ],
			'post_modified_gmt'  => [ 'checked' => 1, 'type' => 'tdt' ],
			'post_category'      => [ 'checked' => 1, 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'category' ],
			'post_tag'           => [ 'checked' => 1, 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'post_tag' ],
			'post_author'        => [ 'checked' => 1, 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'user' ],
			'post_format'        => [ 'checked' => 1, 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'post_format' ],
		],
		'permanently_deleted'         => [],
		'permanently_deleted_pending' => [],
		'last_synced'                 => null,
	];

	/**
	 * @var array built-in field labels
	 */
	public static $field_labels = [
		'post_url'           => 'URL',
		'post_thumbnail_url' => 'Thumbnail URL',
		'blog_id'            => 'Blog ID',
		'post_title'         => 'Title',
		'post_type'          => 'Type',
		'post_excerpt'       => 'Excerpt',
		'post_content'       => 'Content',
		'post_status'        => 'Status',
		'post_author'        => 'Author',
		'post_date'          => 'Published Date',
		'post_date_gmt'      => 'Published Date (GMT)',
		'post_modified'      => 'Modified Date',
		'post_modified_gmt'  => 'Modified Date (GMT)',
		'post_category'      => 'Categories',
		'post_tag'           => 'Tags',
		'post_format'        => 'Format',
	];

	/**
	 * @var array valid field types
	 */
	public static $field_types = [
		't'                        => 'Text',
		'ts'                       => 'Text (multiple)',
		'b'                        => 'Boolean',
		'bs'                       => 'Boolean (multiple)',
		'tdt'                      => 'Date',
		'd'                        => 'Double',
		'ds'                       => 'Double (multiple)',
		'f'                        => 'Float',
		'fs'                       => 'Float (multiple)',
		'i'                        => 'Integer',
		'is'                       => 'Integer (multiple)',
		'p'                        => 'Location',
		'l'                        => 'Long',
		'ls'                       => 'Long (multiple)',
		's'                        => 'String',
		'ss'                       => 'String (multiple)',
		//'c'   => 'Currency' //It is supported by only one search engine
		self::TAXONOMY_ENTITY_TYPE => 'Taxonomy / Entity',
	];

	/**
	 * @var array valid taxonomy field types
	 */
	public static $taxonomy_field_types = [
		'taxo'        => 'Taxonomy',
		'entity'      => 'Entity (General)',
		'entity_name' => 'Entity (People)',
	];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param    string $plugin_name The name of this plugin.
	 * @param    string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		static::$instance = $this;

	}

	public function ajax_do_index() {
		$re_index   = isset( $_POST['re_index'] );
		$initial    = isset( $_POST['initial'] );
		$last_batch = isset( $_POST['last_batch'] );
		wp_send_json_success( OlaSearch_Indexer::index( null, $last_batch, $initial, $re_index ) );
	}

	public function ajax_do_wipe() {
		wp_send_json_success( OlaSearch_Indexer::wipe() );
	}

	public function __get( $name ) {
		switch ( $name ) {
			case 'options':
				return OlaSearch_Indexer::options();
		}

		return null;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */

	public function add_plugin_admin_menu() {
		add_menu_page(
			'Ola Search', // page_title
			'Ola Search', // menu_title
			'manage_options', // capability
			$this->plugin_name, // menu_slug
			[ $this, 'display_plugin_setup_page' ], // function
			WP_CONTENT_URL . '/plugins/olasearch/admin/img/logo.png', // icon_url
			100 // position
		);

		foreach ( static::$sections as $tab => $name ) {
			add_submenu_page(
				$this->plugin_name,
				$name,
				$name,
				'manage_options',
				$tab,
				[ $this, 'display_plugin_setup_page' ] // function
			);
		}
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */

	public function add_action_links( $links ) {
		$settings_link = [
			'<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __( 'Settings',
				$this->plugin_name ) . '</a>',
		];

		return array_merge( $settings_link, $links );

	}

	/**
	 * Add validation for options page.
	 *
	 * @since    1.0.0
	 */

	public function options_update() {
		register_setting( $this->plugin_name, $this->plugin_name, [ $this, 'validate' ] );
	}

	public function validate( $input ) {
		// All checkboxes inputs
		$valid = [];

		$valid['batch'] = isset( $input['batch'] ) && is_numeric( $input['batch'] )
			? (int) $input['batch']
			: 0;

		$valid['post_fields'] = $input['post_fields'];
		$valid['meta_fields'] = $input['meta_fields'];
		$valid['post_types']  = $input['post_types'];
		$valid['taxonomies']  = $input['taxonomies'];

		$valid['permanently_deleted']         = $input['permanently_deleted'];
		$valid['permanently_deleted_pending'] = $input['permanently_deleted_pending'];
		$valid['last_synced']                 = $input['last_synced'];
		$valid['enable_search']               = boolval( $input['enable_search'] );

		if ( ! empty( $input['api_key'] ) ) {
			$valid['api_key'] = sanitize_text_field( $input['api_key'] );
		}

		if ( ! empty( $input['api_server'] ) ) {
			$valid['api_server'] = sanitize_text_field( $input['api_server'] );
		}

		if ( empty( $valid['api_key'] ) && ( empty( $_GET['page'] || $_GET['page'] == self::SETTINGS_TAB ) ) ) {
			$error = 'Please provide a valid ' . self::API_KEY_LABEL;
			add_settings_error( $this->plugin_name, $this->plugin_name, $error, 'error' );
			static::notice( $error, 'error' );
		}

		return $valid;
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */

	public function display_plugin_setup_page() {
		include_once OLA_SEARCH_PLUGIN_DIRECTORY . 'admin/partials/olasearch-admin-display.php';
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/olasearch-admin.css', [],
			$this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/olasearch-admin.js', [ 'jquery' ],
			$this->version, false );
	}

	/**
	 * Find current section.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return   string
	 */
	private function section() {
		static $section;
		if ( is_null( $section ) ) {
			if ( isset( $_GET['page'] ) && isset( static::$sections[$_GET['page']] ) ) {
				$section = $_GET['page'];
			} else {
				$section = self::SETTINGS_TAB;
			}
		}

		return $section;
	}

	/**
	 * Returns any taxonomies registered for the provided post types.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @return   string[] fields
	 **/
	private function taxonomies() {
		global $wp_taxonomies;
		$types = $this->options['post_types'];

		// user taxonomy is manually added
		$taxonomy_groups = [ 'user' => [ 'group' => 'user', 'label' => 'Users' ] ];

		// include only if events calendar is active
		if ( static::is_plugin_the_events_calendar_active() ) {
			$taxonomy_groups['tribe_venue']     = [ 'group' => 'tribe_venue', 'label' => 'Event Venues' ];
			$taxonomy_groups['tribe_organizer'] = [ 'group' => 'tribe_organizer', 'label' => 'Event Organizers' ];
		}

		// include taxonomies
		foreach ( $wp_taxonomies as $name => $taxonomy ) {
			$pts = array_intersect( $taxonomy->object_type, $types );
			if ( count( $pts ) ) {
				$pt_names = [];
				foreach ( $pts as $pt ) {
					$pt_names[] = $pt;
				}
				$taxonomy_groups[$name] = [ 'group' => $name, 'label' => $taxonomy->label, 'post_types' => $pt_names, ];
			}
		}

		// populate selected taxonomy type
		$selected_values = isset( $this->options['taxonomies'] ) ? $this->options['taxonomies'] : [];
		foreach ( $taxonomy_groups as &$taxonomy_group ) {
			$taxonomy_group['var']  = 'taxonomies';
			$taxonomy_group['type'] = isset( $selected_values[$taxonomy_group['group']]['type'] ) ? $selected_values[$taxonomy_group['group']]['type'] : self::DEFAULT_TAXONOMY_TYPE;
		}

		uasort( $taxonomy_groups, array( $this, 'sort_assoc_array_by_label_key' ) );

		return $taxonomy_groups;
	}

	/**
	 * Returns all built-in post type fields.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @return   string[] fields
	 **/
	private function built_in_fields() {
		$default_fields  = OlaSearch_Admin::$section_defaults['post_fields'];
		$built_in_fields = [];

		foreach ( $default_fields as $name => $default ) {
			$type                   = ! empty( $default ) ? $default['type'] : self::DEFAULT_FIELD_TYPE;
			$built_in_fields[$name] = [
				'var'        => 'post_fields',
				'name'       => $name,
				'label'      => OlaSearch_Admin::$field_labels[$name],
				'type'       => $type,
				'post_types' => [],
			];
			if ( $type === self::TAXONOMY_ENTITY_TYPE ) {
				$built_in_fields[$name]['group'] = $default['group'];
			}
		}

		uasort( $built_in_fields, array( $this, 'sort_assoc_array_by_label_key' ) );

		return $built_in_fields;
	}

	/**
	 * Returns all custom fields registered for any post type.
	 * Copied method meta_form() from admin/includes/templates.php as inline method.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @return   string[] meta keys sorted
	 **/
	private static function custom_fields() {
		global $wpdb;
		$options       = static::$instance->options;
		$custom_fields = [];
		$keys          = $wpdb->get_col( "SELECT meta_key
                            FROM $wpdb->postmeta
                            GROUP BY meta_key
                            HAVING meta_key NOT LIKE '\_%'
                            ORDER BY meta_key" );

		if ( $keys ) {
			$meta_fields     = isset( $options['meta_fields'] ) ? $options['meta_fields'] : [];
			$selected_values = isset( $meta_fields['custom_fields'] ) ? $meta_fields['custom_fields'] : [];
			foreach ( $keys as $name ) {
				$label                = ucwords( implode( ' ', preg_split( '/[-_]/', $name ) ) );
				$custom_fields[$name] = [
					'var'        => 'custom_fields',
					'name'       => $name,
					'label'      => $label,
					'type'       => self::DEFAULT_FIELD_TYPE,
					'checked'    => false,
					'post_types' => [],
				];
				if ( isset( $selected_values[$name] ) ) {
					if ( isset( $selected_values[$name]['type'] ) ) {
						$custom_fields[$name]['type'] = $selected_values[$name]['type'];
					}
					if ( isset( $selected_values[$name]['checked'] ) ) {
						$custom_fields[$name]['checked'] = $selected_values[$name]['checked'];
					}
				}
			}
		}

		uasort( $custom_fields, array( static::$instance, 'sort_assoc_array_by_label_key' ) );

		return $custom_fields;
	}

	/**
	 * Returns all custom fields registered for any post type or plugin as groups.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @return   string[] meta field groups
	 **/
	private static function meta_field_groups() {
		$meta_field_groups = [
			'tribe_events'  => [
				'label'  => 'Events Calendar fields',
				'fields' => static::$instance->tribe_events_fields(),
			],
			'custom_fields' => [
				'label'  => 'Custom fields',
				'fields' => static::$instance->custom_fields(),
			],
		];

		return $meta_field_groups;
	}

	/**
	 * Helps to normalize the meta key. Used to clean up The Events Calendar plugin fields.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    string $meta_key meta key or label
	 * @param    null|string $remove_str The string to be replaced
	 * @param    bool $label clean up lable or meta key
	 * @return   bool|mixed|string normalized meta key
	 */
	private static function normalize_meta_key( $meta_key, $remove_str = null, $label = false ) {
		if ( $label ) {
			$replace_list = [ 'U R L' => 'URL', 'U T C' => 'UTC', 'I D' => 'ID' ];
			$label        = preg_replace( '([A-Z])', ' $0', str_replace( $remove_str, '', $meta_key ) );
			foreach ( $replace_list as $search => $replace ) {
				$label = str_replace( $search, $replace, $label );
			}

			return $label;
		}

		return strtolower( str_replace( $remove_str, '', $meta_key ) );
	}

	/**
	 * Returns all meta tribe_events fields from The Events Calendar plugin.
	 *
	 * @since    2.0.0
	 *
	 * @return   array
	 */
	public static function tribe_events_fields() {
		$options = static::$instance->options;

		$tribe_events_fields = [];

		// checks whether the The Events Calendar plugin is active
		if ( ! static::is_plugin_the_events_calendar_active() ) {
			return $tribe_events_fields;
		}

		$exclude_fields      = [ '_EventVenueID', '_EventOrganizerID', '_tribe_events_errors', '_tribe_featured' ];
		$tribe_events        = Tribe__Events__Main::instance();
		$event_field_map     = [
			'cat'       => [ 'label' => 'Event Categories', 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'tribe_events_cat' ],
			'organizer' => [ 'label' => 'Event Organisers', 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'tribe_organizer' ],
			'venue'     => [ 'label' => 'Event Venues', 'type' => self::TAXONOMY_ENTITY_TYPE, 'group' => 'tribe_venue' ],
		];
		$venue_field_map     = [];
		$tribe_organizer_map = [];
		foreach ( $tribe_events->metaTags as $meta_key ) {
			$field = static::normalize_meta_key( $meta_key, '_Event' );
			if ( in_array( $meta_key, $exclude_fields ) ) {
				continue;
			}
			$event_field_map[$field] = [
				'meta_key' => $meta_key,
				'label'    => static::normalize_meta_key( $meta_key, '_', true ),
			];
		}
		foreach ( $tribe_events->venueTags as $meta_key ) {
			$field = static::normalize_meta_key( $meta_key, '_Venue' );
			if ( in_array( $meta_key, $exclude_fields ) ) {
				continue;
			}
			$venue_field_map[$field] = [
				'meta_key' => $meta_key,
				'label'    => static::normalize_meta_key( $meta_key, '_', true ),
			];
		}
		foreach ( $tribe_events->organizerTags as $meta_key ) {
			$field = static::normalize_meta_key( $meta_key, '_Organizer' );
			if ( in_array( $meta_key, $exclude_fields ) ) {
				continue;
			}
			$tribe_organizer_map[$field] = [
				'meta_key' => $meta_key,
				'label'    => static::normalize_meta_key( $meta_key, '_', true ),
			];
		}

		$field_sets = [
			'tribe_events'    => [
				'field_prefix' => 'tribe_events',
				'field_map'    => $event_field_map,
			],
			'tribe_venue'     => [
				'field_prefix' => 'tribe_venue',
				'field_map'    => $venue_field_map,
			],
			'tribe_organizer' => [
				'field_prefix' => 'tribe_organizer',
				'field_map'    => $tribe_organizer_map,
			],
		];

		$meta_fields = isset( $options['meta_fields'] ) ? $options['meta_fields'] : [];
		foreach ( $field_sets as $var => $field_set ) {
			$selected_values = isset( $meta_fields[$var] ) ? $meta_fields[$var] : [];
			foreach ( $field_set['field_map'] as $field => $meta ) {
				$name                       = $field_set['field_prefix'] . "_$field";
				$tribe_events_fields[$name] = [
					'var'        => $var,
					'meta_key'   => $meta['meta_key'],
					'name'       => $name,
					'label'      => $meta['label'],
					'type'       => self::DEFAULT_FIELD_TYPE,
					'group'      => $meta['group'],
					'checked'    => false,
					'post_types' => [],
				];
				if ( isset( $selected_values[$name] ) ) {
					if ( isset( $selected_values[$name]['type'] ) ) {
						$tribe_events_fields[$name]['type'] = $selected_values[$name]['type'];
					}
					if ( isset( $selected_values[$name]['checked'] ) ) {
						$tribe_events_fields[$name]['checked'] = $selected_values[$name]['checked'];
					}
				}
			}
		}

		uasort( $tribe_events_fields, array( static::$instance, 'sort_assoc_array_by_label_key' ) );

		return $tribe_events_fields;
	}

	/**
	 * Get all labels for fields.
	 *
	 * @since    2.0.0
	 *
	 * @return   array
	 */
	public static function get_field_labels() {
		$field_labels = [];

		// built-in fields
		$built_in_fields = static::$instance->built_in_fields();
		foreach ( $built_in_fields as $field_name => $field ) {
			$field_labels[$field_name] = trim( $field['label'] );
		}

		// taxonomies
		$taxonomy_fields = static::$instance->taxonomies();
		foreach ( $taxonomy_fields as $field_name => $field ) {
			$field_labels[$field_name] = trim( $field['label'] );
		}

		// meta field groups
		$meta_field_groups = static::$instance->meta_field_groups();
		foreach ( $meta_field_groups as $meta_field_group_type => $meta_field_group ) {
			foreach ( $meta_field_group['fields'] as $field_name => $field ) {
				$field_labels[$field_name] = trim( $field['label'] );
			}
		}

		return $field_labels;
	}

	/**
	 * Checks whether the The Events Calendar plugin is active or not.
	 *
	 * @since    2.0.0
	 *
	 * @return   boolean
	 **/
	public static function is_plugin_the_events_calendar_active() {
		return is_plugin_active( 'the-events-calendar/the-events-calendar.php' );
	}

	/**
	 * Comparison function to be used by sort method to sort fields.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    array $a The first associative array.
	 * @param    array $b The second associative array.
	 * @return   bool
	 */
	private function sort_assoc_array_by_label_key( $a, $b ) {
		return strcasecmp( $a['label'], $b['label'] ) > 0 ? true : false;
	}

	private static function is_associative_array( array $arr ) {
		if ( [] === $arr ) {
			return false;
		}

		return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
	}

	public static function get_field_name_by_type( $field, $type ) {
		return $field;
	}

	public static function checkbox( $var, $name, $value = 1, $description = null ) {
		$options = static::$instance->options;
		$option  = static::$instance->plugin_name;
		if ( is_null( $description ) ) {
			$description = $name;
		}

		$checked = false;

		if ( is_array( $var ) ) {
			$id           = implode( '_', $var ) . '_' . $name;
			$temp_var     = '';
			$temp_options = $options;
			foreach ( $var as $v ) {
				$temp_var .= "[$v]";
				if ( isset( $temp_options[$v] ) ) {
					if ( ! isset( $temp_options[$v][$name] ) ) {
						$temp_options = $temp_options[$v];
					} else {
						$checked = ! empty( $temp_options[$v][$name] );
					}
				}
			}
			$name = $option . $temp_var . '[' . $name . ']';
		} else {
			$id = $var . '_' . $name;
			if ( isset( $options[$var] ) ) {
				if ( static::is_associative_array( $options[$var] ) ) {
					$checked = isset( $options[$var][$name] ) && ! empty( $options[$var][$name] );
				} else {
					$checked = in_array( $name, $options[$var] );
				}
			}
			$name = $option . '[' . $var . '][' . $name . ']';
		}
		$checked = $checked
			? ' checked'
			: '';
		?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php echo $description ?></span></legend>
            <label for="<?php echo $id ?>">
                <input type="checkbox" id="<?php echo $id ?>"
                       name="<?php echo $name ?>"
                       value="<?php echo $value ?>"<?php echo $checked ?>/>
                <span><?php esc_attr_e( $description ); ?></span>
            </label>
        </fieldset>
		<?php
	}

	public static function select( $var, $name, $options, $selected ) {
		$option = static::$instance->plugin_name;

		if ( is_array( $var ) ) {
			$temp_var = '';
			foreach ( $var as $v ) {
				$temp_var .= "[$v]";
			}
			$var = $temp_var;
		} else {
			$var = "[$var]";
		}

		$name = $option . $var . '[' . $name . ']';
		echo '<select name="' . $name . '" id="field_' . $name . '">';
		foreach ( $options as $type => $label ) {
			$s = $selected == $type ? 'selected' : '';
			echo '<option value="' . $type . '" ' . $s . '>' . $label . '</option>';
		}
		echo '</select>';
	}

	public static function notice( $message, $type = 'info', $sticky = false ) {
		$class = $sticky ? ' inline' : '';
		echo '<div class="notice notice-' . $type . $class . ' is-dismissible"><p> ';
		if ( $type == 'info' ) {
			echo '<i class="dashicons dashicons-info"></i> ';
		}

		echo $message;
		echo '</p><button type="button" class="notice-dismiss'
			. '"><span class="screen-reader-text">Dismiss this notice.</span></button></div><p></p>';
	}

	public static function update_option_for_section( $input, $section ) {
		$option  = static::$instance->plugin_name;
		$options = static::$instance->options;

		switch ( $section ) {
			case self::SETTINGS_TAB:
				$options['api_key'] = $input['api_key'];
				if ( $options['enable_search'] != $input['enable_search'] ) {
					self::update_status_search_page( $input['enable_search'] ? 'publish' : 'draft' );
				}
				$options['enable_search'] = $input['enable_search'];
				if ( isset( $input['api_server'] ) ) {
					$options['api_server'] = $input['api_server'];
				}
				break;

			case 'post_types':

				if ( isset( $input[$option][$section] ) ) {
					$options[$section] = array_keys( $input[$option][$section] );
				} else {
					static::notice( 'At least one post type must be selected', 'error' );

					return false;
				}
				break;

			case 'post_fields' :

				if ( isset( $input[$option][$section] ) ) {
					$values = $input[$option][$section];
					if ( isset( $input[$option][$section . '_type'] ) ) {
						$types = $input[$option][$section . '_type'];
						foreach ( $values as $key => $value ) {
							if ( isset( $types[$key] ) ) {
								$values[$key] = $types[$key];
							}
						}
					}
				} else {
					$values = [];
				}
				$options[$section] = $values;

				//meta_fields
				$section = 'meta_fields';
				if ( isset( $input[$option][$section] ) ) {
					$values = $input[$option][$section];
				} else {
					$values = [];
				}
				$options[$section] = $values;

				break;

			case 'taxonomies':

				if ( isset( $input[$option][$section] ) ) {
					$options[$section] = $input[$option][$section];
				} else {
					static::notice( 'Missing taxonomies', 'error' );

					return false;
				}

				break;


		}

		$updated = OlaSearch_Indexer::update_options( $options );
		if ( $updated ) {
			OlaSearch_Indexer::reset_posts_to_index();
		}

		return $updated;
	}


	public static function tabs() {
		$section = static::$instance->section();
		$page    = static::$instance->plugin_name;

		if ( isset( $_POST['updated'] ) && $_POST['updated'] === 'true' ) {
			if (
				! isset( $_POST[$section] ) ||
				! wp_verify_nonce( $_POST[$section], $page . '_update' )
			) {
				//add_settings_error( $page, $page, 'Invalid Update. Please use the form', 'error' );
				static::notice( 'Invalid Update. Please use the form', 'error' );
			} else {
				$success = static::update_option_for_section( $_POST, $section );
				if ( $success ) {
					static::notice( 'Settings saved. The content will have to be <a href="' . admin_url( 'admin.php?page=' . static::SETTINGS_TAB ) . '">re-indexed</a> with the new settings.', 'success' );
				}
			}
		}

		return $section;
	}

	/**
	 * Post types by WordPress
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @return   array
	 */
	private function get_types_by_wordpress() {
		static $types_by_wordpress;
		if ( $types_by_wordpress !== null ) {
			return $types_by_wordpress;
		}

		$cpts_raw = array(
			'post'       => array( 'slug' => 'post', '_buildin' => 1 ),
			'page'       => array( 'slug' => 'page', '_buildin' => 1 ),
			'attachment' => array( 'slug' => 'attachment', '_buildin' => 1 ),
		);

		$cpts = array();
		foreach ( $cpts_raw as $cpt_raw ) {
			$post_type = get_post_type_object( $cpt_raw['slug'] );
			// only use active post types
			if ( isset( $post_type->name ) ) {
				$cpts[$cpt_raw['slug']] = $post_type;
			}
		}

		uasort( $cpts, array( $this, 'sort_post_types_by_name' ) );
		$types_by_wordpress = $cpts;

		return $types_by_wordpress;
	}

	/**
	 * Post types by 3rd party (by themes/plugins)
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @return   array
	 */
	private function get_types_by_3rd() {
		static $types_by_3rd;
		if ( $types_by_3rd !== null ) {
			return $types_by_3rd;
		}

		$cpts_raw = get_post_types( [ 'public' => true ] );
		// manually adding internal post types from The Events Calendar plugin
		if ( static::is_plugin_the_events_calendar_active() ) {
			$cpts_raw['tribe_venue']     = 'tribe_venue';
			$cpts_raw['tribe_organizer'] = 'tribe_organizer';
		}
		$cpts = array();
		foreach ( $cpts_raw as $cpt_slug => $cpt_raw ) {
			$post_type = get_post_type_object( $cpt_slug );
			// only use active post types
			if ( isset( $post_type->name ) )
				$cpts[$cpt_slug] = $post_type;
		}

		$cpts = array_diff_key( $cpts, $this->get_types_by_wordpress() );

		uasort( $cpts, array( $this, 'sort_post_types_by_name' ) );
		$types_by_3rd = $cpts;

		return $types_by_3rd;
	}

	/**
	 * Comparison function to be used by sort method to sort post types by name.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @return   bool
	 **/
	private function sort_post_types_by_name( $a, $b ) {
		return strcasecmp( $a->label, $b->label ) > 0 ? true : false;
	}

	/**
	 * Post types filtered by screen options.
	 *
	 * @since    2.0.0
	 *
	 * @param    null|bool $built_in filter by built-in or not or all
	 * @return   array
	 */
	public static function get_post_types_filtered_by_screen_options( $built_in = null ) {
		static $built_in_cpts_filtered;
		static $custom_cpts_filtered;
		static $all_cpts_filtered;

		if ( is_null( $built_in ) ) {
			if ( ! is_null( $all_cpts_filtered ) ) {
				return $all_cpts_filtered;
			}

			$all_cpts_filtered = static::$instance->get_types_by_wordpress() + static::$instance->get_types_by_3rd();

			// by default no media
			if ( isset( $built_in_cpts_filtered['attachment'] ) ) {
				unset( $built_in_cpts_filtered['attachment'] );
			}

			uasort( $all_cpts_filtered, array( static::$instance, 'sort_post_types_by_name' ) );

			return $all_cpts_filtered;
		} else if ( $built_in ) {
			if ( ! is_null( $built_in_cpts_filtered ) ) {
				return $built_in_cpts_filtered;
			}

			$built_in_cpts_filtered = static::$instance->get_types_by_wordpress();

			// by default no media
			if ( isset( $built_in_cpts_filtered['attachment'] ) ) {
				unset( $built_in_cpts_filtered['attachment'] );
			}

			return $built_in_cpts_filtered;
		} else {
			if ( ! is_null( $custom_cpts_filtered ) ) {
				return $custom_cpts_filtered;
			}

			$custom_cpts_filtered = static::$instance->get_types_by_3rd();

			return $custom_cpts_filtered;
		}
	}

	/**
	 * Create the Ola Search search page.
	 *
	 * @since    2.0.0
	 */
	public static function create_search_page() {
		$options       = static::$instance->options;
		$page_status   = $options['enable_search'] ? 'publish' : 'draft';
		$page_content  = '[ola_serp]';
		$page_template = 'olasearch-template.php';
		$page_check    = get_page_by_title( self::SEARCH_PAGE_TITLE );
		$search_page   = [
			'post_type'      => 'page',
			'post_title'     => self::SEARCH_PAGE_TITLE,
			'post_content'   => $page_content,
			'post_status'    => $page_status,
			'post_author'    => 1,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		];
		if ( ! isset( $page_check->ID ) ) {
			$search_page_id = wp_insert_post( $search_page );
		} else {
			$search_page_id = $page_check->ID;
			self::update_status_search_page( $page_status ); // enable or disable search page
		}

		// update the search page template
		update_post_meta( $search_page_id, '_wp_page_template', $page_template );
	}

	/**
	 * Delete the Ola Search search page.
	 *
	 * @since    2.0.0
	 */
	public static function delete_search_page() {
		$page_check = get_page_by_title( self::SEARCH_PAGE_TITLE );
		if ( isset( $page_check->ID ) ) {
			wp_delete_post( $page_check->ID, true ); // this will trash, not delete
		}
	}

	/**
	 * Update status of the Ola Search search page.
	 *
	 * @since    2.0.0
	 *
	 * @param    string $status The status of the page.
	 */
	public static function update_status_search_page( $status ) {
		$page_check = get_page_by_title( self::SEARCH_PAGE_TITLE );
		if ( isset( $page_check->ID ) ) {
			// update the status of the page
			wp_update_post( [ 'ID' => $page_check->ID, 'post_status' => $status ] );
		}
	}

}
