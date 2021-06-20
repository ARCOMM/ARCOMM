<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)" role="button" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-history"></i>
    </a>

    <div class="dropdown-menu" style="width: 20rem">
        <div class="list-group pull-left w-100">
            @if ($mission->revisions->isEmpty())
                <p class="text-xs-center text-muted p-y-2 m-b-0">No revisions have been made!</p>
            @else
                @foreach ($mission->revisions as $revision)
                    <a
                        href="javascript:void(0)"
                        class="list-group-item notification-item"
                        title="{{ $revision->created_at }}"
                        style="text-decoration: none !important;cursor: default;">
                        <span class="notification-image" style="'background-image: url('{{ $revision->user->avatar }}')"></span>

                        <p class="list-group-item-text notification-text">
                            Updated by {{ $revision->user->username }}
                        </p>

                        <p class="list-group-item-text notification-subtext">
                            {{ $revision->created_at->diffForHumans() }}
                        </p>
                    </a>
                @endforeach
            @endif
        </div>
    </div>
</li>
