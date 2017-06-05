<?php
if ( ! class_exists( 'FooBoxShare_Network_Delicious' ) ) {

	class FooBoxShare_Network_Delicious extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'Delicious', 'fooboxshare' );
        }

		function __construct() {
			$this->url_format = 'https://delicious.com/save?v=5&provider={provider}&noui&jump=close&url={share_url}&title={title}';
			$this->ua_regex = '/Java\/1\.8\.0_40/i';
		}

		public function get_url( $share ) {
			$share->provider = ''; //Not sure if this is needed
			return parent::get_url( $share );
		}

	}

}
