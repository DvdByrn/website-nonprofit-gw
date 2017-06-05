<?php
if ( !class_exists( 'FooGallery_FooBox_Extension' ) ) {

	class FooGallery_FooBox_Extension {

		function __construct() {
			//integration with FooGallery
			add_filter( 'foogallery_gallery_template_field_lightboxes', array($this, 'add_lightbox' ) );
            add_filter( 'foogallery_attachment_custom_fields', array($this, 'add_panning_fields' ) );
            add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'add_panning_attributes' ), 10, 3 );
		}

		function add_lightbox($lightboxes) {
			$lightboxes['foobox'] = __( 'FooBox', 'foobox' );
			return $lightboxes;
		}

		function add_panning_fields( $fields ) {
            $fields['foobox_panning'] = array(
                'label'       =>  __( 'Panning', 'foogallery' ),
                'input'       => 'radio',
                'helps'       => __( 'Enable mouse panning for this image in the lightbox.', 'foogallery' ),
                'exclusions'  => array( 'audio', 'video' ),
                'options'     => array(
                    '' => __( 'Disabled', 'foogallery' ),
                    'enabled' => __( 'Enabled', 'foogallery' )
                )
            );

            return $fields;
        }

        function add_panning_attributes( $attr, $args, $foogallery_attachment ) {

            $foobox_panning = get_post_meta( $foogallery_attachment->ID, '_foobox_panning', true );

            if ( !empty( $foobox_panning ) ) {
                //add data-overflow="true" + data-proportion="false" attributes to the anchor link
                $attr['data-overflow'] = 'true';
                $attr['data-proportion'] = 'false';
            }

            return $attr;
        }
	}
}