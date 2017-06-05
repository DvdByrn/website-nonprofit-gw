<?php
namespace S8\Zipper\Admin;

use S8\Zipper\Archivers\Archiver;

class MenuPage
{
	public function __construct()
	{
		add_action( 'admin_menu', [$this, 'register_submenu_page'] );
	}

	/**
	 * The tabs
	 * @var array
	 */
	protected $tabs = [
		'main' => 'Create',
		'backups' => 'Download',
		//'settings' => 'Settings'
	];

	/**
	 * @return array
	 */
	public function get_tabs()
	{
		return $this->tabs;
	}

	/**
	 * Register the 'Zipper' under the Tools admin menu.
	 */
	public function register_submenu_page()
	{
		add_submenu_page(
			'tools.php',
			'Zipper',
			'Zipper',
			'manage_options',
			'zipper',
			[$this, 'render_html']
		);
	}

	/**
	 * Prints the admin page URL.
	 */
	public function render_html()
    {
	    wp_enqueue_script( 'underscore' );

	    // Enqueue vuejs app build.
	    wp_enqueue_script( 's8-zipper-vue', S8_ZIPPER_URL . 'assets/js/s8-zipper.js' );

	    // Font awesome.
	    wp_enqueue_style( 's8-zipper', S8_ZIPPER_URL . 'assets/css/s8-zipper.css' );
	    wp_enqueue_style( 'font-awesome', S8_ZIPPER_URL . 'assets/css/font-awesome.min.css' );

	    $this->process_forms();

		require_once S8_ZIPPER_DIR . 'views/admin/zipper-admin.php';
	}

	/**
	 * @return mixed
	 */
	private function get_first_tab_key()
	{
		return array_keys( $this->tabs )[0];
	}

	/**
	 * @return string
	 */
	public function get_current_tab()
	{
		// Get the current tab, or default to the first.
		if ( isset( $_GET['tab'] ) ) {
			$tab = filter_var( $_GET['tab'], FILTER_SANITIZE_STRING );
		} else {
			$tab = $this->get_first_tab_key();
		}

		return $tab;
	}

	public function get_url_root()
	{
		return admin_url() . 'tools.php?page=zipper';
	}

	/**
	 * @return string
	 */
	public function render_tab_html()
	{
		$tabs = $this->get_tabs();
		$url_root = $this->get_url_root();

		$output =  '<div id="icon-themes" class="icon32"><br></div>';
		$output .= '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name )
		{
			$class = ( $tab == $this->get_current_tab() ) ? ' nav-tab-active' : '';
			$output .= "<a class='nav-tab{$class}' href='{$url_root}&tab={$tab}'>{$name}</a>";
		}
		$output .= '</h2>';

		return $output;
	}

	/**
	 * @return string
	 */
	public function render_tab_content_html()
	{
		$path = S8_ZIPPER_DIR . 'views/admin/tabs/' . $this->get_current_tab() . '.php';

		// Verify that the file exists.
		if ( ! file_exists( $path ) )
		{
			?>
			<p>Invalid admin tab specified...</p>
			<?php
			return;
		}

		ob_start();
		require_once $path;
		return ob_get_clean();
	}

	protected $feedback_messages = [];

	public function process_forms()
    {
        switch( $this->get_current_tab() )
        {
            case 'backups':

                if ( isset( $_GET['file'] ) )
                {
                    $archive = new Archiver();
                    $file = $archive->get_storage_path() . $_GET['file'];
                    if ( is_file( $file ) ) {
                        unlink( $file );
                        $this->feedback_messages[] = "File deleted: " . $_GET['file'];
                    }
                }

                break;
        }
    }
}