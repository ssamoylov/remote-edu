$(document).ready(function()
{
    test = new Test();

    $('#lnk-save').click(function() {
        setOptions();
    });

    $('#lnk-add-question').click(function() {
        test.addQuestion('pick-one', $('#questions'));
    });

    $('#frm-options').submit(function() {
        $('#lnk-save').click();
        return false;
    });

    if (null !== test_id) {
        test.setId(test_id);
        loadTest(test_id);
    }
})

function setOptions() {
    $('#status').text('Сохранение...').show();
    $('td.error').text('');

    var options = test.getOptions();
    options.test_id = test.getId();

    $.ajax({
        type:     'POST',
        url:      '/tests/ajax_save_options',
        data:     options,
        dataType: 'json',
        success:  onSetOptionsSuccess,
        error:    onAjaxError
    });
}

function onSetOptionsSuccess(response) {
    if (response.result != true && undefined !== response.formErrors)
    {
        for (field in response.formErrors) {
            $('#' + field)
                .parent()
                .next()
                .text(response.formErrors[field]);
        }
        $('#status').hide();
    }
    else if(response.result != true)
    {
        var msg = 'Не удалось сохранить тест. ' + response.error;
        $('#status').text(msg);
    }
    else
    {
        if (undefined !== response.testId) {
            test.setId(response.testId);
        }

        setQuestions();

        $('#lnk-add-question').show();
    }
}

function setQuestions() {
    var questions = test.getQuestions();

    if (!questions.length) {
        $('#status').hide();
        return;
    }

    test.hideErrors();

    questions = $.toJSON(questions);
    //alert(questions);

    var data = {
        test_id: test.getId(),
        questions: questions
    }

    $.ajax({
        type:     'POST',
        url:      '/tests/ajax_save_questions',
        data:     data,
        dataType: 'json',
        success:  onSetQuestionsSuccess,
        error:    onAjaxError
    });
}

function onSetQuestionsSuccess(response) {
    if (response.result != true)
    {
        if (undefined != response.field_errors)
        {
            var errors;

            for (var idx in response.field_errors)
            {
                errors = response.field_errors[idx];

                if (undefined != errors.question_id) {
                    test.showErrors('old', errors.question_id, errors.errors);
                }
                else if (undefined != errors.tmp_id) {
                    test.showErrors('new', errors.tmp_id, errors.errors);
                }
            }

            $('#status').text('Ошибки при заполнение форм вопросов.');
        }
        else
        {
            var msg = 'Не удалось сохранить вопросы. ' + response.error;
            $('#status').text(msg);
        }
    } else {

        if (undefined != response.new_ids) {
            test.setNewIds(response.new_ids);
        }

        $('#status').hide();
    }
}

function loadTest(tid) {
    $('#status').text('Загрузка...').show();

    $.ajax({
        type:     'POST',
        url:      '/tests/ajax_load_test',
        data:     {test_id: tid},
        dataType: 'json',
        success:  onTestLoadSuccess,
        error:    onAjaxError
    });
}

function onTestLoadSuccess(response) {
    if (response.result != true) {
        var msg = 'Не удалось загрузить параметры теста. ' + response.error;
        $('#status').text(msg);
    } else {
        test.setOptions(response.options);
        test.setQuestions(response.questions, $('#questions'));

        $('#status').hide();
        $('#lnk-add-question').show();
    }
}

function onAjaxError(xhr, textStatus, errorThrown) {
    var msg = 'Ошибка: ' + textStatus;
    $('#status').text(msg);
}