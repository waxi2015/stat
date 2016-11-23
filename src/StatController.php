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
    	if (!\Auth::guard('admin')->check()) {
    		\App::abort(403, 'Unauthorized action.');
    	}

    	$descriptor = unserialize(decode($request->descriptor));

		$stat = new \Stat($descriptor);

    	switch ($stat->getType()) {
    		case 'chart':
    			$id = $request->id;
				$source = $request->source;
				$year = $request->year;
				$month = $request->month;
				$period = $request->period;

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
    			break;

    		case 'simple-bars':
    			$id = $request->id;

				$simpleBars = $stat->getStat($id);

				$response['data'] = $simpleBars->getData();
    			break;
    	}
		

		return $response;
	}
}
