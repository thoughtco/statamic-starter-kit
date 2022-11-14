@if(isset($params['type']) && $params['type'] == 'string')
	{{ $src }}
@else 
	<img src="{{ $src }}" {!! $params !!} />
@endif