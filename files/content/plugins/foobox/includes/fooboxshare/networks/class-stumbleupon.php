<?php
if ( ! class_exists( 'FooBoxShare_Network_StumbleUpon' ) ) {

	class FooBoxShare_Network_StumbleUpon extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'StumbleUpon', 'fooboxshare' );
        }

		function __construct() {
			$this->url_format = 'http://www.stumbleupon.com/submit?url={share_url}&title={title}';
		}

	}

}