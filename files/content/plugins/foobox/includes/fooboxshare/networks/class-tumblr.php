<?php
if ( ! class_exists( 'FooBoxShare_Network_Tumblr' ) ) {

	class FooBoxShare_Network_Tumblr extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'Tumblr', 'fooboxshare' );
        }

		function __construct() {
			$this->url_format = 'https://www.tumblr.com/widgets/share/tool?canonicalUrl={share_url}&title={title}&caption={description}';
		}

	}

}