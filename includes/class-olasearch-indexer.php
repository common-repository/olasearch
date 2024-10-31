<?php

/**
 * Methods to connect with Ola Search platform.
 *
 * @link       https://olasearch.com
 * @since      1.0.0
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/includes
 */

/**
 * Methods to connect with Ola Search platform.
 *
 * Maintain all methods necessary to communicate with Ola Search platform. It includes the methods to Add/Update/Delete
 * documents & taxonomy terms and wipe all content from Ola Search platform.
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/includes
 * @author     Ola Search <hello@olasearch.com>
 */
class OlaSearch_Indexer {
	private static $options;

	const META_KEY_OLA_INDEXED = '_ola_indexed';
	const BATCH_LIMIT = 50;

	public static function update_options( $new_options ) {
		if ( update_option( 'olasearch', $new_options ) ) {
			//reload data
			static::$options = $new_options;

			return true;
		}

		return false;
	}

	public static function options( $update = false ) {
		if ( is_null( static::$options ) || $update ) {
			static::$options = get_option( 'olasearch', [] );
		}

		return static::$options;
	}

	public static function post_types() {
		$options = static::options();

		return isset( $options['post_types'] ) ? $options['post_types'] : [];
	}

	public static function api_key() {
		$options = static::options();

		return isset( $options['api_key'] ) ? $options['api_key'] : '0';
	}

	public static function ola_host() {
		$options = static::options();
		if ( isset( $options['api_server'] ) && $options['api_server'] == OlaSearch_Admin::OLA_SERVER_INTERNAL ) {
			return OlaSearch_Admin::OLA_HOST_INTERNAL;
		} else if ( isset( $options['api_server'] ) && $options['api_server'] == OlaSearch_Admin::OLA_SERVER_LOCAL ) {
			return OlaSearch_Admin::OLA_HOST_LOCAL;
		} else {
			return OlaSearch_Admin::OLA_HOST;
		}
	}

	public static function ola_env() {
		return OlaSearch_Admin::OLA_ENVIRONMENT;
	}

	public static function ola_asset_host() {
		$options = static::options();
		if ( isset( $options['api_server'] ) && $options['api_server'] == OlaSearch_Admin::OLA_SERVER_INTERNAL ) {
			return OlaSearch_Admin::OLA_ASSET_HOST_INTERNAL;
		} else if ( isset( $options['api_server'] ) && $options['api_server'] == OlaSearch_Admin::OLA_SERVER_LOCAL ) {
			return OlaSearch_Admin::OLA_ASSET_HOST_LOCAL;
		} else {
			return OlaSearch_Admin::OLA_ASSET_HOST;
		}
	}

	public static function ola_url( $path = null ) {
		return static::ola_host() . "/api/connector/" . ( ! is_null( $path ) ? $path : '' );
	}

