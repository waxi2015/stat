<?php

namespace Waxis\Stat\Stat;

class Metrics extends Ancestor {

	public $type = 'metrics';

	public $template = 'metrics.blade.php';

	public $metrics = [];

	public function __construct($descriptor) {
		if (isset($descriptor['metrics'])) {
			$this->metrics = $descriptor['metrics'];
		}

		parent::__construct($descriptor);
	}

	public function getMetrics () {
		$metrics = [];
		foreach ($this->metrics as $metric) {
			$metrics[] = new \Waxis\Stat\Stat\Metric($metric);
		}

		return $metrics;
	}
}