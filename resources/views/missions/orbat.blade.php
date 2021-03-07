@php
    $level = $mission->orbat($faction);
@endphp

<div class="pull-left w-100 m-t-3">
    @include('partials.orbat', $level)
</div>
