<?php
if ( ! class_exists( 'FooBoxShare_Network_Pinterest' ) ) {

	class FooBoxShare_Network_Pinterest extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'Pinterest', 'fooboxshare' );
        }

		function __construct() {
			$this->ua_regex = '/Pinterest/i';
			$this->url_format = 'https://pinterest.com/pin/create/bookmarklet/?media={content_url}&url={share_url}&is_video={is_video}&description={title}';
		}

		/**
		 * @param $share FooBoxShare_Data_v1
		 *
		 * @return string
		 */
		public function get_url( $share ) {
			$share->is_video = $share->content_type === 'video';
			return parent::get_url( $share );
		}

		public function supports_video() {
			return false;
		}
	}

}