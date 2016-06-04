<?php

namespace Waxis\Stat;

class Stat extends Stat\Ancestor {

	public $type = 'stat';

	public $template = 'stat.blade.php';

	public function __construct ($descriptor) {
		if (is_string($descriptor)) {
			$descriptorClass = '\App\Descriptors\Stat\\' . ucfirst($descriptor);
			$descriptorObj = new $descriptorClass();

			$descriptor = $descriptorObj->descriptor();
		}

		parent::__construct($descriptor);
	}

	public function getStats () {
		$stats = [];
		foreach ($this->getDescriptor() as $stat) {
			switch ($stat['type']) {
				case 'metrics':
					$stats[] = new \Waxis\Stat\Stat\Metrics($stat);
					break;
			}
		}

		return $stats;
	}
}