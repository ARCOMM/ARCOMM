require('./bootstrap');

$(document).ready(function(event) {
    /*$.ajax({
        type: 'GET',
        url: (window.location.protocol + "//" + window.location.hostname + "/") + 'hub/notifications',
        success: function(data) {
            var container = $('#nav-notifications');
            container.find('.dropdown-menu').html(data);

            if (container.find('.dropdown-menu .list-group').hasClass('notifications-empty')) {
                container.find('.nav-link .notifications-has-unread').hide();
            } else {
                container.find('.nav-link .notifications-has-unread').show();
            }
        }
    });*/
});

window.mdconvert = new showdown.Converter();

setUrl = function(url, title) {
    var url = (window.location.protocol + "//" + window.location.hostname + "/") + url;

    window.history.replaceState({
        archubPush: true
    }, document.title, url);

    if (title) document.title = title;
};

(function($) {
    $.fn.offClick = function(a) {
        var caller = $(this);
        $(document).bind("mouseup.offClick", function(event) {
            var target = $(event.target);
            if (!target.is(caller) && !target.is(caller.find('*')))
                if (typeof a == "function") a();
        });
    }
})(jQuery);
