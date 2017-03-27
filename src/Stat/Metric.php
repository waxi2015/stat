<?php

namespace Waxis\Stat\Stat;

class Metric extends Ancestor {

	public $type = 'metric';

	public $template = 'metric.phtml';

	public $width = [3,6,6,12];

	public $change = 30;

	public $table = null;

	public $label = null;

	public $icon = null;

	public $sum = null;

	public $convert = null;

	public $increase = null;

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
		$class = '';

		foreach ($this->getWidth() as $key => $one) {
			$class .= 'col-';

			switch ($key) {
				case 0:
					$class .= 'lg';
					break;
				case 1:
					$class .= 'md';
					break;
				case 2:
					$class .= 'sm';
					break;
				case 3:
					$class .= 'xs';
					break;
			}

			$class .= '-' . $one . ' ';
		}

		return ' class="' . $class . 'wax-stat-metric"';
	}

	public function getWidth() {
		return $this->width;
	}

	public function getTable() {
		return $this->table;
	}

	public function getSum() {
		return $this->sum;
	}

	public function getConvert() {
		return $this->convert;
	}

	public function getChange() {
		return $this->change;
	}

	public function getValue($where = null, $convert = true) {
		$query = \DB::table($this->getTable());

		if ($this->getSum() !== null) {
			$query->select(\DB::raw('SUM(' . $this->getSum() . ') AS metric'));
		} else {
			$query->select(\DB::raw('COUNT(*) AS metric'));
		}

		if ($where !== null) {
			$query->whereRaw($where);
		}

		$metric = $query->first()->metric;

		if ($convert && $this->getConvert() !== null) {
			$this->convert($metric);
		}

		return $metric;
	}

	public function convert(&$metric) {
		foreach ($this->convert as $convert) {
			if (isset($convert['operand'])) {
				switch ($convert['operand']) {
					case '/':
						$metric = round($metric / $convert['by'], $convert['round']);
						break;
				}
				
			}
		}
	}

	public function getIncrease() {
		if ($this->increase === null) {
			$current = $this->getValue(null, false);

			$where = 'created_at < "'.date('Y-m-d H:i:s', time() - 60 * 60 * 24 * $this->getChange()).'"';

			$previous = $this->getValue($where, false);

			$increase = round(($current / $previous - 1) * 100);

			$this->increase = $increase;
		}

		return $this->increase;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getIcon() {
		return $this->icon;
	}
}