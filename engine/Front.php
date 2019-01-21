<?php

namespace payla\engine;

use Payla;

final class Front {
	private $pre_actions = [];
	private $error;
	private $action;

	// public function addPreAction($action) {
	// 	$this->pre_actions[] = $action;
	// }

	public function setAction($route = null, $args = []){
		if(is_null($route))
			$route = Payla::app()->request->route;

		$this->action = new Action($route, $args);
		return $this->action;
	}

	public function actionName(){
		return $this->action->getMethod();
	}

	public function controllerName(){
		return $this->action->getController();
	}

	public function dispatch() {
		if(!$this->action)
			$this->setAction();

		$action = $this->action;
		foreach ($this->pre_actions as $pre_action) {
			$result = $this->execute($pre_action);

			if ($result) {
				$action = $result;
				break;
			}
		}

		while ($action) {
			$action = $this->execute($action);
		}
	}

	public function execute($action) {
		$result = $action->execute();

		if (is_object($result)) {
			$action = $result;
		} elseif ($result === false) {
			if($this->error){
				return $this->setAction($this->error);
			}else{
				http_response_code(404);
				include(PAYLA_PATH.'/errors/404.php');
				exit;
			}
		} else {
			$action = false;
		}

		return $action;
	}
}