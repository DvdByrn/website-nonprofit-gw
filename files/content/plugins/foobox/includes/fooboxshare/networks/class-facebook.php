<?php
if ( ! class_exists( 'FooBoxShare_Network_Facebook' ) ) {

	define( 'FOOBOXSHARE_SETTING_FACEBOOK_ID', 'social_facebook_appid' );

	class FooBoxShare_Network_Facebook extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'Facebook', 'fooboxshare' );
        }

		function __construct() {
			$this->ua_regex = '/(facebookexternalhit|facebot)/i';
			$this->url_format = 'https://www.facebook.com/dialog/share?app_id={app_id}&display=popup&href={share_url}';
		}

		public function get_url( $share ) {
			$share->app_id = fooboxshare_get_setting( FOOBOXSHARE_SETTING_FACEBOOK_ID, fooboxshare_facebook_default_app_id(), true );
			return parent::get_url( $share );
		}

		/**
		 * @param FooBoxShare_Data_v1 $share
		 *
		 * @return mixed
		 */
		public function add_meta( $meta_tags, $share ) {
			$meta_tags['property="fb:app_id"'] = fooboxshare_get_setting( FOOBOXSHARE_SETTING_FACEBOOK_ID, fooboxshare_facebook_default_app_id(), true );
			return $meta_tags;
		}
	}

}