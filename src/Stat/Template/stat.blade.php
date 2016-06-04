@foreach ($this->getStats() as $stat)
	{{$stat->render()}}
@endforeach