/*
  Метод для написания функции, содержащей более 2х вопросов для пользователя,
  зависящих от каких-либо условий
  Поскольку в нашей системе используется кастомизированный prompt было придумано следующее 
  решение для сокращения количества кода
  По аналогии можно написать любой метод сожержащий >= 2 prompt, содержащих в себе callbacks
  или же если вам необходимо сделать несколько действий в зависимости от каких-либо условий.

  В общем виде схема работы следующая:
  1. Задаём массив условий - conditions и устанавлием индекс в нулевое значение - condition_index = 0
  2. Задаём массив функций, которые будут выполнены, если соответствующее условие истинно.
     В конец каждой из этих функций добавляем метод check(), который пройдёт по оставшимся условиям
     если мы не достигли последнего. Как только последнее условие отработает выполняем конечные 
     действия для данной функции, например посылаем ajax запрос.
*/
function process_with_conditions() {
    /* Создаём объект для ответов пользователя, с объектом работать легче, т.к. нет ассоциативных массивов */
    var answers = {}; 
    /* 
      Условия при которых мы будем спрашивать пользователя о том или ином решении 
      Например что-то было отмечено в DOM: $('free').checked == true
    */
    var conditions = ['test' == 'test', 'test2' == 'test2'];
    /*
      Индекс чтобы пройтись оп всем условиям и соответствующим prompt
    */
    var condition_index = 0;
    /*
      Сами вопросы (массив), соответствуют условиям conditions:
      первая ф-я будет вызывана при первом условии
    */
    var prompts = [
        function() {
            smoke.prompt('Prompt 1', function(e){
                if(e) {
                    answers.a = e; //Запоминаем ответ пользователя
                } else {
                    answers.a = 'nothing'; //Нажали "отмена"
                }
                check(); //Проверяем следующие условия
            });
        },
        function() {
            smoke.prompt('Prompt 2', function(e){
                if(e) {
                    answers.b = e; //Запоминаем ответ пользователя
                } else {
                    answers.b = 'nothing'; //Нажали "отмена"
                }
                check(); //Проверяем следующие условия
            });
        },
    ];
    /*
      Этот метод проверяет условия и увеличивает счётчик. 
      Как только мы достигнем последнего условия выполняются дальнейшие действия
    */
    var check = function() {
        /*
          Достигли последнего ответа и можем обрабатывать ответы,
          посылать с ними запросы и т. д.
          для примера просто вывод  
        */
        if(condition_index == conditions.length) {
            console.log(answers);
        }
        /*
          Выбираем текущее условие condition_index и вызываем соответствующий prompt
        */
        while(condition_index < conditions.length) {
            if(conditions[condition_index]) {
                prompts[condition_index]();
                condition_index++; //Увеличили индекс
                break; //Вышли из цикла
            }
            condition_index++; //Увеличили индекс
        }
        
    }
    check(); //Делаем вызов
}
function process_something_with_conditions() {
    if(condition) {
        smoke.prompt('Bla bla bla?', function(e){
            if(e) {
                //Запомнили ответ пользователя
            } else {
                //Что-то другое
            }
            if(condition2) {
                smoke.prompt('Bla bla bla?', function(e){
                    if(e) {
                        //Запомнили ответ пользователя
                    } else {
                        //Что-то другое
                    }
                });
            }
        });
    } else {
        if(condition2) {
            smoke.prompt('Bla bla bla?', function(e){
                if(e) {
                    //Запомнили ответ пользователя
                } else {
                    //Что-то другое
                }
            });
        } else {
            //... в том же духе
        }
    }
    
}
function test_replace_account() {
    var answers = {};

    var conditions = [
        1 == 1,
        //$('pin') && $('pin').value != $('input_pin').value, 
        $('other_hoster') && $('regmov') && $('regmov').checked == true && $('other_hoster').checked == false && $('jur6month') && $('jur6month').checked == false, 
        $('free') && $('free').checked == true && $('input_free').value == 'n',
        $('free') && $('free').checked == false && $('input_free').value == 'y'
    ];
    var condition_index = 0;
    
    answers.input_pin_change_comment = '';
    answers.no_other_hoster_bonus_comment = '';
    answers.free_customer_change_comment = '';
    answers.free = '';
    answers.jur6month = '';
    
    var prompts = [
        function() {
            smoke.prompt('Вы изменили значение PIN для аккаунта. Укажите причину:', function(e) {
               if(e != '') {
                   answers.input_pin_change_comment = e;
               } else {
                   answers.input_pin_change_comment = 'причина не указана.';
               }
               /*
               if ($('input_pin').value == '') {
                   a.input_pin_change_comment = 'PIN был установлен по причине: ' + a.input_pin_change_comment;
               } else if ($('pin').value == '') {
                   a.input_pin_change_comment = 'PIN был удален по причине: ' + a.input_pin_change_comment;
               } else {
                   a.input_pin_change_comment = 'PIN был изменен по причине: ' + a.input_pin_change_comment;
               }
               */
               answers.input_pin_change_comment = 'PIN был изменен по причине: ' + answers.input_pin_change_comment;
               check();
            });
        },
        function() {
            smoke.prompt('Бонус при переходе от другого провайдера не был зачислен. Укажите причину:', function(e){
               if(e != '') {
                   answers.no_other_hoster_bonus_comment = e;
               } else {
                   answers.no_other_hoster_bonus_comment = 'причина не указана.';
               }
               answers.no_other_hoster_bonus_comment = 'Бонус при переходе от другого провайдера не был зачислен по причине: ' + answers.no_other_hoster_bonus_comment;
               check();
            });
        },
        function () {
            smoke.prompt('Вы хотите сделать аккаунт бесплатным. Укажите причину:', function(e){
                if(e != '') {
                   answers.free_customer_change_comment = e;
               } else {
                   answers.free_customer_change_comment = 'причина не указана.';
               }
               answers.free_customer_change_comment = 'Аккаунт сделан бесплатным по причине: ' + answers.free_customer_change_comment;
               answers.free = 'y';
               answers.cp_enter = false;
               smoke.confirm('Запретить вход в Панель управления?', function(e) {
                   if(e) {
                       answers.cp_enter = true;
                   }
                   check();
               });
            });
        },
        function() {
            smoke.prompt('Вы хотите сделать аккаунт платным. Укажите причину:', function(e){
                if(e != '') {
                   answers.free_customer_change_comment = e;
               } else {
                   answers.free_customer_change_comment = 'причина не указана.';
               }
               answers.free_customer_change_comment = 'Аккаунт сделан платным по причине: ' + answers.free_customer_change_comment;
               answers.free = 'n';
               check();
            });
        }
    ];
    var afterCheck = function() {
        console.log(answers);
    }
    var check = function() { 
        while(condition_index < conditions.length) {
            if(conditions[condition_index]) {
                prompts[condition_index]();
                condition_index++;
                return ;
            }  
            condition_index++;
        }  
        afterCheck();
    }
    /*
    if (!$('modify') || ($('modify').value != 'on' && $('login').value.length < 5)) {
        smoke.alert('Логин должен состоять как минимум из 5 символов');
        return;
    }
    if ($('jur6month') && $('jur6month').checked == true && $('other_hoster').checked == true) {
        $('other_hoster').checked = false;
    }
    */
    check();
}



