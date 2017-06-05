<?php
namespace S8\Zipper;

class Bootstrapper
{
	/**
	 * Includes plugin classes.
	 */
	public function load()
	{

		require_once __DIR__ . '/Admin/MenuPage.php';
		require_once __DIR__ . '/Archivers/Archiver.php';
		require_once __DIR__ . '/Archivers/ArchiverAjaxController.php';
		require_once __DIR__ . '/Archivers/ArchiverCleanup.php';
	}

	/**
	 * Initializes plugin classes.
	 */
	public function initialize()
	{
		// Register the "Zipper" admin submenu page under Tools.
		new Admin\MenuPage;

		// Hook ajax callbacks.
		new Archivers\ArchiverAjaxController;
	}

	public function __construct()
	{
		$this->load();
		$this->initialize();
	}
}