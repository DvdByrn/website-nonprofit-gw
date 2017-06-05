<?php
namespace S8\Zipper\Archivers;

use Ifsnop\Mysqldump\Mysqldump;

class ArchiverAjaxController
{
	public function __construct()
	{
		$prefix = 's8_zipper';

		add_action( "wp_ajax_{$prefix}_clean_up", [$this, 'clean_up'] );
		add_action( "wp_ajax_{$prefix}_get_file_manifest", [$this, 'get_file_manifest'] );
		add_action( "wp_ajax_{$prefix}_get_database_manifest", [$this, 'get_database_manifest'] );
		add_action( "wp_ajax_{$prefix}_get_working_archive", [$this, 'get_working_archive'] );
		add_action( "wp_ajax_{$prefix}_process_files", [$this, 'process_files'] );
		add_action( "wp_ajax_{$prefix}_process_table", [$this, 'process_table'] );
		add_action( "wp_ajax_{$prefix}_concatenate_sql", [$this, 'concatenate_sql'] );
	}

	public function clean_up()
	{
		$archive = new Archiver();
		//$archive->purge_temp();
	}

	public function get_file_manifest()
	{
		$archive = new Archiver();
		//wp_send_json( $archive->get_file_manifest( S8_ZIPPER_DIR . 'tests') );
		wp_send_json( $archive->get_file_manifest() );
	}

	public function get_database_manifest()
	{
		global $wpdb;
		$db_name = DB_NAME;
		$sql = "SHOW TABLES IN `{$db_name}`";
		$tables = [];

		foreach( $wpdb->get_results( $sql ) as $result )
		{
			$result = (array) $result;
			$tables[] = array_shift($result);
		}

		wp_send_json( $tables );
	}

	/**
	 * Create an empty zip archive for this backup.
	 */
	public function get_working_archive()
	{
		$time = current_time( 'mysql' );
		$time = str_replace( ':', '-', $time );

		$filename = "backup-{$time}.zip";

		$archive = new Archiver();
		$archive->create_zip_file( $filename );

		wp_send_json( $filename );
	}

	public function process_files()
	{
		$data = json_decode( file_get_contents( 'php://input' ), true );
		$files = $data['files'];
		$filename = $data['filename'];
		$archive = new Archiver();
		$archive->add_zip_files( $filename, $files );

		wp_send_json( $filename );
	}

	public function process_table()
	{
		$data     = json_decode( file_get_contents( 'php://input' ), true );
		$table    = $data['table'];
		$filename = $data['filename'];
		$archive  = new Archiver();

		$table_filename = $table . '.sql';
		$zip_table_filename = 'database/' . $table_filename;

		$db_host = DB_HOST;
		$db_user = DB_USER;
		$db_pass = DB_PASSWORD;
		$db_name = DB_NAME;

		$storage_path = $archive->get_temp_dir();
		$dump_path = $storage_path . $table_filename;

		try {
			$dump_settings = [
				'include-tables' => [$table],
			    'add-drop-table' => true
			];
			$dump = new Mysqldump(
				"mysql:host={$db_host};dbname={$db_name}",
				$db_user,
				$db_pass,
				$dump_settings
			);
			$dump->start( $dump_path );
		}
		catch ( \Exception $e ) {
			echo 'mysqldump-php error: ' . $e->getMessage();
		}

		// $archive->add_zip_string( $filename, $zip_table_filename, file_get_contents( $dump_path ) );

		// Update branding.
		$search = '-- mysqldump-php https://github.com/ifsnop/mysqldump-php';
		$replace =  "-- S8-Zipper MySQL Dump\n";
		$replace .= "-- http://sideways8.com\n";
		$contents = file_get_contents( $dump_path );
		$contents = str_replace( $search, $replace, $contents );
		file_put_contents( $dump_path, $contents );
		
		wp_send_json( $filename );
	}

	public function concatenate_sql()
	{
		$archive = new Archiver();
		$temp_dir = $archive->get_temp_dir();
		$files = scandir( $temp_dir );
		$files = array_diff( $files, ['.', '..'] );
		ob_start();
		foreach( $files as $file ) {
			$path = $temp_dir . $file;
			include_once $path;
			echo "\n\n\n";
		}

		$data = json_decode( file_get_contents( 'php://input' ), true );
		$filename = $data['filename'];
		$zip_table_filename = 'database/backup.sql';
		$archive->add_zip_string( $filename, $zip_table_filename, ob_get_clean() );

		if ( defined( 'WP_MULTI_TENANCY' ) ) {
			$archive->add_stubs( $filename );
		}

		//$archive->purge_temp();
		//rmdir( $archive->get_temp_dir() );
	}
}