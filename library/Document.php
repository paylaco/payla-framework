<?php

namespace payla\library;

class Document {

	private $title;
	private $description;
	private $links = [];
	private $styles = [];
	private $scripts = [];

	public function init(){}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getDescription() {
		return $this->description;
	}

	public function addLink($href, $rel) {
		$this->links[$href] = [
			'href' => $href,
			'rel'  => $rel
		];
	}

	public function getLinks() {
		return $this->links;
	}

	public function addStyle($href, $rel = 'stylesheet', $media = 'screen') {
		$this->styles[$href] = [
			'href'  => $href,
			'rel'   => $rel,
			'media' => $media
		];
	}

	public function getStyles() {
		return $this->styles;
	}

	public function addScript($script, $raw = false, $pos = 'top') {
		if($raw)
			$this->scripts['raw'][$pos][md5($script)] = $script;
		else
			$this->scripts['file'][$pos][md5($script)] = $script;
	}

	public function getScripts($raw = false, $pos = 'top') {
		if($raw){
			if(isset($this->scripts['raw'][$pos]))
				return $this->scripts['raw'][$pos];
		}else{
			if(isset($this->scripts['file'][$pos]))
				return $this->scripts['file'][$pos];
		}

		return [];
	}
}