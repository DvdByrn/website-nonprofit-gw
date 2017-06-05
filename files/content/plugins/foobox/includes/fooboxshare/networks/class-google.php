<?php
if ( ! class_exists( 'FooBoxShare_Network_Google' ) ) {

	class FooBoxShare_Network_Google extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'Google', 'fooboxshare' );
        }

		function __construct() {
			$this->ua_regex   = '/(search|developers|www)\.google.com\/((\+\/web\/|webmasters\/tools\/rich)snippet(s)?|structured-data\/testing-tool)/i';
			$this->url_format = 'https://plus.google.com/share?url={share_url}';
		}

	}

}