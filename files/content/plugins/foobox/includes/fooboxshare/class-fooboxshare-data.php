<?php
/**
 * FooBoxShare Data Structure
 * Date: 15/11/2016
 */
if ( ! class_exists( 'FooBoxShare_Data_v1' ) ) {

	class FooBoxShare_Data_v1 extends stdClass {

		/**
		 * FooBoxShareData constructor.
		 *
		 * @param array $data
		 */
		function __construct( $data = null ) {
			$this->exists = false;
			$this->invalid = false;
			$this->hash = $this->title = $this->description = $this->content_type = $this->content_url = $this->url = '';

			if ( isset( $data ) ) {
				$this->exists = true;
				$this->url = $data['url'];
				$this->hash = $data['hash'];
				$this->content_url = $data['content_url'];
				$this->content_type = $data['content_type'];
				$this->title = $data['title'];
				$this->description = $data['description'];
				$this->redirect_url = $this->url . ( !empty( $this->hash ) ? $this->hash : '' );
				if ( array_key_exists( 'thumb_url', $data ) ) {
					$this->thumb_url = $data['thumb_url'];
				}
				if ( array_key_exists( 'image_width', $data ) ) {
					$this->image_width = $data['image_width'];
				}
				if ( array_key_exists( 'image_height', $data ) ) {
					$this->image_height = $data['image_height'];
				}
			}
		}
	}
}