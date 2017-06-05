<?php
/**
 * Base Network Class
 * Date: 30/11/2016
 */
if ( ! class_exists( 'FooBoxShare_Network_Base' ) ) {
	class FooBoxShare_Network_Base extends stdClass {

		public $ua_regex = '';
		public $url_format = '';

        public function Name() {
            return '';
        }

		public function has_crawler(){
			return !empty( $this->ua_regex );
		}

		/**
		 * @param $share FooBoxShare_Data_v1
		 *
		 * @return string
		 */
		public function get_url( $share ){
			$url = $this->url_format;

			foreach($share as $key => $val){
				$key = sprintf('{%s}', $key);
				$url = str_replace($key, urlencode($val), $url);
			}
			return $url;
		}

		/**
		 * @param FooBoxShare_Data_v1 $share
		 *
		 * @return mixed
		 */
		public function add_meta( $meta_tags, $share ) {
			return $meta_tags;
		}

		/**
		 * @return bool
		 */
		public function supports_video() {
			return true;
		}

		/**
		 * @return string
		 */
		public function preferred_image_size() {
			return 'full';
		}
	}
}