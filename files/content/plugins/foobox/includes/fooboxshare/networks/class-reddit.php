<?php
if ( ! class_exists( 'FooBoxShare_Network_Reddit' ) ) {

	class FooBoxShare_Network_Reddit extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'Reddit', 'fooboxshare' );
        }

		function __construct() {
			$this->url_format =  'http://reddit.com/submit?url={share_url}&title={title}';
		}

	}
}