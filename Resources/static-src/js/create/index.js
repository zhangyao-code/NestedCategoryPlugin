initEvent();

function initEvent() {
    $('#create-btn').click(() => {
        if (!$('#create-btn').hasClass('disabled')) {
            $(this).button('loading');
            $.post($('#create-form').attr('action'), $('#create-form').serialize(), function (json) {
                window.location.reload();
            }, 'json');
        }
    });
}
