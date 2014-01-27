(function () {
    'use_strict'
    $('#check-notifications').on('change', function() {
        var val = ($('#check-notifications').attr('checked')) ? 1: 0;
        $.ajax({
            type: 'POST',
            url: Routing.generate('claro_message_notification', {'isNotified': val}),
            success: function() {
                var translation_key = (val === 0) ? 'notification_deactivated': 'notification_activated';
                var toAppend = "<div class='alert alert-info'>" +
                    "<a class='close' data-dismiss='alert' href='#'>×</a>" +
                    Translator.get('platform' + ':' + translation_key); +
                    "</div>";

                $('#flashbox').append(toAppend);
            }
        })
    });
})();