<?php

namespace payla\engine;

class Controller extends Component {
	
	public $layout = 'main';

	protected function beforeRender($view)
	{
		return true;
	}

	protected function afterRender($view, &$output)
	{
	}

	public function renderJson($response = []){
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function renderPartial($view,$data=[],$return=true)
	{
		$output = $this->load->view($this->config->get('config_template')."/".$view,$data);
		if($return)
			return $output;
		else
			echo $output;
	}

	public function render($view,$data=[])
	{
		if($this->beforeRender($view))
		{
			$output = $this->load->view($this->config->get('config_template')."/".$view,$data);
			if(($layoutData=$this->getLayoutData($this->layout))!==false)
				$output = $this->load->view($this->config->get('config_template')."/"."layout/".$this->layout.".tpl",array_merge($layoutData, ['content'=>$output]));
			$this->afterRender($view,$output);
			
			$this->response->setOutput($output);
		}
	}

	public function getLayoutData($layoutName)
	{
		$this->load->language('layout/'.$layoutName);

		$data['title'] = $this->document->getTitle();
			
		$layout_data = $this->load->controller('layout/'.$layoutName);

		$data['description'] = $this->document->getDescription();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['rawScripts'] = $this->document->getScripts('raw');
		$data['lang'] = $this->config->get('lang');
		$data['direction'] = $this->config->get('direction');
		$data['base'] = $this->config->get('domain');

		if(is_array($layout_data))
			$data = array_merge($data, $layout_data);

		return $data;
	}

}