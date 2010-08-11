function newClass(child, parent) {
    var new_class = function() {
        if (new_class.__defining_class__) {
            this.__parent = new_class.prototype;
            return;
        }

        if (new_class.__constructor__) {
            this.constructor = new_class;
            new_class.__constructor__.apply(this, arguments);
        }
    }

    new_class.prototype = {};

    if (parent) {
        parent.__defining_class__ = true;
        new_class.prototype = new parent();
        delete parent.__defining_class__;

        new_class.prototype.constructor = parent;
        new_class.__constructor__ = parent;
    }

    var constr_name = '__construct';

    if (child)
    {
        for (var prop in child) {
            new_class.prototype[prop] = child[prop];
        }

        if (child[constr_name] && Object != child[constr_name]) {
            new_class.__constructor__ = child[constr_name];
        }
    }

    return new_class;
}

/**
* Прототип объекта для создания тестов.
*/
Test = {
    /**
    * Идентификатор теста.
    */
    _id: null,

    /**
    * Список вопросов, сохранённых в базе.
    */
    _questions: {},

    /**
    * Список новых введённых запросов.
    */
    _new_questions: {},

    /**
    * Список вопросов при прохождении теста.
    */
    _exam_questions: {},

    /**
    * Последнее значение временного идентификатора для новых вопросов.
    */
    _last_tmp_id: 0,

    /**
    * Карта типов вопросов в factory function для создания объектов вопросов.
    */
    _types_map: {
        'pick-one': function() { return new Question_PickOne(); }
    },

    /**
    * Объект представления для работы с формой опций теста.
    */
    _view: null,

    __construct: function(optsFormId) {
        this._id = null;
        this._questions = {};
        this._new_questions = {};
        this._last_tmp_id = 0;
        this._view = new View_Test_Options(optsFormId);
    },

    /**
    * Проверяет, установлен ли идентификатор теста.
    *
    * @return boolean
    */
    isIdSet: function() {
        return (null !== this._id);
    },

    /**
    * Установка идентификатора теста.
    *
    * @param  int
    * @return void
    */
    setId: function(id) {
        this._id = id;
    },

    /**
    * Получение идентификатор теста.
    *
    * @return int
    */
    getId: function() {
        return this._id;
    },

    /**
    * Добавление в тест нового вопроса.
    *
    * @param  string type      Тип вопроса.
    * @param  object container Контейнер на странице, в который будет добавлена форма вопроса.
    * @return void
    */
    addQuestion: function(type, container) {
        /* Создаём объект вопроса и добавляем его форму на страницу */
        var q = new this._types_map[type]();
        q.renderForm(container);

        /* Сохраняем в списке вопросов теста */
        this._new_questions[this._last_tmp_id++] = q;
    },

    /**
    * Отображение ошибок в форме вопроса.
    *
    * @param  string q_category Категория вопроса: new - новые, ещё не сохранённые вопросы, old - старые.
    * @param  int    key        Идентификатор вопроса.
    * @param  object errors     Список ошибок.
    * @return void
    */
    showErrors: function(q_category, id, errors) {
        var questions;

        /* Выбираем в зависимости от категории список вопросов */
        if ('new' == q_category) {
            questions = this._new_questions;
        } else {
            questions = this._questions;
        }

        /* Находим объект вопроса и вызываем его метод для вывода ошибок */
        questions[id].showErrors(errors);
    },

    /**
    * Скрывает надписи ошибок.
    *
    * @return void
    */
    hideErrors: function() {
        /* Создаём функцию для обработки элементов списка */
        var hider = function(id, q) {
            /* Прячем ошибки в очередном вопросе */
            q.hideErrors();
        };

        /* Проходим по обоим спискам вопросов нашей функцией */
        $.each(this._new_questions, hider);
        $.each(this._questions, hider);
    },

    /**
    * Установка значений опций теста. Эти значения будут сохранены в полях ввода
    * формы опций.
    *
    * @param  object options Список значений вида {option: value}.
    * @return void
    */
    setOptions: function(options) {
        /* Перебираем переданный список */
        for (key in options) {
            /* Через отображение */
            this._view
                /* находим поле ввода по ключу опции */
                .getInput(key)
                    /* и вводим в него значение опции */
                    .val(options[key]);
        }
    },

    /**
    * Получение значений опций теста.
    *
    * @return object
    */
    getOptions: function() {
        var data = {},
            /* Задаём список ключей, по которым будем находть значения опций */
            opts = ['theme', 'num_questions', 'errors_limit', 'attempts_limit'],
            opt;

        /* Перебираем список ключей */
        for (var idx in opts) {
            /* Берём очередный ключ опции */
            opt = opts[idx];
            /* И запоминаем значение из соответствующего input'а */
            data[opt] = this._view.getInput(opt).val();
        }

        return data;
    },

    /**
    * Добавление сохранённых вопросов из базы в тест.
    *
    * @param  array  Список вопросов.
    * @param  object Контейнер, в которой добавлять формы вопросов.
    * @return void
    */
    setQuestions: function(questions, container) {
        var q, q_obj;

        /* Перебираем список вопросов */
        for (key in questions)
        {
            /* Берём данные очередного вопроса */
            q = questions[key];
            /* Создаём объект соответствующего типа вопроса */
            q_obj = this._types_map[q.type]();

            /* Сохраняем в объекте данные вопроса */
            q_obj.setData(q);
            /* Выводим форму вопроса на страницу */
            q_obj.renderForm(container);

            /* И запоминаем новый объект вопроса в списке */
            this._questions[q.question_id] = q_obj;
        }
    },

    /**
    * Получение данных всех вопросов теста.
    *
    * @return array
    */
    getQuestions: function() {
        var questions = [], q, key;

        /* Перебираем старые вопросы */
        for (key in this._questions)
        {
            /* Берём очередной вопрос */
            q = this._questions[key];
            /* Собираем его данные из формы */
            q.collectData();
            /* И сохраняем в итоговый список */
            questions.push(q.getData());
        }

        var q_data = {};

        /* Перебираем новые вопросы */
        for (key in this._new_questions)
        {
            /* Берём очередной объект вопроса */
            q = this._new_questions[key];

            /* Собираем его данные из формы и получаем их */
            q_data = q.collectData();
            q_data = q.getData();

            /* Удаляем атрибут идентификатора, он ещё не задан, так как вопрос
            не сохранён */
            delete q_data.question_id;
            /* Присваиваем временный идентификатор - ключ в списке вопросов */
            q_data.tmp_id = key;

            /* Заносим данные в итоговый список */
            questions.push(q_data);
        }

        return questions;
    },

    setNewIds: function(ids) {
        var new_questions = this._new_questions,
            old_questions = this._questions;

        var setter = function(idx, elem) {
            var q = new_questions[idx];
            var data = q.getData();

            data.question_id = elem;
            q.setData(data);

            old_questions[data.question_id] = q;
            delete new_questions[idx];
        };

        $.each(ids, setter);
    },

    setExamQuestions: function(questions, container) {
        var exam_questions = this._exam_questions;
        var factory = this._types_map;

        $.each(questions, function(q_id, q_data) {
            var q_obj = factory[q_data.type]();

            q_data.question_id = q_id;
            q_obj.setExamData(q_data);

            q_obj.renderExamForm(container);

            exam_questions[q_id] = q_obj;
        });
    },

    getExamAnswers: function() {
        var answers = {};

        $.each(this._exam_questions, function(id, q) {
            answers[id] = q.getAnswer();
        });

        return answers;
    },

    disableRadios: function() {
        $.each(this._exam_questions, function(idx, q) {
            q.disableRadios();
        });
    },

    displayCorrectness: function(results) {
        var questions = this._exam_questions;

        var create_func = function(correctness) {
            return function(idx, q_id) {
                questions[q_id].setCorrectness(correctness);
            }
        }

        $.each(results.correct,    create_func(true));
        $.each(results.incorrect,  create_func(false));
        $.each(results.unanswered, create_func(false));
    }
}
Test = newClass(Test);

