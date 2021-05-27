<span>
    <iframe src="{{ $video->url() }}" 
            frameborder="0" 
            allowfullscreen="true" 
            scrolling="no" 
            height="400" 
            width="400">
    </iframe>

    @if ($video->isMine() || auth()->user()->hasPermission('mission_media:delete'))
            <span class="fa fa-trash mission-video-item-delete" data-video="{{ $video->id }}"></span>
    @endif
</span>