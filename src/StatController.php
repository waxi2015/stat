<?php

namespace Waxis\Stat;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class StatController extends Controller
{
	public function __construct (Request $request) {
		if (isset($request->locale)) {
			\Lang::setLocale($request->locale);
		}
	}

    public function chart (Request $request) {
    	$descriptor = unserialize(decode($request->descriptor));
		$id = $request->id;
		$source = $request->source;
		$year = $request->year;
		$month = $request->month;
		$period = $request->period;

		$stat = new \Stat($descriptor);
		$chart = $stat->getStat($id)
					 ->setSource($source)
					 ->setYear($year)
					 ->setMonth($month)
					 ->setPeriod($period);

		$response['data'] = $chart->getData();
		$response['chartTitle'] = $chart->getChartTitle();
		$response['barWidth'] = $chart->getBarWidth();
		$response['showEvery'] = $chart->getShowEvery();
		$response['format'] = $chart->getFormat();

		return $response;
	}
}
