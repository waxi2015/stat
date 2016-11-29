<?php

namespace Waxis\Stat\Stat;

class SimpleBars extends Ancestor {

	public $type = 'simple-bars';

	public $template = 'simple-bars.phtml';

	public $height = 220;
	
	public $days = 7;

	public $sources = null;

	public function __construct($descriptor) {
		if (isset($descriptor['height'])) {
			$this->height = $descriptor['height'];
		}

		if (isset($descriptor['days'])) {
			$this->days = $descriptor['days'];
		}

		if (isset($descriptor['sources'])) {
			$this->sources = $descriptor['sources'];
		}

		parent::__construct($descriptor);
	}

	public function getDays () {
		return $this->days - 1;
	}

	public function getSources () {
		return $this->sources;
	}

	public function getHeight () {
		return $this->height;
	}

	public function getColors () {
		$colors = [];
		foreach ($this->getSources() as $one) {
			$colors[] = $one['color'];
		}

		return $colors;
	}

	public function getData () {
		$startDate = date('Y-m-d H:i:s' , strtotime(date('Y-m-d') . ' 00:00:00 -' . $this->getDays() . ' DAYS'));
		$endDate = date('Y-m-d') . ' 23:59:59';

		$allSourceData = [];
		foreach ($this->getSources() as $source) {
			$allSourceData[] = $this->getSourceData($source, $startDate, $endDate);
		}

		$data = [
			[
				'Date'
			]
		];

		foreach ($this->getSources() as $one) {
			$data[0][] = $one['label'];
		}

		$k = 1;
		for ($i = $this->getDays(); $i >= 0; $i--) {
			$date = date('Y-m-d' , strtotime(date('Y-m-d') . ' -' . $i . ' DAYS'));
			
			$data[$k][] = date('M d', strtotime($date));

			foreach ($allSourceData as $one) {
				if (array_key_exists($date, $one)) {
					$data[$k][] = (int) $one[$date];
				} else {
					$data[$k][] = 0;
				}
			}

			$k++;
		}

		return $data;
	}

	public function getSourceData ($source, $startDate, $endDate) {
		$query = \DB::table($source['table'])
					->where('created_at', '>=', $startDate)
					->where('created_at', '<=', $endDate)
					->groupBy(\DB::raw('DATE(created_at)'));

		if (isset($source['where'])) {
			foreach ($source['where'] as $one) {
				$query->whereRaw($one);
			}
		}

		$query->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(*) AS count'));

		$data = to_array($query->get());
		$temp = [];
		foreach ($data as $one) {
			$temp[$one['date']] = $one['count'];
		}
		$data = $temp;

		return $data;
	}
}