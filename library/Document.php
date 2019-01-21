<?php

namespace payla\library;

class Document {

	private $title;
	private $description;
	private $links = [];
	private $styles = [];
	private $scripts = [];
	private $rawScripts = [];

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

	public function addScript($script, $raw = false) {
		if($raw)
			$this->rawScripts[md5($script)] = $script;
		else
			$this->scripts[md5($script)] = $script;
	}

	public function getScripts($raw = false) {
		if($raw)
			return $this->rawScripts;
		else
			return $this->scripts;
	}
}