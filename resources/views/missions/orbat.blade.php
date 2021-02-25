
@php
$factions = (array)$mission->orbat();
@endphp

@if(!is_null($factions))
        <div class="pull-left w-100 m-t-3">
        @foreach($factions as $name => $level)
                <h2>{{ $name }}</h2>
                <ul>
                        @include('partials.orbat', $level)
                </ul>
        @endforeach
        </div>
@endif
