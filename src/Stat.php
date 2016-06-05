<?php

namespace Waxis\Stat;

class Stat extends Stat\Ancestor {

	public $type = 'stat';

	public $template = 'stat.phtml';

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
					
				case 'chart':
					$stats[] = new \Waxis\Stat\Stat\Chart($stat);
					break;
			}
		}

		return $stats;
	}

	public function getStat ($id) {
		$stats = $this->getStats();

		foreach ($stats as $stat) {
			if ($stat->getId() == $id) {
				return $stat;
			}
		}

		return false;
	}
}