
@php
$levels = (array)$mission->orbat();
@endphp

<div class="pull-left w-100 m-t-3">
        @each('partials.orbat', $levels, 'level', 'partials.orbat-none')
</div>
