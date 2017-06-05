<?php
if ( ! class_exists( 'FooBoxShare_Network_Buffer' ) ) {

	class FooBoxShare_Network_Buffer extends FooBoxShare_Network_Base {

	    function Name() {
	        return __( 'Buffer', 'fooboxshare' );
        }

		/**
		 * FooBoxShare_Network_Buffer constructor.
		 */
		function __construct() {
			$this->url_format = 'http://bufferapp.com/add?text={title}&url={share_url}';
		}
	}
}