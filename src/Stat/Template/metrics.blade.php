<div class="row wax-stat-metrics-container white">
	@foreach ($this->getMetrics() as $metric)
		{{$metric->render()}}
	@endforeach
</div>