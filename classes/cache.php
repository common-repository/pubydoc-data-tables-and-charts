<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class CachePyt {
	private $directories = array();

	public function init() {
		$this->initFilesystem();
	}
	public static function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new CachePyt();
		}
		return $instance;
	}
	public static function _() {
		return self::getInstance();
	}
	public function getDirectory( $key ) {
		return isset($this->directories[$key]) ? $this->directories[$key] : false;
	}
	protected function initFilesystem()
    {
        $directories = array(
            'tmp' => '/pyt-tables',
            'log' => '/pyt-tables/log',
            'cache' => '/pyt-tables/cache',
            'cache_tables' => '/pyt-tables/cache/tables',
        );

        foreach ($directories as $key => $dir) {
            if (false !== $fullPath = $this->makeDirectory($dir)) {
                $this->directories[$key] = $fullPath;
            }
        }
    }
    protected function makeDirectory( $directory ) {
        $uploads = wp_upload_dir();

        $basedir = $uploads['basedir'];
        $dir = $basedir . $directory;
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0775, true)) {
                return false;
            }
        } else {
            if (!is_writable($dir)) {
                return false;
            }
        }
        return $dir;
    }
    public function cleanCache( $dir, $file ) {
        $cachePath = $this->getDirectory($dir) . DS . $file;
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }
}
