<?php

namespace payla\engine;

use Payla;

final class Action {

	private $file;
	private $class;
	private $controller;
	private $method;
	private $args = [];

	public function __construct($route, $args = []) {
		$modules = Payla::app()->config->get('modules');
		$module = Payla::app()->request->module;

		// Break apart the route
		$parts = explode('/', str_replace('../', '', (string)$route));
		$path= '';
		foreach ($parts as $part) {
			$path .= $part;

			if (is_dir(APPLICATION_PATH . $modules[$module]['path']. '/controller/' . $path)) {
				$path .= '/';
				array_shift($parts);
				continue;
			}

			$file = APPLICATION_PATH .$modules[$module]['path'].'/controller/'. str_replace(['../','..\\','..'], '', $path) . '.php';

			if (is_file($file)) {
				$this->file = $file;
				$this->class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', $path);
				$this->controller = $part;
				array_shift($parts);
				break;
			}
		}

		if ($args) {
			$this->args = $args;
		}

		$method = array_shift($parts);

		if ($method) {
			$this->method = $method;
		} else {
			$this->method = 'index';
		}
	}

	public function getMethod(){
		return $this->method;
	}

	public function getController(){
		return $this->controller;
	}

	public function execute() {
		// Stop any magical methods being called
		if (substr($this->method, 0, 2) == '__') {
			return false;
		}

		if (is_file($this->file)) {
			include_once($this->file);

			$class = $this->class;

			$controller = new $class();

			if (is_callable([$controller, $this->method])) {
				return call_user_func([$controller, $this->method], $this->args);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}