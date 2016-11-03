<?php

namespace Waxis\Stat\Stat;

class Chart extends Ancestor {

	public $type = 'chart';

	public $template = 'chart.phtml';

	public $year = null;

	public $month = null;

	public $sources = null;

	public $source = null;

	public $period = 'month';

	public function __construct($descriptor) {
		if (isset($descriptor['year'])) {
			$this->year = $descriptor['year'];
		}

		if (isset($descriptor['month'])) {
			$this->month = $descriptor['month'];
		}

		if (isset($descriptor['sources'])) {
			$this->sources = $descriptor['sources'];
		}

		if (isset($descriptor['source'])) {
			$this->source = $descriptor['source'];
		}

		if (isset($descriptor['period'])) {
			$this->period = $descriptor['period'];
		}

		parent::__construct($descriptor);
	}

	public function getYear () {
		if ($this->year === null) {
			$this->year = date('Y');
		}

		return $this->year;
	}

	public function setYear ($year) {
		if ((int) $year < 10) {
			$year = substr($year,1,1);
		}

		$this->year = $year;
		return $this;
	}

	public function getMonth () {
		if ($this->month === null) {
			$this->month = date('n');
		}

		return $this->month;
	}

	public function setMonth ($month) {
		if ((int) $month < 10) {
			$month = substr($month,1,1);
		}

		$this->month = $month;
		return $this;
	}

	public function getPeriod () {
		return $this->period;
	}

	public function setPeriod ($period) {
		$this->period = $period;
		return $this;
	}

	public function getSources () {
		return $this->sources;
	}

	public function getSource ($returnName = false) {
		$sources = $this->sources;

		foreach ($sources as $source => $details) {
			if ($this->source === null || $source == $this->source) {
				if ($returnName) {
					return $source;
				} else {
					return $details;
				}
			}
		}

		return false;
	}

	public function setSource ($source) {
		$this->source = $source;
		return $this;
	}

	public function getTable () {
		return $this->getSource()['table'];
	}

	public function getWhere () {
		return isset($this->getSource()['where']) ? $this->getSource()['where'] : null;
	}

	public function getLabel () {
		return $this->getSource()['label'];
	}

	public function getSum () {
		$source = $this->getSource();
		
		return getValue($source, 'sum', false);
	}

	public function getformat () {
		$source = $this->getSource();
		
		return getValue($source, 'format', '');
	}

	public function getChartTitle () {
		switch ($this->period) {
			case 'month':
				$year = $this->getYear();
				$month = $this->getMonth();
				$table = $this->getTable();

				$monthString = (string)$month;
				if ($month < 10) {
					$monthString = '0' . $month;
				}

				$days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

				$from = date('M d, Y', strtotime("$year-$month-01"));
				$to = date('M d, Y', strtotime("$year-$month-$days"));

				return "$from - $to";
				break;

			case 'year':
				return 'By year';
				break;
		}
	}

	public function getBarWidth () {
		switch ($this->period) {
			case 'month':
				return '96%';
				break;

			case 'year':
				return '30';
				break;
		}
	}

	public function getData() {
		switch ($this->period) {
			case 'month':
				return $this->getDataByMonth();
				break;

			case 'year':
				return $this->getDataByYear();
				break;
		}
	}

	public function getShowEvery() {
		switch ($this->period) {
			case 'month':
				return 3;
				break;

			case 'year':
				return 1;
				break;
		}
	}

	public function getDataByYear () {
		$table = $this->getTable();
		$where = $this->getWhere();

		$query = \DB::table($table)
					->where('created_at', '!=', "0000-00-00 00:00:00")
					->whereNotNull('created_at')
					->groupBy(\DB::raw('YEAR(created_at)'))
					->orderBy(\DB::raw('YEAR(created_at)'), 'ASC');

		if ($where !== null) {
			foreach ($where as $one) {
				$query->whereRaw($one);
			}
		}

		$sum = $this->getSum();
		if ($sum) {
			$query->select(\DB::raw('YEAR(created_at) as year'), \DB::raw('SUM(' . $sum . ') AS count'));
		} else {
			$query->select(\DB::raw('YEAR(created_at) as year'), \DB::raw('COUNT(*) AS count'));
		}

		$db = to_array($query->get());

		$firstYear = $db[0]['year'];
		end($db);
		$lastYear = $db[key($db)]['year'];

		$temp = [];
		foreach ($db as $one) {
			$temp[$one['year']] = $one['count'];
		}
		$db = $temp;

		$data = [
			[$this->getLabel(), 'Total', 'This year']
		];

		$total = 0;
		for ($year = $firstYear; $year <= $lastYear; $year++) {
			$value = 0;
			if (isset($db[$year])) {
				$value = $db[$year];
			}

			$total += $value;

			$data[] = [
				(string) $year,
				(int) $total,
				(int) $value,
			];
		}

		return $data;
	}

	public function getDataByMonth () {
		$year = $this->getYear();
		$month = $this->getMonth();
		$table = $this->getTable();
		$where = $this->getWhere();

		$monthString = (string)$month;
		if ($month < 10) {
			$monthString = '0' . $month;
		}

		$days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

		$query = \DB::table($table)
					->where('created_at', '>', "$year-$monthString-01")
					->where('created_at', '<=', "$year-$monthString-$days")
					->groupBy(\DB::raw('DATE(created_at)'));

		if ($where !== null) {
			foreach ($where as $one) {
				$query->whereRaw($one);
			}
		}

		$beforeQuery = \DB::table($table)
					->where('created_at', '<', "$year-$monthString-01");

		$sum = $this->getSum();
		if ($sum) {
			$query->select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(' . $sum . ') AS count'));
			$beforeQuery->select(\DB::raw('SUM(' . $sum . ') AS count'));
		} else {
			$query->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(*) AS count'));
			$beforeQuery->select(\DB::raw('COUNT(*) AS count'));
		}

		if ($where !== null) {
			foreach ($where as $one) {
				$beforeQuery->whereRaw($one);
			}
		}

		$db = to_array($query->get());
		$before = $beforeQuery->first();

		$temp = [];
		foreach ($db as $one) {
			$temp[$one['date']] = $one['count'];
		}
		$db = $temp;

		$data = [
			[$this->getLabel(), 'Total', 'This day']
		];

		$total = $before->count;
		for ($day = 1; $day <= $days; $day++) {
			$dayString = (string)$day;
			if ($day < 10) {
				$dayString = '0' . $day;
			}

			$date = $year . '-' . $monthString . '-' . $dayString;

			$value = 0;
			if (isset($db[$date])) {
				$value = $db[$date];
			}

			$total += $value;

			$dateLabel = date('M d', strtotime($date));

			$data[] = [
				$dateLabel,
				(int)$total,
				(int)$value,
			];
		}

		return $data;
	}
}