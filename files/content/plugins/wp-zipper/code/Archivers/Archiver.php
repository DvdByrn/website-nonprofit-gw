<?php
namespace S8\Zipper\Archivers;

/**
 * Class Archiver
 *
 * An archive wrapper.
 *
 * @package S8\Zipper\Archivers
 */
class Archiver
{
	/**
	 * @var array
	 */
	public $active_plugins = [];

	/**
	 * Archiver constructor.
	 */
	public function __construct()
	{
		$this->active_plugins = get_option( 'active_plugins' );
	}

	/**
	 * @return array An array of directory top level.
	 */
	public function get_active_plugins()
	{
		return array_values( array_map( function ( $plugin ) {
			$pos = strpos( $plugin, '/' );
			return substr( $plugin, 0, $pos );
		}, $this->active_plugins ));
	}

	public function get_all_plugins()
	{
		$files = scandir( WP_CONTENT_DIR . '/plugins' );
		$files = array_diff( $files, ['.', '..'] );
		return array_values( array_filter( $files, function( $file ) {
			return false === strpos( $file, '.php' );
		}));
	}

	public function get_inactive_plugins()
	{
		$active = $this->get_active_plugins();
		$all = $this->get_all_plugins();
		return array_diff( $all, $active );
	}

	/**
	 * @param $bytes
	 * @param int $decimals
	 *
	 * @return string
	 */
	public function get_human_filesize( $bytes, $decimals = 2 ) {
		$size   = [ 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
		$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );

		return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . @$size[ $factor ];
	}

	/**
	 * Test path for exclusion.
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	public function should_exclude( $path )
	{
		$should_exclude = false;

		$exclusions = [
			'/.idea/',
		    '/.git/',
		    '/node_modules/',
		    '/bower_components/'
		];

		foreach( $exclusions as $exclusion )
		{
			if ( $should_exclude )
			{
				continue;
			}
			if ( false !== strpos( $path, $exclusion ) )
			{
				$should_exclude = true;
			}
		}

		return $should_exclude;
	}

	public function _get_file_list( $path, &$bytestotal, &$nbfiles, &$directories, &$files )
	{
		$ite = new \RecursiveDirectoryIterator( $path );

		// Loop through files.
		foreach ( new \RecursiveIteratorIterator( $ite ) as $filename => $cur ) {
			/** @var $cur \SplFileInfo */

			if ( $this->should_exclude( $filename ) ) {
				continue;
			}

			if ( $cur->isDir() ) {
				// If the last 2 characters are '/.', replace with empty string.
				if ( '/..' === substr( $filename, - 3 ) ) {
					$filename = str_replace( '/..', '/', $filename );
				}

				if ( '/.' === substr( $filename, - 2 ) ) {
					$filename = str_replace( '/.', '/', $filename );
				}

				if ( ! in_array( $filename, $directories ) ) {
					$directories[] = $filename;
					$files[]       = [
						'path' => $filename,
						'size' => 0
					];
				}
			}

