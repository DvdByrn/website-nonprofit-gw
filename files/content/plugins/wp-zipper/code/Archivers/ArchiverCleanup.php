<?php
namespace S8\Zipper\Archivers;

class ArchiverCleanup
{
	public function __construct()
	{
		if ( ! wp_next_scheduled( 's8_zipper_perform_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 's8_zipper_perform_cleanup' );
		}

		add_action( 's8_zipper_perform_cleanup', [$this, 'perform_cleanup'] );
	}

	public function perform_cleanup()
	{
		$archive = new Archiver();
		$archive->purge_temp();

		$files = scandir( $archive->get_storage_path() );
		$files = array_diff( $files, ['.', '..'] );

		foreach( $files as $file ) {
			$file_path = $archive->get_storage_path() . $file;
			if ( time() - filemtime( $file_path) > (60*60*24) ) {
				unlink( $file_path );
			}
		}
	}
}