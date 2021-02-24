<li>{{ $level[0][1] }}</li>
@if (is_array($level[0][0]))
    @if(is_array($level[0][0]))
        <ul>
        @foreach($level[0][0] as $squad)
            <li> {{ $squad[0] }} </li>
            <ul>
                @foreach($squad[1] as $unit)
                    <li> {{ $unit }} </li>
                @endforeach
            </ul>
        @endforeach
        </ul>
    @endif
@endif

@if(count($level) > 1)
    <ul>
    @foreach($level[1] as $level)
        @include('partials.orbat', $level)
    @endforeach
    </ul>
@endif
