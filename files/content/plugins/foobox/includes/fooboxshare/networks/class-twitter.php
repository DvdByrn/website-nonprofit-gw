<?php
if ( ! class_exists( 'FooBoxShare_Network_Twitter' ) ) {

	define( 'FOOBOXSHARE_SETTING_TWITTER_SITE', 'social_twitter_username' );
	define( 'FOOBOXSHARE_SETTING_TWITTER_IMAGE_SIZE', 'social_twitter_image_size' );

	class FooBoxShare_Network_Twitter extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'Twitter', 'fooboxshare' );
        }

		function __construct() {
			$this->ua_regex = '/twitterbot/i';
			$this->url_format = 'https://twitter.com/share?url={share_url}&text={title}';
		}

		/**
		 * @param FooBoxShare_Data_v1 $share
		 *
		 * @return mixed
		 */
		public function add_meta( $meta_tags, $share ) {
			$meta_tags['name="twitter:card"'] = 'summary_large_image';
			$meta_tags['name="twitter:site"'] = fooboxshare_get_setting( FOOBOXSHARE_SETTING_TWITTER_SITE );

			if ( isset( $share->thumb_url ) ) {
				$meta_tags["name=\"twitter:image\""] = $share->thumb_url;
			} else {
				$meta_tags["name=\"twitter:image\""] = $share->content_url;
			}

			$meta_tags['name="twitter:title"'] = $share->title;

			if ( empty( $share->description ) ) {
				$share->description = $share->title;
			}

			$meta_tags['name="twitter:description"'] = $share->description;
			return $meta_tags;
		}

		public function preferred_image_size() {
			return fooboxshare_get_setting( FOOBOXSHARE_SETTING_TWITTER_IMAGE_SIZE, 'full' );
		}
	}
}