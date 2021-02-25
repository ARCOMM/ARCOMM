<li>{{ $level[0] }}</li>
<ul>
@if(isset($level[1]))
    @foreach($level[1] as $level)
        @include('partials.orbat', $level)
    @endforeach
@endif
</ul>
