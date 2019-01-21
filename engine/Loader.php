<?php

namespace payla\engine;

use Payla;

final class Loader {
	private $registry;

	public function init() {
		$this->registry = Payla::app();
	}

	public function config($config) {
		$this->registry->config->load($config);
	}

	public function language($language) {
		return $this->registry->language->load($language);
	}

	public function controller($route, $args = []) {
		$handler = Payla::app()->request->handler();
		return $handler->setAction($route, $args)->execute();
	}

	public function model($model) {
		$this->registry->{'model_' . str_replace('/', '_', $model)} = [
			'class' => "app\\models\\".str_replace('/', '\\', $model)
		];
	}

	public function view($template, $data = []) {
		$modules = Payla::app()->config->get('modules');
		$current_module = Payla::app()->request->module;
		$config_template = !empty($modules[$current_module]['defaultTemplate']) ? "/".$modules[$current_module]['defaultTemplate'] : "";

		$file = APPLICATION_PATH . $modules[$current_module]['path'] . '/view' . $config_template . $template;

		if (file_exists($file)) {
			extract($data);

			ob_start();

			require($file);

			$output = ob_get_contents();

			ob_end_clean();

			return $output;
		} else {
			trigger_error('Error: Could not load template ' . $file . '!');
			exit();
		}
	}

	// public function library($library) {
	// 	$file = DIR_SYSTEM . 'library/' . $library . '.php';

	// 	if (file_exists($file)) {
	// 		include_once($file);
	// 	} else {
	// 		trigger_error('Error: Could not load library ' . $file . '!');
	// 		exit();
	// 	}
	// }

	// public function helper($helper) {
	// 	$file = DIR_SYSTEM . 'helper/' . $helper . '.php';

	// 	if (file_exists($file)) {
	// 		include_once($file);
	// 	} else {
	// 		trigger_error('Error: Could not load helper ' . $file . '!');
	// 		exit();
	// 	}
	// }
}