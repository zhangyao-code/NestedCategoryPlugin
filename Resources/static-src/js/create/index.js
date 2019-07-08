initEvent();

function initEvent() {
    const $form = $('#create-form');

    const validator = $form.validate({
        rules: {
            name: {
                maxlength: 200,
                required: {
                    depends () {
                        $(this).val($.trim($(this).val()));
                        return true;
                    }
                },
            },
        },
        messages: {
            name: {
                required: Translator.trans('project_plan.base.name.required_message'),
                trim: Translator.trans('project_plan.base.name.required_message'),
            },
        }
    });

    $('#create-btn').click(() => {
        if (validator.form()) {
            $(this).button('loading');
            $.post($('#create-form').attr('action'), $('#create-form').serialize(), function (json) {
                window.location.reload();
            }, 'json');
        }

    });

}
