<?php

namespace Waxis\Stat\Stat;

class Metric extends Ancestor {

	public $type = 'metric';

	public $template = 'metric.blade.php';

	public $width = [3,3,6,12];

	public $change = 30;

	public $table = null;

	public $label = null;

	public $icon = null;

	public $sum = null;

	public $convert = null;

	public $value = null;

	public function __construct($descriptor) {
		if (isset($descriptor['width'])) {
			$this->width = $descriptor['width'];
		}

		if (isset($descriptor['change'])) {
			$this->change = $descriptor['change'];
		}

		if (isset($descriptor['metrics'])) {
			$this->metrics = $descriptor['metrics'];
		}

		if (isset($descriptor['table'])) {
			$this->table = $descriptor['table'];
		}

		if (isset($descriptor['label'])) {
			$this->label = $descriptor['label'];
		}

		if (isset($descriptor['icon'])) {
			$this->icon = $descriptor['icon'];
		}

		if (isset($descriptor['sum'])) {
			$this->sum = $descriptor['sum'];
		}

		if (isset($descriptor['convert'])) {
			$this->convert = $descriptor['convert'];
		}

		parent::__construct($descriptor);
	}

	public function getClass() {
		return ' class="col-lg-3 wax-stat-metric"';
	}

	public function getValue() {
		return 11;
	}

	public function getIncrease() {
		return 11;
	}

	public function getLabel() {
		return 'Startups';
	}

	public function getIcon() {
		return 'rocket';
	}
}