			if ( $cur->isFile() ) {
				$filesize   = $cur->getSize();
				$files[]    = [
					'path' => $filename,
					'size' => $this->get_human_filesize( $filesize )
				];
				$bytestotal += $filesize;
				$nbfiles ++;
			}
		}
	}

	/**
	 * @param array $paths
	 *
	 * @return array
	 */
	public function get_files_collection( $paths = [] )
	{
		$bytestotal  = 0;
		$nbfiles     = 0;
		$directories = [];
		$files       = [];

		foreach ( $paths as $path ) {
			$this->_get_file_list( $path, $bytestotal, $nbfiles, $directories, $files );
		}

		return [
			'total_bytes' => $bytestotal,
			'total_files' => $nbfiles,
			'total_dir'   => count( $directories ),
			'size'        => $this->get_human_filesize( $bytestotal ),
			'ram_used'    => $this->get_human_filesize( memory_get_usage() ),
			'files'       => $files,
			'directories' => $directories,
		];
	}

	/**
	 * Return all files below ABSPATH
	 * @param string $path
	 * @return array
	 */
	public function get_file_manifest( $paths = [ABSPATH] )
	{
		if ( is_string( $paths ) ) {
			$paths = [$paths];
		}

		if ( defined( 'WP_MULTI_TENANT' ) ) {
			return $this->_get_multi_tenant_installation_manifest();
		}

		// Return the standard installation file manifest.
		return $this->_get_standard_installation_manifest();
	}

	protected function _get_multi_tenant_installation_manifest()
	{
		$paths = [
			ABSPATH,
			str_replace( '/wp/../', '/', ABSPATH . UPLOADS ),
		];

		foreach( $this->get_active_plugins() as $plugin ) {
			$paths[] = WP_CONTENT_DIR . '/plugins/' . $plugin;
		}

		// Get parent and child theme directories.
		if ( is_child_theme() ) {
			$paths[] = get_template_directory();
			$paths[] = get_stylesheet_directory();
		} else {
			$paths[] = get_template_directory();
		}

		return $this->get_files_collection( $paths );
	}

	protected function _get_standard_installation_manifest()
	{
		return $this->get_files_collection( [ABSPATH] );
	}

	public function get_temp_dir()
	{
		$path = $this->get_storage_path() . '/tmp';

		if ( ! is_dir( $path ) ) {
			mkdir( $path );
		}

		return $path . '/';
	}

	/**
	 * @return string path to uploads directory.
	 */
	public function get_storage_path()
	{

		$path = wp_upload_dir()['basedir'] . '/';

		if ( ! is_dir( $path . '/s8-zipper' ) ) {
			mkdir( $path . '/s8-zipper' );
		}

		return $path . '/s8-zipper/';
	}

	public function get_storage_url()
	{
		$this->get_storage_path();
		$url = $path = wp_upload_dir()['baseurl'] . '/s8-zipper/';
		return $url;
	}

	/**
	 * @param $filename
	 * @return string Path to zip file.
	 */
	public function create_zip_file( $filename )
	{
		$storage_path = $this->get_storage_path();
		$zip_path = $storage_path . $filename;

		$archive = new \ZipArchive();
		$archive->open( $zip_path, \ZipArchive::CREATE );
		//$archive->addEmptyDir( 'files' );
		$archive->addEmptyDir( 'database' );
		$archive->close();

		return $zip_path;
	}

	public function correct_path( $path )
	{
		// Regular WordPress install
		if ( ! defined( 'WP_MULTI_TENANT' ) )
		{
			$base = ABSPATH;
		} else {
			$base = str_replace( '/wp/', '/', ABSPATH  );
		}

		$path = str_replace( $base, '../', '/' . $path );
		$path = str_replace( '/../', '', $path );
		$path = 'files/' . $path;

		if ( defined( 'WP_MULTI_TENANT' ) )
		{
			$sitename = SITENAME;
			$path = str_replace( 'files/sites/' . $sitename . '/uploads', 'files/content/uploads', $path );
		}

		return $path;
	}

	protected $log_entries = [];

	public function log( $string )
	{
		$this->log_entries[] = $string;
	}

	/**
	 * @param $filename
	 * @param array $paths
	 *
	 * @return string
	 */
	public function add_zip_files( $filename, $paths = [] )
	{
		$storage_path = $this->get_storage_path();
		$zip_path     = $storage_path . $filename;

		$paths = array_filter( $paths, function( $path ) {
			return ! empty( $path );
		});

		$archive = new \ZipArchive();
		$archive->open( $zip_path, \ZipArchive::CREATE );

		foreach ( $paths as $path ) {

			if ( is_file( $path ) ) {
				$new_path = $this->correct_path( $path );
				if ( $archive->addFile( $path, $new_path ) ) {
					$this->log( "added file:: {$new_path}" );
				} else {
					$this->log( "failed to add file {$new_path}" );
				}
			}
		}

		$archive->close();

		return $zip_path;
	}

	public function purge_temp()
	{
		$files = scandir( $this->get_temp_dir() );
		$files = array_diff( $files, ['.', '..'] );
		foreach( $files as $file ) {
			//unlink( $this->get_temp_dir() . $file );
		}
	}

	public function add_zip_string( $zip_filename, $file_path, $data )
	{
		$storage_path = $this->get_storage_path();
		$zip_path     = $storage_path . $zip_filename;

		$archive = new \ZipArchive();
		$archive->open( $zip_path, \ZipArchive::CREATE );
		$archive->addFromString( $file_path, $data );
		$archive->close();

		return $zip_path;
	}

	public function get_archives()
	{
		$storage_path = $this->get_storage_path();
		$files = scandir( $storage_path );
		$files = array_diff( $files, ['.', '..'] );

		$files = array_filter( $files, function( $file ) use ( $storage_path ) {
			return ! is_dir( $storage_path . $file );
		});

		return array_map( function( $file ) use( $storage_path ) {
			return [
				'path' => $storage_path . $file,
				'url' => $this->get_storage_url() . $file,
			    'filename' => $file,
			    'date' => filemtime( $storage_path . $file ),
			    'size' => $this->get_human_filesize( filesize( $storage_path . $file ) )
			];
		}, $files );
	}

	public function add_stubs( $zip_filename )
	{
		$keys = [
			'DB_NAME',
		    'DB_USER',
		    //'DB_PASSWORD',
		    'DB_HOST',
		    'DB_CHARSET',
		    'DB_COLLATE',
		    'APP_DOMAIN',
		    'AUTH_KEY',
		    'SECURE_AUTH_KEY',
		    'LOGGED_IN_KEY',
		    'NONCE_KEY',
		    'AUTH_SALT',
		    'SECURE_AUTH_SALT',
		    'LOGGED_IN_SALT',
		    'NONCE_SALT'
		];

		$vars = [];

		foreach ( $keys as $key ) {
			if ( defined( $key ) ) {
				$vars[ $key ] = constant( $key );
			} else {
				$vars[ $key ] = '';
			}
		}

		$vars['TABLE_PREFIX'] = $GLOBALS['table_prefix'];
		$vars['APP_DOMAIN'] = home_url();
		$vars['DB_PASSWORD'] = '';

		$index_stub = file_get_contents( S8_ZIPPER_DIR . 'stubs/index.php.stub' );
		$config_stub = file_get_contents( S8_ZIPPER_DIR . 'stubs/wp-config.php.stub' );
		foreach( $vars as $key => $value ) {
			$config_stub = str_replace( '{' . $key . '}', $value, $config_stub );
		}

		$storage_path = $this->get_storage_path();
		$zip_path     = $storage_path . $zip_filename;

		$archive = new \ZipArchive();
		$archive->open( $zip_path, \ZipArchive::CREATE );
		$archive->addFromString( 'files/index.php', $index_stub );
		$archive->addFromString( 'files/wp-config.php', $config_stub );
		$archive->close();
	}

	public function __destruct()
	{
		// $log_path = $this->get_storage_path() . '/log.txt';
		// file_put_contents( $log_path, implode( "\n", $this->log_entries ) . "\n\n\n\n", FILE_APPEND );
		// $this->purge_temp();
	}
}