/**
* Прототип объекта вопроса с одним правильным ответом.
*/
Question_PickOne = {
    /**
    * Тип вопроса.
    */
    _type: 'pick-one',

    /**
    * Количество возможных ответов на вопрос.
    */
    _num_answers: 4,

    /**
    * Объект с текстом вопроса.
    */
    _question_input: null,

    _id_input: null,

    /**
    * Radio-button'ы и поля для ввода ответов.
    */
    _answer_inputs: [],

    _exam_answer_inputs: [],

    _data: null,

    _view: null,

    __construct: function() {
        this._num_answers = 4;
        this._text_input = null;
        this._id_input = null;
        this._answer_inputs = [];
        this._error_targets = {};
        this._data = null;
    },

    setData: function(data) {
        this._data = {
            question_id:    data.question_id,
            type:           this._type,
            question:       data.question,
            answers:        data.answers,
            correct_answer: data.correct_answer
        };
    },

    /**
    * Выборка данных вопроса из формы и сохранение их в одном массиве.
    */
    getData: function() {
        return $.extend({}, this._data);
    },

    issetData: function() {
        return (null !== this._data);
    },

    collectData: function() {
        var data = {};

        var id_input       = this._view.getIdInput(),
            question_input = this._view.getQuestionInput(),
            answer_inputs  = this._view.getAnswerInputs();

        data.question_id    = id_input.val();
        data.question       = question_input.val();
        data.answers        = [];
        data.correct_answer = null;

        if (this._view.isInputActive(question_input)) {
            data.question = question_input.val();
        } else {
            data.question = '';
        }

        var radio, answer, value, view = this._view;

        $.each(answer_inputs, function(idx, pair)
        {
            if ($(pair.radio).attr('checked')) {
                data.correct_answer = idx;
            }

            if (view.isInputActive($(pair.text))) {
                data.answers.push($(pair.text).val());
            }
        });

        this.setData(data);
    },

    setExamData: function(data) {
        this._data = {
            question_id: data.question_id,
            type:        this._type,
            question:    data.question,
            answers:     data.answers
        };
    },

    renderForm: function(container, q_data) {
        if (undefined === q_data && this.issetData()) {
            q_data = this.getData();
        }

        var view = new View_Question_PickOne_Edit();
        var html = view.render({
            num_answers: this._num_answers,
            q:           q_data
        });

        this._view = view;

        //alert(html.html());
        container.append(html);
    },

    renderExamForm: function(container, q_data) {
        if (undefined === q_data && this.issetData()) {
            q_data = this.getData();
        }

        var view = new View_Question_PickOne_Show();
        var html = view.render({
            id:      q_data.question_id,
            text:    q_data.question,
            answers: q_data.answers
        });

        this._view = view;

        //alert(html.html());
        container.append(html);
    },

    getAnswer: function() {
        var selected_idx = null;

        $.each(this._view.getRadios(), function(idx, radio) {
            if ($(radio).attr('checked')) {
                selected_idx = idx;
            }
        })

        return selected_idx;
    },

    showErrors: function(errors) {
        var target;

        for (target in errors) {
            this._view.showError(target, errors[target]);
        }
    },

    hideErrors: function() {
        this._view.hideErrors();
    },

    disableRadios: function() {
        this._view.disableRadios();
    },

    setCorrectness: function(correctness) {
        this._view.setCorrectness(correctness);
    }
}
Question_PickOne = newClass(Question_PickOne);

