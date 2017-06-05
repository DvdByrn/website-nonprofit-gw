<?php
if ( ! class_exists( 'FooBoxShare_Network_Digg' ) ) {

	class FooBoxShare_Network_Digg extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'Digg', 'fooboxshare' );
        }

		function __construct() {
			$this->url_format = 'http://digg.com/submit?url={share_url}&title={title}';
		}

	}

}