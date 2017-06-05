<?php
/**
 * FooBoxShare Social Sharing Class
 * Version 1.1.0
 *
 */
if ( ! class_exists( 'FooBoxShare' ) ) {

	define( 'FOOBOXSHARE_PATH', plugin_dir_path( __FILE__ ) );
	define( 'FOOBOXSHARE_URL', plugin_dir_url( __FILE__ ) );
	define( 'FOOBOXSHARE_PARAM_EXTERNAL', 'share_fragment' ); //this parameter is part of the url that is shared to the different networks
	define( 'FOOBOXSHARE_PARAM_EXTERNAL_DEBUG', 'fooboxsharedebug' ); //this parameter is used when testing external share urls
	define( 'FOOBOXSHARE_PARAM_INTERNAL', 'foobox_share' ); //this is used when we are performing a share internally
	define( 'FOOBOXSHARE_NONCE_ACTION', 'fooboxshare_nonce' );
	define( 'FOOBOXSHARE_VERSION', '1.1.0' );

	define( 'FOOBOXSHARE_FILTER_SHARING_PARAM', 'fooboxshare_param' );

	class FooBoxShare {

		/** @var  FooBoxShare_Network_Base[]  */
		private $_networks;

		private $_sharing_request_args = array(
			'network'      => array ( 'required' => true ),
			'content_url'  => array ( 'required' => true ),
			'content_type' => array ( 'required' => true ),
			'hash'         => array ( 'required' => false ),
			'title'        => array ( 'required' => false ),
			'description'  => array ( 'required' => false ),
			'post_id'      => array ( 'required' => false )
		);

		function __construct() {

			$this->_networks = fooboxshare_available_networks();

			if ( !is_admin() ) {
				add_filter( 'the_content', array( $this, 'add_postid_hidden_input' ), 99 );
				add_action( 'template_redirect', array($this, 'listen'), 1 );
			} else {
				add_action( 'admin_init', array($this, 'listen'), 1 );
			}
		}

		/**
		 * Listen for any sharing that FooBox needs to handle
		 */
		function listen() {
			//intercept when the visitor is sharing content
			$this->listen_for_internal_sharing();

			//intercept when either a crawler is crawling a shared url, or a potential new visitor has clicked on a shared url
			$this->listen_for_external_share();
		}

		/**
		 * Returns a specific network by key
		 *
		 * @param $name string The key of the network we are looking for
		 *
		 * @return FooBoxShare_Network_Base Returns a network object, else boolean false
		 */
		function get_supported_network( $name ) {
			$networks = $this->_networks;

			if ( array_key_exists( $name, $networks ) ) {
				return $networks[$name];
			}

			return null;
		}

		/**
		 * Generates a share URL for a network, based on the share object saved in the database
		 *
		 * @param $network_name string Name of the network
		 * @param $share FooBoxShare_Data_v1 The share object
		 *
		 * @return mixed Returns a URL string for a valid network, else boolean false
		 */
		function generate_share_url( $network_name, $share ) {
			$network = $this->get_supported_network( $network_name );

			if ( $network !== false ) {
				return $network->get_url( $share );
			}

			return false;
		}

		/**
		 * Extract the share data from the current request
		 *
		 * @return FooBoxShare_Data_v1
		 */
		function extract_share_data_from_external_request() {
			$param = fooboxshare_sharing_param();

			if ( isset( $_GET[ $param ] ) ) {
				$param_data = $_GET[ $param ];

				$data = gzinflate( base64_decode( $param_data ) );

				parse_str( $data, $array );

				return new FooBoxShare_Data_v1( $array );

			}
			return null;
		}

		/**
		 * Extract the sharing data from the request
		 *
		 * @return FooBoxShare_Data_v1|bool
		 */
		function extract_share_data_from_internal_request() {
			$data = new FooBoxShare_Data_v1();

			foreach ( $this->_sharing_request_args as $key => $args ) {

				if ( isset( $_GET[ $key ] ) ) {
					$data->$key = $_GET[ $key ];
				} else {
					//we could not find the data in the request. Check if it is required
					if ( true === $args['required'] ) {
						//if it is required and we don't have anything then return false!
						return false;
					}
				}
			}

			//if we have a post_id, then get the permalink, otherwise get the current url
			if ( isset( $data->post_id ) && intval( $data->post_id ) > 0 ) {
				$data->url = get_permalink( $data->post_id );
			} else {

				$url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

				$data->url = strtok( $url, '?' );
			}

			return $data;
		}

		/**
		 * Adds a hidden input with the post ID to all pages.
		 * @param $content
		 *
		 * @return string
		 */
		public function add_postid_hidden_input($content){
			return '<input class="fooboxshare_post_id" type="hidden" value="' . get_the_ID() . '"/>' . $content;
		}

		/**
		 * Listen for when a visitor is trying to share content
		 */
		function listen_for_internal_sharing() {

			//check if we are trying to share
			if ( !isset( $_GET[FOOBOXSHARE_PARAM_INTERNAL] ) ) {
				return;
			}

			$nonce = $_GET[FOOBOXSHARE_PARAM_INTERNAL];

			//verify that we have a valid request
			if ( empty( $nonce ) ) {
				return;
			}

			//check if trying to share from an admin page and abort early.
			if ( is_admin() ) {
				wp_die( __('Social sharing is not available from a page within your WordPress admin. Instead, please test sharing from a published page or post.', 'foobox' ) );
				exit;
			}

			//extract all the data we need from the request
			$share_data = $this->extract_share_data_from_internal_request();

			//check if we got back something that we can use
			if ( false !== $share_data ) {

				//get the network
				$network = $this->get_supported_network( $share_data->network );

				if ( isset( $network ) ) {
					//build up the redirect URL
					$share_data->redirect_url = $share_data->url . ( ! empty( $share_data->hash ) ? $share_data->hash : '' );

					//stripping html from title and desc
					$share_data = $this->cleanup_share_data( $share_data );

					//build up the share urls
					$share_data = $this->build_share_url( $share_data );

					//redirect to the network share url
					$network_share_url = $network->get_url( $share_data );

					//echo $network_share_url;
					header( "Location: {$network_share_url}" );
					nocache_headers();
					exit;
				}
			}

			wp_die( __( 'There was a problem sharing!', 'fooboxshare' ) );
			exit;
		}

		/**
		 * @param $share_data FooBoxShare_Data_v1
		 *
		 * @return FooBoxShare_Data_v1
		 */
		function cleanup_share_data ( $share_data ) {
			$share_data->title = wp_strip_all_tags( $share_data->title );
			$share_data->description = wp_strip_all_tags( $share_data->description );

			return $share_data;
		}

		/**
		 * @param $share_data FooBoxShare_Data_v1
		 *
		 * @return FooBoxShare_Data_v1
		 */
		function build_share_url( $share_data ) {
			$query_string_array = json_decode( json_encode( $share_data ), true );
			$query_string = http_build_query( $query_string_array );

			$fooboxshareparam = urlencode( base64_encode( gzdeflate( $query_string, 9 ) ) );

			$share_url = add_query_arg( fooboxshare_sharing_param(), $fooboxshareparam, $share_data->url );

			$share_data->share_url = apply_filters( 'fooboxshareurl', $share_url );

			return $share_data;
		}

		/**
		 * Listen for when the shared content is being crawled or visited by a potential visitor
		 */
		function listen_for_external_share() {

			if ( !isset( $_GET[FOOBOXSHARE_PARAM_EXTERNAL] ) ) {
				return;
			}

			//make sure we are dealing with a GET request
			if ( strtolower( $_SERVER['REQUEST_METHOD'] ) === 'get' ) {

				//try to extract a share id from the request
				$share_data = $this->extract_share_data_from_external_request();

				//only continue if we have share data
				if ( !$share_data->exists ) {
					return;
				}

				//check for a crawler?
				foreach ( $this->_networks as $network_name => $network ) {
					if ( $network->has_crawler() ) {
						//check for a user agent match
						$debug = isset( $_GET[FOOBOXSHARE_PARAM_EXTERNAL_DEBUG] ) ? $_GET[FOOBOXSHARE_PARAM_EXTERNAL_DEBUG] : false;

						if ( $debug === $network_name || preg_match( $network->ua_regex, $_SERVER['HTTP_USER_AGENT'] ) ) {

							//apply any changes to the share data before the crawler crawls
							$share_data = apply_filters( 'fooboxshare_sharedata_for_crawlers', $share_data );

							//we can now manipulate the share data for even better sharing! E.g. get a thumb for videos OR get a smaller image for twitter cards
							$share_data = $this->manipulate_share_data( $share_data, $network );

							if ( true === $this->crawler_handle_request( $network, $share_data ) ) {
								//we are handling a response for a crawler, so do not redirect
								exit;
							}
						}
					}
				}

				//if we get here, then we are not dealing with a crawler, so we need to redirect
				header( "Location: {$share_data->redirect_url}" );
				nocache_headers();
				exit;
			}
		}

		/**
		 * @param FooBoxShare_Network_Base $network
		 * @param FooBoxShare_Data_v1      $share_data
		 *
		 * @return bool
		 */
		function crawler_handle_request($network, $share_data) {
			global $fooboxshare_current_share_network;
			global $fooboxshare_current_share_data;

			$fooboxshare_current_share_network = $network;
			$fooboxshare_current_share_data = $share_data;

			switch ( $share_data->content_type ) {
				case 'image':
				case 'video':
					nocache_headers();
					include_once( FOOBOXSHARE_PATH . "/crawler-markup-{$share_data->content_type}.php" );
					return true;
			}

			return false;
		}

		function generate_twitter_card_test_share_url() {

			$share_data = new FooBoxShare_Data_v1();

			$share_data->exists = true;
			$share_data->url = trailingslashit( home_url() );
			$share_data->content_url = trailingslashit( FOOBOXSHARE_URL ) . 'assets/twitter.jpg';
			$share_data->content_type = 'image';
			$share_data->title = __( 'Twitter Site Validation Success!', 'foobox' );
			$share_data->description = __( 'You need to validate your site with Twitter before you can share Twitter Cards. If you see a Twitter logo above and you see a message stating your domain is whitelisted for summary_large_image card, then you have successfully validated your site.', 'foobox' );
			$share_data->redirect_url = $share_data->url;

			$share_data = $this->build_share_url( $share_data );

			return $share_data->share_url;
		}

		function generate_pinterest_rich_pin_test_share_url() {

			$share_data = new FooBoxShare_Data_v1();

			$share_data->exists = true;
			$share_data->url = trailingslashit( home_url() );
			$share_data->content_url = trailingslashit( FOOBOXSHARE_URL ) . 'assets/pinterest.jpg';
			$share_data->content_type = 'image';
			$share_data->title = __( 'Pinterest Site Validation Success!', 'foobox' );
			$share_data->description = __( 'You need to validate your site with Pinterest before you can share Rich Pins. If you get a message "Your Pin\'s have been validated!" then you have successfully validated your site.', 'foobox' );
			$share_data->redirect_url = $share_data->url;

			$share_data = $this->build_share_url( $share_data );

			return $share_data->share_url;
		}

		/**
		 * @param $share_data FooBoxShare_Data_v1
		 * @param $network FooBoxShare_Network_Base
		 *
		 * @return mixed
		 */
		public function manipulate_share_data( $share_data, $network ) {
			if ( 'image' === $share_data->content_type ) {
				$original_image_url = $share_data->content_url;

				$attachment_id = attachment_url_to_postid( $original_image_url );

				if ( 0 !== $attachment_id ) {

					//get the preferred image size for the network
					$size = $network->preferred_image_size();

					$image_url = wp_get_attachment_image_src( $attachment_id, $size );

					if ( false !== $image_url ) {
						$share_data->content_url = $image_url[0];
						$share_data->image_width = $image_url[1];
						$share_data->image_height = $image_url[2];
					}

					//check we have at least an image title
					if ( empty( $share_data->title ) ) {
						$share_data->title = get_the_title( $attachment_id );
					}
				}
			} else if ( 'video' === $share_data->content_type ) {

				require_once( ABSPATH.'wp-includes/class-oembed.php' );
				$oembed = new WP_oEmbed;
				$provider = $oembed->discover( $share_data->content_url );
				if ( false !== $provider ) {
					$video                 = $oembed->fetch( $provider, $share_data->content_url, array( 'width' => 300, 'height' => 175 ) );
					$share_data->thumb_url = $video->thumbnail_url;
					$share_data->content_url = str_replace( 'http://', 'https://', $share_data->content_url );

					//handle social networks that cannot handle sharing videos correctly, e.g. Pinterst
					if ( !$network->supports_video() ) {
						$share_data->content_type = 'image';
						$share_data->content_url = $share_data->thumb_url;
					}

				} else {
					//we could not determine the provider
					$share_data->content_type = 'html';
					$share_data->content_url = $share_data->redirect_url;
				}
			}

			return $share_data;
		}
	}
}
