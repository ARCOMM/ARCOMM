<script>
    $(document).ready(function(e) {
        $.ajax({
            type: 'POST',
            url: '{{ url('/hub/missions/orbat') }}',
            data: {
                mission_id: {{ $mission->id }}
            },
            success: function(data) {
                $('.mission-orbat-content').html(data);
            }
        });

        event.preventDefault();
    });
</script>

<div class="mission-orbat">
    <div class="mission-orbat-content"></div>
</div>