	/**
	 * Format the value based on the type provided.
	 *
	 * @since    2.0.0
	 *
	 * @param    string $type The type of the field.
	 * @param    mixed $val The value.
	 * @return   mixed The formatted value.
	 */
	public static function format_value( $type, $val ) {
		if ( $type == 'tdt' ) {
			try {
				$val = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $val ) );
			} catch ( Exception $e ) {
				$val = null;
			}
		} else if ( $type == 'b' ) {
			$val = (boolean) $val;
		}

		return $val;
	}

	/**
	 * Helper method to generate entities using type.
	 *
	 * @since    2.0.0
	 *
	 * @param    string $post_type The post type.
	 * @param    int $post_id The ID of the post
	 * @param    string $entity_field The name of the entity field.
	 * @param    mixed $entity_field_value The value of the entity field.
	 * @return   array
	 */
	public static function get_entities_by_type( $post_type, $post_id, $entity_field, $entity_field_value ) {
		$entities = [];
		if ( ! is_null( $entity_field_value ) && $entity_field == 'post_author' ) {
			$user = get_user_by( 'ID', $entity_field_value );
			if ( ! empty( $user ) ) {
				$entities[] = (object) [
					'term_id'          => $user->ID,
					'name'             => $user->display_name,
					'slug'             => $user->user_nicename,
					'taxonomy'         => 'user',
					'term_taxonomy_id' => 0,
					'parent'           => 0,
				];
			}
		} elseif ( $post_type === 'tribe_events' ) {
			if ( $entity_field == 'tribe_events_venue' ) {
				$venue_id = get_post_meta( $post_id, '_EventVenueID', true );
				if ( ! empty( $venue_id ) ) {
					$venue_post = get_post( $venue_id );
					if ( ! empty( $venue_post ) && $venue_post->post_status === 'publish' && ! empty( $venue_post->post_title ) ) {
						$entities[] = (object) [
							'term_id'          => $venue_post->ID,
							'name'             => $venue_post->post_title,
							'slug'             => get_permalink( $venue_post ),
							'taxonomy'         => 'tribe_venue',
							'term_taxonomy_id' => 0,
							'parent'           => 0,
						];
					}
				}
			} elseif ( $entity_field == 'tribe_events_organizer' ) {
				$organizer_ids = get_post_meta( $post_id, '_EventOrganizerID', false );
				if ( ! empty( $organizer_ids ) && is_array( $organizer_ids ) && count( $organizer_ids ) ) {
					$organizer_posts = get_posts( [ 'post_type' => 'tribe_organizer', 'post__in' => $organizer_ids ] );
					foreach ( $organizer_posts as $organizer_post ) {
						if ( ! empty( $organizer_post ) && $organizer_post->post_status === 'publish' && ! empty( $organizer_post->post_title ) ) {
							$entities[] = (object) [
								'term_id'          => $organizer_post->ID,
								'name'             => $organizer_post->post_title,
								'slug'             => get_permalink( $organizer_post ),
								'taxonomy'         => 'tribe_organizer',
								'term_taxonomy_id' => 0,
								'parent'           => 0,
							];
						}
					}
				}
			}
		}

		return $entities;
	}

	/**
	 * Formats payload and headers to connect with Ola Search platform. Parse the response received.
	 *
	 * @since    2.0.0
	 *
	 * @param    $url
	 * @param    $method
	 * @param    null $body
	 * @return   array|WP_Error
	 */
	public static function ola_remote_request( $url, $method, $body = null ) {
		$args = [
			'method'  => $method,
			'headers' => [ 'Content-type' => 'application/json', 'Authorization' => self::api_key() ],
			'timeout' => 30
		];

		if ( ! empty( $body ) ) {
			$args['body'] = json_encode( $body );
		}

		$url .= '?connector=wordpress';

		return wp_remote_request( $url, $args );
	}

	/**
	 * Send data to ola for indexing.
	 *
	 * @since    1.0.0
	 *
	 * @param    array|null $ids
	 * @param    boolean $initial
	 * @param    boolean $re_index
	 * @param    bool $last_batch
	 *
	 * @return   array
	 */
	public static function index( $ids = null, $last_batch = false, $initial = false, $re_index = false ) {
		$options = static::options();
		$query   = [ 'meta_query' => [ [ 'key' => static::META_KEY_OLA_INDEXED, 'value' => false ] ] ];
		$data    = [];

		if ( ! is_null( $ids ) && count( $ids ) ) {
			$query['post__in'] = $ids;
		}

		// if re-index, reset indexed meta field
		if ( $re_index ) {
			static::reset_posts_to_index();
		}

		// if it is initial request then prepare batch details
		if ( $initial ) {
			$data['total_posts']   = static::get_posts_to_index_count( $query );
			$data['batch_limit']   = static::BATCH_LIMIT;
			$data['no_of_batches'] = (int) ceil( $data['total_posts'] / $data['batch_limit'] );
			if ( $data['no_of_batches'] <= 1 ) {
				$last_batch = true;
			}
		}

		$query['posts_per_page']     = static::BATCH_LIMIT;
		$url                         = static::ola_url( 'document' );
		$posts                       = static::get_posts_to_index( $query );
		$data['current_batch_total'] = count( $posts );

		if ( count( $options['permanently_deleted_pending'] ) === 0 && $data['current_batch_total'] === 0 ) {
			$data['error'] = 'Indexing error: There are no documents to be indexed.';

			return $data;
		}

		$body = static::get_delete_add_update_posts( $posts, $options, $last_batch );

		// delete all pending permanently deleted posts if any
		if ( count( $options['permanently_deleted_pending'] ) ) {
			$body['delete_ids'] = array_merge( $body['delete_ids'], $options['permanently_deleted_pending'] );
		}
		$data['ids']        = array_column( $body['docs'], 'id' );
		$data['delete_ids'] = $body['delete_ids'];

		$response = self::ola_remote_request( $url, 'POST', $body );
		static::handle_ola_response( $response, $data );

		if ( $data['code'] === 200 ) { // request succeed
			if ( count( $options['permanently_deleted_pending'] ) ) {
				$options['permanently_deleted']         = array_unique( array_merge( $options['permanently_deleted'], $options['permanently_deleted_pending'] ) );
				$options['permanently_deleted_pending'] = [];
			}
			$options['last_synced'] = current_time( 'mysql' );
			static::update_options( $options );

			// mark posts as indexed
			static::update_post_meta_ola_index( [ 'post__in' => array_merge( $data['ids'], $data['delete_ids'] ) ], true );
		}

		return $data;
	}

	/**
	 * delete specific posts from ola search using ids.
	 *
	 * @since    1.0.0
	 *
	 * @param    array $ids
	 * @param    bool $update_meta_ola_index
	 * @return   array
	 */
	public static function delete( $ids = [], $update_meta_ola_index = true ) {
		$ids  = array_values( $ids );
		$url  = static::ola_url( 'document' );
		$data = [];

		if ( ! empty( $ids ) ) {
			$response = self::ola_remote_request( $url, 'DELETE', $ids );
			static::handle_ola_response( $response, $data );

			if ( $update_meta_ola_index && $data['code'] === 200 ) {
				// mark posts as indexed
				static::update_post_meta_ola_index( [ 'post__in' => $ids ], true );
			}
		}

		$data['ids'] = $ids;

		return $data;
	}

	/**
	 * Remove all indexed posts to wipe clean.
	 *
	 * @since    1.0.0
	 *
	 * @return   array
	 */
	public static function wipe() {
		$options = static::options();
		$url     = static::ola_url( 'wipe' );

		$response = self::ola_remote_request( $url, 'DELETE' );

		$data = [];
		static::handle_ola_response( $response, $data );

		if ( $data['code'] === 200 ) { // request succeed
			static::update_options( $options );

			// mark all as not indexed
			static::reset_posts_to_index();
		}

		// delete all pending permanently deleted posts
		static::delete( $options['permanently_deleted_pending'], false );

		return $data;
	}

	/**
	 * Returns formatted payload to index the documents, taxonomy terms and field mapping.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    array $posts
	 * @param    array $options
	 * @param    bool $last_batch
	 * @return   array
	 */
	private static function get_delete_add_update_posts( $posts, $options, $last_batch ) {
		$delete_ids       = [];
		$add_update_posts = [];
		$taxonomy_terms   = [];

		foreach ( $posts as $post ) {
			if ( $post->post_status == 'publish' && in_array( $post->post_type, static::post_types() ) ) {
				list( $formatted_post, $formatted_terms ) = static::post_format( $post, $options );
				$add_update_posts[] = $formatted_post;
				$taxonomy_terms     = array_merge( $taxonomy_terms, $formatted_terms );
			} else {
				$delete_ids[] = $post->ID;
			}
		}
		// TODO: make the $taxonomy_terms unique - remove duplicate

		$post_types = array_unique( array_column( $add_update_posts, 'ola_collection_slug' ) );

		return [
			'last_batch'     => $last_batch,
			'delete_ids'     => $delete_ids,
			'docs'           => $add_update_posts,
			'taxonomy_terms' => $taxonomy_terms,
			'mapping'        => static::get_post_fields_mapping( $options, $post_types ),
		];
	}

	/**
	 * Handles the response object from OLA and update data object.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    WP_Error|array $response The response or WP_Error on failure.
	 * @param    array $data
	 */
	private static function handle_ola_response( $response, &$data ) {
		if ( is_wp_error( $response ) ) { // request can't performed
			$data['error'] = $response->get_error_message();
		} else {
			$code         = wp_remote_retrieve_response_code( $response );
			$data['body'] = json_decode( wp_remote_retrieve_body( $response ) );
			$data['code'] = $code;
			if ( $code != 200 ) { // request succeed but non 200 response
				$data['error'] = $data['body']->error;
			}
		}
	}

	/**
	 * Create the mapping for the post fields which will be send to the Ola server.
	 *
	 * @since    2.0.0
	 *
	 * @param    $options
	 * @return   array
	 */
	public static function get_post_fields_mapping( $options, $post_types ) {
		$mapping              = [];
		$post_taxonomy_fields = [];

		$field_labels = OlaSearch_Admin::get_field_labels();
		foreach ( $options['post_fields'] as $field => $field_option ) {
			$field_mapping = [ 'name' => $field, 'type' => $field_option['type'], 'label' => $field_labels[$field] ];
			if ( $field_option['type'] == OlaSearch_Admin::TAXONOMY_ENTITY_TYPE ) {
				$field_mapping['group'] = $field_option['group'];
			}
			$post_taxonomy_fields[] = $field_mapping;
		}

		foreach ( $post_types as $post_type ) {
			$mapping[$post_type] = $post_taxonomy_fields;
			if ( $post_type == 'tribe_events' ) {
				$mapping[$post_type][] = [ 'name' => 'ola_location' ];
				$mapping[$post_type][] = [ 'name' => 'tribe_venue_city', 'type' => 'ss', 'label' => 'Venue City' ];
				$mapping[$post_type][] = [ 'name' => 'tribe_venue_country', 'type' => 'ss', 'label' => 'Venue Country' ];
			} else if ( $post_type == 'tribe_venue' ) {
				$mapping[$post_type][] = [ 'name' => 'ola_location' ];
			}
			if ( isset( $options['meta_fields'][$post_type] ) ) {
				foreach ( $options['meta_fields'][$post_type] as $field => $field_option ) {
					$field_mapping = [ 'name' => $field, 'type' => $field_option['type'], 'label' => $field_labels[$field] ];
					if ( $field_option['type'] == OlaSearch_Admin::TAXONOMY_ENTITY_TYPE ) {
						$field_mapping['group'] = $field_option['group'];
					}
					$mapping[$post_type][] = $field_mapping;
				}
			}
		}

		return $mapping;
	}

	/**
	 * Formats the post with tribe_events fields from The Events Calendar plugin.
	 *
	 * @since    2.0.0
	 *
	 * @param    WP_Post $post
	 * @param    $options
	 * @param    $meta_fields
	 * @param    $document
	 * @param    $taxonomy_terms
	 */
	public static function tribe_events_format( $post, $options, $meta_fields, &$document, &$taxonomy_terms ) {
		// checks whether the The Events Calendar plugin is active
		if ( $post->post_type === 'tribe_events' && OlaSearch_Admin::is_plugin_the_events_calendar_active() ) {
			$tribe_events_fields = OlaSearch_Admin::tribe_events_fields();
			$record              = self::get_and_flatten_event_meta( $post->ID );
			if ( ! empty( $record ) ) {
				foreach ( $tribe_events_fields as $field => $tribe_events_field ) {
					if ( $tribe_events_field['var'] !== 'tribe_events' ) {
						continue;
					}

					$type    = $meta_fields[$field]['type'];
					$checked = $meta_fields[$field]['checked'];
					if ( $checked ) {
						$var = OlaSearch_Admin::get_field_name_by_type( $field, $type );
						if ( $type === OlaSearch_Admin::TAXONOMY_ENTITY_TYPE ) {
							static::populate_taxonomy_field_values( $post, $options, $field, $meta_fields[$field], $document, $taxonomy_terms );
						} else {
							$val = $record[$tribe_events_field['meta_key']];
							if ( isset( $val ) && ! is_null( $val ) && $val !== '' ) {
								$document[$var] = static::format_value( $type, $val );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Formats the post with tribe_venue fields from The Events Calendar plugin.
	 *
	 * @since    2.0.0
	 *
	 * @param    WP_Post $post
	 * @param    $options
	 * @param    $meta_fields
	 * @param    $document
	 * @param    $taxonomy_terms
	 */
	public static function tribe_venue_format( $post, $options, $meta_fields, &$document, &$taxonomy_terms ) {
		// checks whether the The Events Calendar plugin is active
		if ( $post->post_type === 'tribe_venue' && OlaSearch_Admin::is_plugin_the_events_calendar_active() ) {
			$tribe_events_fields = OlaSearch_Admin::tribe_events_fields();
			foreach ( $tribe_events_fields as $field => $tribe_events_field ) {
				if ( $tribe_events_field['var'] !== 'tribe_venue' ) {
					continue;
				}

				$type    = $meta_fields[$field]['type'];
				$checked = $meta_fields[$field]['checked'];
				if ( $checked ) {
					$var = OlaSearch_Admin::get_field_name_by_type( $field, $type );
					$val = get_post_meta( $post->ID, $tribe_events_field['meta_key'], true );
					if ( isset( $val ) && ! is_null( $val ) && $val !== '' ) {
						$document[$var] = static::format_value( $type, $val );
					}
				}
			}
			$document['ola_location'] = static::find_tribe_venue_location_latlong( $document );
		}
	}

	/**
	 * Formats the post with tribe_organizer fields from The Events Calendar plugin.
	 *
	 * @since    2.0.0
	 *
	 * @param    WP_Post $post
	 * @param    $options
	 * @param    $meta_fields
	 * @param    $document
	 * @param    $taxonomy_terms
	 */
	public static function tribe_organizer_format( $post, $options, $meta_fields, &$document, &$taxonomy_terms ) {
		// checks whether the The Events Calendar plugin is active
		if ( $post->post_type === 'tribe_organizer' && OlaSearch_Admin::is_plugin_the_events_calendar_active() ) {
			$tribe_events_fields = OlaSearch_Admin::tribe_events_fields();
			foreach ( $tribe_events_fields as $field => $tribe_events_field ) {
				if ( $tribe_events_field['var'] !== 'tribe_organizer' ) {
					continue;
				}

				$type    = $meta_fields[$field]['type'];
				$checked = $meta_fields[$field]['checked'];
				if ( $checked ) {
					$var = OlaSearch_Admin::get_field_name_by_type( $field, $type );
					$val = get_post_meta( $post->ID, $tribe_events_field['meta_key'], true );
					if ( isset( $val ) && ! is_null( $val ) && $val !== '' ) {
						$document[$var] = static::format_value( $type, $val );
					}
				}
			}
		}
	}

	/**
	 * Formats the post with custom fields.
	 *
	 * @since    2.0.0
	 *
	 * @param    WP_Post $post
	 * @param    $options
	 * @param    $meta_fields
	 * @param    $document
	 * @param    $taxonomy_terms
	 */
	public static function custom_fields_format( $post, $options, $meta_fields, &$document, &$taxonomy_terms ) {
		foreach ( $meta_fields as $field => $field_option ) {
			$type    = $field_option['type'];
			$checked = $field_option['checked'];
			if ( $checked ) {
				$var = OlaSearch_Admin::get_field_name_by_type( $field, $type );
				$val = get_post_meta( $post->ID, $field, true );
				if ( isset( $val ) && ! is_null( $val ) && $val !== '' ) {
					$document[$var] = static::format_value( $type, $val );
				}
			}
		}
	}

	/**
	 * Populates document and taxo term parameters with taxo/entity fields.
	 *
	 * @since    2.0.0
	 *
	 * @param    $post
	 * @param    $options
	 * @param    $field
	 * @param    $field_option
	 * @param    $document
	 * @param    $taxonomy_terms
	 */
	public static function populate_taxonomy_field_values( $post, $options, $field, $field_option, &$document, &$taxonomy_terms ) {
		$type = $field_option['type'];
		$var  = OlaSearch_Admin::get_field_name_by_type( $field, $type );
		if ( in_array( $field, [ 'post_author', 'tribe_events_venue', 'tribe_events_organizer' ] ) ) {
			$entities = static::get_entities_by_type( $post->post_type, $post->ID, $field, $post->$field );
			$terms    = count( $entities ) ? $entities : [];

			if ( $post->post_type === 'tribe_events' && $field === 'tribe_events_venue' ) {
				$venue                           = static::find_tribe_events_venue_location_latlong( $post->ID );
				$document['tribe_venue_city']    = ! empty( $venue['tribe_venue_city'] ) ? [ $venue['tribe_venue_city'] ] : [];
				$document['tribe_venue_country'] = ! empty( $venue['tribe_venue_country'] ) ? [ $venue['tribe_venue_country'] ] : [];
				$document['ola_location']        = $venue['ola_location'];
			}
		} else {
			$terms = wp_get_object_terms( $post->ID, $field_option['group'] );
		}
		foreach ( $terms as $term ) {
			$term             = [
				'id'     => $term->term_id,
				'group'  => $term->taxonomy,
				//				'group_id' => $term->term_taxonomy_id,
				'term'   => $term->name,
				//				'slug'     => $term->slug,
				'type'   => $options['taxonomies'][$term->taxonomy]['type'],
				'parent' => $term->parent,
			];
			$document[$var][] = [ 'id' => $term['id'], 'term' => $term['term'] ];
			$taxonomy_terms[] = $term;
		}
	}

	/**
	 * Formats the post for indexing.
	 *
	 * @since    2.0.0
	 *
	 * @param    WP_Post $post
	 * @param    $options
	 *
	 * @return   array
	 */
	public static function post_format( $post, $options ) {
		global $blog_id;
		global $wp_post_types;
		$taxonomy_terms = [];
		$document       = [
			'id'                  => $post->ID,
			'ola_collection_slug' => $post->post_type,
			'ola_collection_name' => $wp_post_types[$post->post_type]->labels->singular_name,
		];

		//extract fields
		foreach ( $options['post_fields'] as $field => $field_option ) {
			$type    = $field_option['type'];
			$checked = $field_option['checked'];

			if ( $checked ) {
				$var = OlaSearch_Admin::get_field_name_by_type( $field, $type );
				if ( $type === OlaSearch_Admin::TAXONOMY_ENTITY_TYPE ) {
					static::populate_taxonomy_field_values( $post, $options, $field, $field_option, $document, $taxonomy_terms );
				} else if ( $field === 'post_url' ) {
					$document[$var] = static::format_value( $type, get_permalink( $post ) );
				} else if ( $field === 'blog_id' ) {
					$document[$var] = static::format_value( $type, $blog_id );
				} else if ( $field === 'post_thumbnail_url' && has_post_thumbnail( $post ) ) {
					$document[$var] = get_the_post_thumbnail_url( $post, [ 640, 360 ] );
				} else if ( isset( $post->$field ) ) {
					$document[$var] = static::format_value( $type, $post->$field );
				}
			}
		}

		//extract meta_fields
		foreach ( $options['meta_fields'] as $meta_field_group => $meta_fields ) {
			static::{$meta_field_group . '_format'}( $post, $options, $meta_fields, $document, $taxonomy_terms );
		}

		//extract taxonomies

		return [ $document, $taxonomy_terms ];

	}

	/**
	 * Retrieves the venue with longitude and latitude.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    $post_id
	 * @return   array
	 */
	private static function find_tribe_events_venue_location_latlong( $post_id ) {
		$venue_id = get_post_meta( $post_id, '_EventVenueID', true );
		if ( ! empty( $venue_id ) ) {
			$venue_post = get_post( $venue_id );

			$options     = static::options();
			$venue       = [];
			$venue_terms = [];
			static::tribe_venue_format( $venue_post, $options, $options['meta_fields']['tribe_venue'], $venue, $venue_terms );

			return $venue;
		}
	}

	/**
	 * Gets the longitude and latitude for the venue using venue address.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    $venue
	 * @return   array
	 */
	private static function find_tribe_venue_location_latlong( $venue ) {
		$address = '';
		if ( isset( $venue['tribe_venue_address'] ) ) {
			$address .= $venue['tribe_venue_address'];
		}
		if ( isset( $venue['tribe_venue_city'] ) ) {
			$address .= ', ' . $venue['tribe_venue_city'];
		}
		if ( isset( $venue['tribe_venue_country'] ) ) {
			$address .= ', ' . $venue['tribe_venue_country'];
		}
		if ( isset( $venue['tribe_venue_zip'] ) ) {
			$address .= ', ' . $venue['tribe_venue_zip'];
		}
		if ( ! empty( $address ) ) {
			$body = [
				'field_rules' => [
					'address' => [
						[
							"module"  => "geoloc",
							"options" => [
								"service"        => "google",
								"field"          => "latlng",
								"new_field_name" => "address_latlong_p",
								"api_key"        => "AIzaSyAcfgDmUOzK8eHDh4w_Yma_6PeI4cfvbaw"
							]
						]
					]
				],
				"data"        => [ [ "address" => $address ] ]
			];
			$args = [ 'headers' => [ 'Content-type' => 'application/json' ], 'body' => json_encode( $body ), 'timeout' => 30 ];
			static::handle_ola_response( wp_remote_post( 'http://ai.olasearch.com/v1/ca/enrich', $args ), $args );
			if ( $args['code'] === 200 && isset( $args['body'] ) && count( $args['body'] ) && count( $args['body'][0]->address_latlong_p ) && $args['body'][0]->address_latlong_p[0] && $args['body'][0]->address_latlong_p[1] ) {
				return [ $args['body'][0]->address_latlong_p[0] . ',' . $args['body'][0]->address_latlong_p[1] ];
			}
		}
	}

	/**
	 * Helper function to build the WordPress query to retrieve content.
	 *
	 * @since    2.0.0
	 * @access   private
	 *
	 * @param    array $query
	 * @return   array
	 */
	private static function build_wp_query( $query = [] ) {
		if ( is_null( $query ) ) {
			$query = [];
		}

		if ( ! isset( $query['post_type'] ) ) {
			$query['post_type'] = array_keys( OlaSearch_Admin::get_post_types_filtered_by_screen_options() );
		}

		if ( ! isset( $query['posts_per_page'] ) ) {
			$query['posts_per_page'] = -1;
		}

		if ( ! isset( $query['post_status'] ) ) {
			$query['post_status'] = [ 'publish', 'pending', 'draft'/*, 'auto-draft'*/, 'future', 'private'/*, 'inherit'*/, 'trash' ];
		}

		return $query;
	}

	/**
	 * Helper function to update meta key to indicating whether the entry is indexed or not.
	 *
	 * @since    2.0.0
	 *
	 * @param    array $query
	 * @param    bool $ola_indexed_value
	 * @return   bool
	 */
	public static function update_post_meta_ola_index( $query = [], $ola_indexed_value = false ) {
		$posts   = static::get_posts_to_index( $query );
		$updated = true;
		foreach ( $posts as $post ) {
			$updated = $updated && update_post_meta( $post->ID, static::META_KEY_OLA_INDEXED, $ola_indexed_value );
		}

		return $updated;
	}

	/**
	 * Helper function to remove all indexed meta key entries.
	 *
	 * @since    2.0.0
	 *
	 * @return   bool
	 */
	public static function remove_post_meta_keys() {
		return delete_post_meta_by_key( static::META_KEY_OLA_INDEXED );
	}

	/**
	 * Get the number of posts available for indexing.
	 *
	 * @since    2.0.0
	 *
	 * @param    array $query
	 * @return   int number of posts
	 */
	public static function get_posts_to_index_count( $query = [] ) {
		return ( new \WP_Query( static::build_wp_query( $query ) ) )->found_posts;
	}

	/**
	 * Helper function to retrieve all posts to index using the query provided.
	 *
	 * @since    2.0.0
	 * @param    array $query
	 * @return   array
	 */
	public static function get_posts_to_index( $query = [] ) {
		return query_posts( static::build_wp_query( $query ) );
	}

	/**
	 * Helper function to reset all posts to index i.e mark as not indexed.
	 *
	 * @since    2.0.0
	 *
	 */
	public static function reset_posts_to_index() {
		$options = static::options();

		$options['permanently_deleted_pending'] = array_unique( array_merge( $options['permanently_deleted_pending'], $options['permanently_deleted'] ) );
		static::update_options( $options );

		static::update_post_meta_ola_index();
	}

	/**
	 * Checks whether there is any content to be indexed.
	 *
	 * @since    2.0.0
	 *
	 * @return bool
	 */
	public static function can_index() {
		$options = static::options();

		if ( ! empty( $options['permanently_deleted_pending'] ) ) {
			return true;
		}

		$query = [ 'meta_query' => [ [ 'key' => static::META_KEY_OLA_INDEXED, 'value' => false ] ] ];

		return static::get_posts_to_index_count( $query ) > 0;
	}

	/**
	 * Checks whether there is any content to be re-indexed.
	 *
	 * @since    2.0.0
	 *
	 * @return bool
	 */
	public static function can_reindex() {
		$options = static::options();

		return ! empty( $options['last_synced'] );
	}

	/**
	 * Gets all post meta and flattens it out a bit
	 *
	 * @since    2.0.3
	 *
	 * @param int $event_id Post ID for event
	 *
	 * @return array
	 */
	private static function get_and_flatten_event_meta( $ID ) {
		if ( method_exists( Tribe__Events__API, 'get_and_flatten_event_meta' ) ) {
			return Tribe__Events__API::get_and_flatten_event_meta( $ID );
		} else {
			$temp_post_meta = get_post_meta( $ID );
			$post_meta      = array();
			foreach ( (array) $temp_post_meta as $key => $value ) {
				if ( 1 === count( $value ) ) {
					$post_meta[$key] = maybe_unserialize( reset( $value ) );
				} else {
					$post_meta[$key] = maybe_unserialize( $value );
				}
			}

			return $post_meta;
		}
	}
}