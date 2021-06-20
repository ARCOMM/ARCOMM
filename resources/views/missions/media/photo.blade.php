<div
    href="{{ $media->getUrl() }}"
    class="mission-media-item mission-media-item-image"
    style="background-image: url('{{ url($media->getUrl('thumb')) }}')">

    @if ($media->getCustomProperty('user_id', -1) == auth()->user()->id || auth()->user()->hasPermission('mission_media:delete'))
        <span class="fa fa-trash mission-media-item-delete" data-mission="{{ $mission->id }}" data-media="{{ $media->id }}"></span>
    @endif
</div>
