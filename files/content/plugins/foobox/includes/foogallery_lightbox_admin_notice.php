<?php
/*
 * FooBox FooGallery Lightbox Admin Notice Class
 */


if (!class_exists('FooBox_FooGallery_Lightbox_Admin_Notice')) {
	class FooBox_FooGallery_Lightbox_Admin_Notice {

		const META_KEY = 'foogallery_fooboxpro_lightbox_ignore';

		function __construct() {
			add_action( 'admin_notices', array( $this, 'admin_notice_foogallery_lightboxes' ) );
			add_action( 'wp_ajax_foobox_foogallery_lightboxes_ignore_notice', array( $this, 'admin_notice_foogallery_lightboxes_ignore' ) );
			add_action( 'wp_ajax_foobox_foogallery_lightboxes_update', array( $this, 'admin_notice_foogallery_lightboxes_update' ) );
			add_action( 'admin_print_scripts', array( $this, 'admin_notice_foogallery_lightboxes_inline_js' ), 999 );
		}

		function admin_notice_foogallery_lightboxes() {
			if ( ! current_user_can( 'activate_plugins' ) || ! class_exists( 'FooGallery_Plugin' ) )
				return;

			if ( !get_user_meta( get_current_user_id(), self::META_KEY ) ) {
				$galleries = foogallery_get_all_galleries();
				$gallery_count = 0;
				foreach ( $galleries as $gallery ) {
					$template = $gallery->gallery_template;
					if ( !empty( $template ) && $gallery->has_attachments() ) {
						$lightbox = $gallery->get_meta( "{$template}_lightbox", 'no_lightbox_setting_exists' );
						if ( 'no_lightbox_setting_exists' !== $lightbox && strpos( $lightbox, 'foobox' ) === false ) {
							$gallery_count ++;
						}
					}
				}

				if ( $gallery_count > 0 ) {
					?>
					<style>
						.foobox-foogallery-lightboxes .spinner {
							float: none;
							margin: 0 10px;;
						}


					</style>
					<div class="foobox-foogallery-lightboxes notice error is-dismissible">
						<p>
							<strong><?php _e( 'FooBox PRO + FooGallery Alert : ', 'foobox-image-lightbox' ); ?></strong>
							<?php echo sprintf( _n( 'We noticed that you have 1 FooGallery that is NOT using FooBox PRO!', 'We noticed that you have %s FooGalleries that are NOT using FooBox PRO!', $gallery_count, 'foobox' ), $gallery_count ); ?>

							<a class="foobox-foogallery-update-lightbox"
							   href="#update_galleries"><?php echo _n( 'Update it to use FooBox PRO now!', 'Update them to use FooBox PRO now!', $gallery_count, 'foobox' ); ?></a>
							<span class="spinner"></span>
						</p>
					</div>
					<?php
				}
			}
		}

		function admin_notice_foogallery_lightboxes_ignore() {
			if ( check_admin_referer( 'foobox_foogallery_lightboxes_ignore_notice' ) ) {
				add_user_meta( get_current_user_id(), self::META_KEY, 'true', true );
			}
		}

		function admin_notice_foogallery_lightboxes_update() {
			if ( check_admin_referer( 'foobox_foogallery_lightboxes_update', 'foobox_foogallery_lightboxes_update_nonce' ) ) {
				//update all galleries to use foobox!
				$galleries = foogallery_get_all_galleries();
				$gallery_update_count = 0;
				foreach ( $galleries as $gallery ) {
					$template = $gallery->gallery_template;
					$meta_key = "{$template}_lightbox";
					$lightbox = $gallery->get_meta( $meta_key, 'none' );
					if ( strpos( $lightbox, 'foobox' ) === false ) {
						$gallery->settings[$meta_key] = 'foobox';
						update_post_meta( $gallery->ID, FOOGALLERY_META_SETTINGS, $gallery->settings );
						$gallery_update_count++;
					}
				}

				//return JSON here
				$json_array = array(
					'success' => true,
					'updated' => sprintf( _n( '1 FooGallery successfully updated to use FooBox PRO!', '%s FooGalleries successfully updated to use FooBox PRO!', $gallery_update_count, 'foobox' ), $gallery_update_count )
				);

				header('Content-type: application/json');
				echo json_encode($json_array);
				die;
			}
		}

		public function admin_notice_foogallery_lightboxes_inline_js() {
			if ( ! current_user_can( 'activate_plugins' ) || ! class_exists( 'FooGallery_Plugin' ) )
				return;

			if ( get_user_meta( get_current_user_id(), self::META_KEY ) )
				return;

			?>
			<script type="text/javascript">
				( function ( $ ) {
					$( document ).ready( function () {
						$( '.foobox-foogallery-lightboxes.is-dismissible' )
							.on( 'click', '.notice-dismiss', function ( e ) {
								e.preventDefault();
								$.post( ajaxurl, {
									action: 'foobox_foogallery_lightboxes_ignore_notice',
									url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
									_wpnonce: '<?php echo wp_create_nonce( 'foobox_foogallery_lightboxes_ignore_notice' ); ?>'
								} );
							} )

							.on( 'click', '.foobox-foogallery-update-lightbox', function ( e ) {
								e.preventDefault();
								var $spinner = $(this).parents('div:first').find('.spinner');
								$spinner.addClass('is-active');

								var data = 'action=foobox_foogallery_lightboxes_update' +
									'&foobox_foogallery_lightboxes_update_nonce=<?php echo wp_create_nonce( 'foobox_foogallery_lightboxes_update' ); ?>' +
									'&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

								$.ajax({
									type: "POST",
									url: ajaxurl,
									data: data,
									success: function(data) {
										$('.foobox-foogallery-lightboxes').slideUp();
										alert(data.updated);
										$spinner.removeClass('is-active');
									}
								});
							} );
					} );
				} )( jQuery );
			</script>
			<?php
		}

	}
}

