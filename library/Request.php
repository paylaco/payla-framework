<?php

namespace payla\library;

use Payla;
use payla\engine\Front;

class Request {

	private $frontController;

	public $get = [];
	public $post = [];
	public $cookie = [];
	public $files = [];
	public $server = [];
	
	public $module = 'default';
	public $route;

	public function init() {
		$this->checkServer();

		$this->get = $this->clean($_GET);
		$this->post = $this->clean($_POST);
		$this->request = $this->clean($_REQUEST);
		$this->cookie = $this->clean($_COOKIE);
		$this->files = $this->clean($_FILES);
		$this->server = $this->clean($_SERVER);
	}

	public function resolveUri(){
		$this->route = $this->CurrentUri();

		$modules = Payla::app()->config->get('modules');
		if(empty($modules)){
			$modules = [
				'default' => [
		            'url' => '/',
		            'path' => 'default',
		            'defaultRoute' => 'index/index',
		            'defaultTemplate' => ''
		        ]
			];
			Payla::app()->config->set('modules', $modules);
		}

		foreach ($modules as $module => $config) {
			if(strpos($this->route."/", $config['url']) === 0){
				$this->module = $module;
				$this->route = substr($this->route, strlen($config['url']));
				break;
			}
		}


		if(!$this->route && !isset($modules[$this->module]['defaultRoute']))
			throw new \Exception("define default route for module '{$this->module}'", 1);
		elseif(!$this->route)
			$this->route = $modules[$this->module]['defaultRoute'];
	}

	public function handler(){
		if(!$this->frontController)
			$this->frontController = new Front();

		return $this->frontController;
	}
    
    public function CurrentUri()
    {
        $basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        $uri = substr($_SERVER['REQUEST_URI'], strlen($basepath));
        if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));
        $uri = trim($uri, '/');
        return $uri;
    }

    public function BaseUrl($complete = false){
    	$modules = Payla::app()->config->get('modules');
    	$BaseUrl = $modules[$this->module]['url'];

    	if(!$complete)
    		return $BaseUrl;
    }

    public function isEmpty(){
    	$uri = $this->CurrentUri();

    	if(empty($uri) || $uri == '/')
    		return true;
    	
    	return false;
    }

	public function clean($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				unset($data[$key]);

				$data[$this->clean($key)] = $this->clean($value);
			}
		} else {
			$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
		}

		return $data;
	}

	public function isPost(){
		return ($this->server['REQUEST_METHOD'] == 'POST');
	}

	public function getIP(){
		return $this->server['REMOTE_ADDR'];
	}

	private function checkServer(){
		if (!ini_get('date.timezone')) {
		    date_default_timezone_set('UTC');
		}

		// Windows IIS Compatibility
		if (!isset($_SERVER['DOCUMENT_ROOT'])) {
		    if (isset($_SERVER['PATH_TRANSLATED'])) {
		        $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
		    }elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
		        $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
		    }
		}

		if (!isset($_SERVER['REQUEST_URI'])) {
		    $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

		    if (isset($_SERVER['QUERY_STRING'])) {
		        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
		    }
		}

		if (!isset($_SERVER['HTTP_HOST'])) {
		    $_SERVER['HTTP_HOST'] = getenv('HTTP_HOST');
		}

		// Check if SSL
		if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
		    $_SERVER['HTTPS'] = true;
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
		    $_SERVER['HTTPS'] = true;
		} else {
		    $_SERVER['HTTPS'] = false;
		}
	}
}