View = {
    render: function(tpl, data) {
        return $.nano(tpl, data);
    }
};
View = newClass(View);

View_Test_Options = {
    _inputs_map: {
        theme:          'theme',
        num_questions:  'num_questions',
        errors_limit:   'errors_limit',
        attempts_limit: 'attempts_limit'
    },

    _html: null,

    __construct: function(formId) {
        this._html = $(formId);
    },

    getInput: function(alias) {
        return $('#' + this._inputs_map[alias], this._html);
    }
};
View_Test_Options = newClass(View_Test_Options, View);

View_Question_PickOne = {
    _classes: {
        inactiveInput: 'inactive',
        errorTarget:   'e-target'
    },

    _error_targets: {},

    __construct: function() {
        this._error_targets = {};
    },

    isInputActive: function(input) {
        return !input.hasClass(this._classes.inactiveInput);
    },

    showError: function(target, msg) {
        $(this._error_targets[target])
            .text(msg)
            .show();
    },

    hideErrors: function() {
        $.each(this._error_targets, function(idx, elem) {
            $(elem).hide();
        });
    }
};
View_Question_PickOne = newClass(View_Question_PickOne, View);

View_Question_PickOne_Edit = {
    _classes: {
        form:     'question-form',
        id:       'question-id',
        question: 'question-text',
        radio:    'question-radio',
        answer:   'question-answer'
    },

    _tpl: {
        question: '<div>' +
                       '<form class="{cls.form}">' +
                           '<input type="text" class="{cls.question}" value="{q.question}" />' +
                           '<span class="{cls.errorTarget}"></span>' +
                           '<input type="hidden" class="{cls.id}" value="{q.question_id}" />' +
                           '{answers}' +
                       '</form>' +
                   '</div>',

        answer: '<input type="radio" class="{cls.radio}" name="correct_answer" {checked}/>' +
                '<input type="text" class="{cls.answer}" value="{answer}" />'
    },

    _html: null,

    __construct: function() {
        this.__parent.__construct.call(this);
        $.extend(this._classes, this.__parent._classes);
    },

    getIdInput: function() {
        return $('.' + this._classes.id, this._html);
    },

    getQuestionInput: function() {
        return $('.' + this._classes.question, this._html);
    },

    getAnswerInputs: function() {
        var radios = $('.' + this._classes.radio, this._html);
        var text_fields = $('.' + this._classes.answer, this._html);

        var pairs = [];
        radios.each(function(idx, radio) {
            pairs.push({
                radio: radio,
                text:  text_fields.get(idx)
            });
        });

        return pairs;
    },

    render: function(data) {
        var answers = '', checked;

        for (var i = 0; i < data.num_answers; i++) {
            checked = false;
            if (undefined != data.q) {
                if (i == data.q.correct_answer) {
                    checked = true;
                }
            }

            data.q.answers[i] = data.q.answers[i].replace(/"/gi, '\'');

            answers += this.__parent.render(this._tpl.answer, {
                cls:     this._classes,
                answer:  (undefined != data.q ? data.q.answers[i] : ''),
                checked: (checked ? 'checked ' : '')
            });
        }

        data.cls     = this._classes;
        data.answers = answers;

        if (undefined == data.q) {
            data.q = {
                question_id: '',
                question:    ''
            };
        }

        data.q.question = data.q.question.replace(/"/gi, '\'');
        //alert(data.q.question);

        var html = this.__parent.render(this._tpl.question, data);
        //alert(html);
        this._html = $(html);

        if (undefined == data.q.answers)
        {
            var hinter = this._createInputHinter(), pairs, q_input,
                empty_inputs = [];

            pairs = this.getAnswerInputs();

            $.each(pairs, function(idx, pair) {
                $(pair.text).data('hint', 'Ответ ' + (idx + 1));
                empty_inputs.push(pair.text);
            });

            q_input = this.getQuestionInput().data('hint', 'Вопрос');

            empty_inputs.push(q_input);
            $.each(empty_inputs, hinter);
        }

        this._error_targets.question =
            $('.' + this._classes.errorTarget, this._html).get(0);

        return this._html;
    },

    /**
    * Создание функции для установки подсказок в поля ввода. В качестве текста
    * подсказки используется текущее значение поля.
    */
    _createInputHinter: function() {
        /**
        * Заносим в переменную из области видимости функции класс неактивного
        * input'а, чтобы он был доступен внутри замыкания.
        */
        var class_inactive = this._classes.inactiveInput;

        var hinter = function() {
            $(this).val($(this).data('hint'));
            $(this)
                //.data('default', $(this).val())
                .addClass(class_inactive)

                .focus(function() {
                    if (
                        $(this).val() == $(this).data('hint') &&
                        $(this).hasClass(class_inactive)
                    ) {
                        $(this).val('');
                        $(this).removeClass(class_inactive);
                    }
                })

                .blur(function() {
                    var default_val = $(this).data('hint');
                    if ($(this).val() == '') {
                        $(this).addClass(class_inactive);
                        $(this).val($(this).data('hint'));
                    }
                });
        };

        return hinter;
    }
};
View_Question_PickOne_Edit = newClass(View_Question_PickOne_Edit,
                                      View_Question_PickOne);

View_Question_PickOne_Show = {
    _classes: {
        container: 'exam-container',
        form:      'exam-form',
        question:  'exam-question',
        answer:    'exam-answer',
        radio:     'exam-radio',
        correctness: 'exam-correctness'
    },

    _tpl: {
        question: '<li class="{cls.container}">' +
                      '<form class="{cls.form}">' +
                          '<div class="{cls.question}">{text}</div>' +
                              '<span class="{cls.errorTarget}"></span>' +
                              '{answers}' +
                      '</form>' +
                  '</li>',

        answer: '<div class="{cls.answer}">' +
                    '<input type="radio" name="correct_answer" id="{id}" class="{cls.radio}" />' +
                    '<label for="{id}">{answer}</label>' +
                 '</div>'
    },

    _html: null,

    __construct: function() {
        this.__parent.__construct.call(this);
        $.extend(this._classes, this.__parent._classes);
    },

    render: function(data) {
        var answers = '';

        for (idx in data.answers) {
            answers += this.__parent.render(this._tpl.answer, {
                cls:    this._classes,
                answer: data.answers[idx],
                id:     'answer_' + data.id + '_' + idx
            });
        }

        data.cls     = this._classes;
        data.answers = answers;

        var html = this.__parent.render(this._tpl.question, data);

        this._html = $(html);
        return this._html;
    },

    getRadios: function() {
        return $('.' + this._classes.radio, this._html);
    },

    getContainer: function() {
        return this._html;
    },

    disableRadios: function() {
        this.getRadios().each(function(idx, radio) {
            $(radio).attr('disabled', 'disabled');
        });
    },

    setCorrectness: function(correctness) {
        var img = (correctness ? 'plus' : 'minus');
        this.getContainer().css('list-style-image',
                                'url(/images/' + img  + '.png)');
    }
};
View_Question_PickOne_Show = newClass(View_Question_PickOne_Show,
                                      View_Question_PickOne);