<?php

namespace Waxis\Stat\Stat;

class Ancestor {

	public $type = null;

	public $templateDirectory = null;

	public $template = null;
	
	public $id = null;

	public $descriptor = null;

	public function __construct ($descriptor) {
		if (isset($descriptor['templateDirectory'])) {
			$this->templateDirectory = $descriptor['templateDirectory'];
		}

		if (isset($descriptor['template'])) {
			$this->template = $descriptor['template'];
		}

		if (isset($descriptor['id'])) {
			$this->id = $descriptor['id'];
		}

		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
	}

	public function getId () {
		return $this->id;
	}

	public function getDescriptor () {
		return $this->descriptor;
	}

	public function getType () {
		if ($this->type === null) {
			throw new Exception('Type must be defined at: ' . get_called_class(),1);
		}

		return $this->type;
	}

	public function render ($template = null) {
		echo $this->fetch($template);
	}

	public function renderJavascript ($template = null) {
		if ($template === null) {
			$template = $this->template;
		}

		$template = 'javascript/' . $template;

		echo $this->fetch($template);
	}

	public function fetch ($template = null) {

		ob_start();
		include($this->getTemplate($template));
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	# Bit messy but needed
	public function getTemplate ($template = null) {
		if ($template === null) {
			$template = $this->template;
		}

		$pathToTemplate = null;

		# ha van megadva template directory, akkor abban keressen elsősorban
		if ($this->templateDirectory !== null) {
			$paths = [
				$this->templateDirectory . '/' . $this->getType() . '/' . $template,
				$this->templateDirectory . '/' . $template,
			];
		}

		# utána nézze meg először a projekt könyvtárában, hogy van e egyedi view
		$paths[] = resource_path('views/stat/') . $this->getType() . '/' . $template;
		$paths[] = resource_path('views/stat/') . $template;

		# ha semmi nincs, akkor használja az alapértelmezettet
		$paths[] = __DIR__ . '/Template/' . $this->getType() . '/' . $template;
		$paths[] = __DIR__ . '/Template/' . $template;

		foreach ($paths as $path) {
			if (file_exists($path)) {
				$pathToTemplate = $path;
				break;
			}
		}

		# 8th - if it's not any of the places then you missed it
		if ($pathToTemplate === null) {
			throw new \Exception('Template not found: ' . $pathToTemplate,1);
			return false;
		}

		return $pathToTemplate;
	}
}