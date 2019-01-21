<?php

namespace payla\library;

use Payla;

class Url {
    private static $instance;
	private $domain;
	private $ssl;
	private $rewrite = [];

	public function init() {
        $modules = Payla::app()->config->get('modules');
        $current_module = Payla::app()->request->module;
		$domain = Payla::app()->config->get('domain')."/".str_replace("/","",$modules[$current_module]['url']);

        $this->domain = "http://".$domain;
		$this->ssl = Payla::app()->config->get('config_secure') ? 'https://'.$domain : $this->domain;

        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = $this;
        }
        return self::$instance;
	}

    private function addToken($args){
    	$token = isset($_SESSION['token']) ? $_SESSION['token'] : false;
    	if($token){
    		if(empty($args))
                $args = 'token=' . $token;
    		elseif(!strpos($args, 'token'))
    			$args = 'token=' . $token . '&' . $args;
    	}

    	return $args;
    }

	public function addRewrite($rewrite) {
		$this->rewrite[] = $rewrite;
	}

	public function link($route, $args = '', $secure = false) {
		if (!$secure) {
			$url = $this->domain;
		} else {
			$url = $this->ssl;
		}

        $url .= $route;

		if ($args) {
			$url .= str_replace('&', '&amp;', '?' . ltrim($args, '&'));
		}

		foreach ($this->rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		return $url;
	}

    public static function hlink($route, $args = '', $secure = false){
    	$args = Payla::app()->url->addToken($args);
    	echo htmlspecialchars_decode(self::$instance->link($route, $args, $secure));
    }

    public static function slink($route, $args = '', $secure = false){
    	$args = Payla::app()->url->addToken($args);
    	return self::$instance->link($route, $args, $secure);
    }
}
