<?php
if ( ! class_exists( 'FooBoxShare_Network_Linkedin' ) ) {

	class FooBoxShare_Network_Linkedin extends FooBoxShare_Network_Base {

        function Name() {
            return __( 'LinkedIn', 'fooboxshare' );
        }

		function __construct() {
			$this->ua_regex = '/LinkedInBot/i';
			$this->url_format = 'https://linkedin.com/shareArticle?mini=true&url={share_url}&title={title}&summary={description}';
		}

	}

}