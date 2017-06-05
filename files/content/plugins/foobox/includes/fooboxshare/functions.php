<?php


if ( !function_exists( 'fooboxshare_sharing_param' ) ) {
	/**
	 * Returns the sharing param that is used when sharing a URL
	 *
	 * @return string
	 */
	function fooboxshare_sharing_param() {
		return apply_filters( FOOBOXSHARE_FILTER_SHARING_PARAM, FOOBOXSHARE_PARAM_EXTERNAL );
	}
}

if ( !function_exists( 'fooboxshare_get_setting' ) ) {
	/**
	 * Get a setting used in fooboxshare
	 *
	 * @param string $key
	 * @param bool   $default
	 * @param bool   $check_for_empty
	 *
	 * @return mixed
	 */
	function fooboxshare_get_setting( $key, $default = false, $check_for_empty = false ) {
		$foobox = $GLOBALS['foobox'];
		$value = $foobox->get_option( $key, $default );

		if ( $check_for_empty && empty( $value ) ) {
			return $default;
		}

		return $value;
	}
}

if ( !function_exists( 'fooboxshare_network_defaults' ) ) {
	/**
	 * Returns the default network options used in every network
	 *
	 * @return array
	 */
	function fooboxshare_network_defaults() {
		return apply_filters(
			'fooboxshare_network_defaults', array(
			'meta_tags'  => array(),
			'ua_regex'   => null,
			'url_format' => null
		)
		);
	}
}

if ( !function_exists( 'fooboxshare_available_networks' ) ) {
	/**
	 * Returns the available social networks
	 *
	 * @return FooBoxShare_Network_Base[]
	 */
	function fooboxshare_available_networks() {
		$networks = array();

		$networks['buffer']       = new FooBoxShare_Network_Buffer();
		$networks['delicious']    = new FooBoxShare_Network_Delicious();
		$networks['digg']         = new FooBoxShare_Network_Digg();
		$networks['facebook']     = new FooBoxShare_Network_Facebook();
		$networks['google-plus']  = new FooBoxShare_Network_Google();
		$networks['linkedin']     = new FooBoxShare_Network_Linkedin();
		$networks['pinterest']    = new FooBoxShare_Network_Pinterest();
		$networks['reddit']       = new FooBoxShare_Network_Reddit();
		$networks['stumble-upon'] = new FooBoxShare_Network_StumbleUpon();
		$networks['tumblr']       = new FooBoxShare_Network_Tumblr();
		$networks['twitter']      = new FooBoxShare_Network_Twitter();

		return apply_filters( 'fooboxshare_available_networks', $networks );
	}
}

if ( !function_exists( 'fooboxshare_facebook_default_app_id' ) ) {
	/**
	 * Returns the default Facebook app ID that will be used in sharing
	 *
	 * @return array
	 */
	function fooboxshare_facebook_default_app_id() {
		return apply_filters( 'fooboxshare_facebook_default_app_id', '966242223397117' );
	}
}

if ( !function_exists( 'fooboxshare_network_enabled' ) ) {
	function fooboxshare_network_enabled( $network ) {
		return apply_filters( 'fooboxshare_network_enabled', true, $network );
	}
}