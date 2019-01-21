<?php

namespace payla\library;

use Payla;

class Language {

	// Hold an instance of the class
    private static $instance;
	private $default = 'en';
	private $directory;
	private $data = [];

	public function init($directory = '') {
		$this->directory = Payla::app()->config->get('config_admin_language');
		if (!isset(self::$instance)) {
        	$class = __CLASS__;
            self::$instance = $this;
        }
        return self::$instance;
	}

    public static function t($key){
    	//force to generate instance
    	return Payla::app()->language->get($key);
    }

	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : $key);
	}
	
	public function load($filename) {
		$_ = [];

		$modules = Payla::app()->config->get('modules');
		$current_module = Payla::app()->request->module;

		$file = APPLICATION_PATH . $modules[$current_module]['path'] . '/language/' . $this->directory . '/' . $filename;

		if (file_exists($file.'.ini'))
			$_ = parse_ini_file($file.'.ini');
		elseif(file_exists($file.'.php'))
			require($file.'.php');
		else{
			$file = APPLICATION_PATH . $modules[$current_module]['path'] . 'language/' . $this->default . '/' . $filename;

			if (file_exists($file.'.ini'))
				$_ = parse_ini_file($file.'.ini', true);
			elseif(file_exists($file.'.php'))
				require($file.'.php');
		}

		$this->data = array_merge($_, $this->data);

		return $this->data;
	}
}