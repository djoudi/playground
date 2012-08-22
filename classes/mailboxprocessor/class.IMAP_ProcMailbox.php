<?php
/* PEAR::Net_IMAP */
require_once 'Net/IMAP.php';
/* Подключаем класс для работы с письмом */
require_once dirname(__FILE__).'/class.IMAP_Mail.php';

/* Подключаем конфигурацию класса */
require_once dirname(__FILE__).'/config/IMAP_ProcMailbox_default.php';

/*
 * Класс IMAP_MailboxProc - является обработчиком писем удалённого ящика, 
 * к которому подключается
 * Основной метод process($filterFrom, [$filterSubject = '']) - обрабатывает письма, используя функцию-коллбек, 
 * имя которой указывается при создании объекта данного класса и перекладывает эти письма в определённые папки.
 * Перемещение писем зависит от результата фильтрации и успешности или не успешности обработки письма.
 * В случае, если письмо не попало под фильтры ($filterFrom, [$filterSubject = '']) письмо переходит в папку
 * 'unrecognized' (Неопознанное).
 * В случае, если возникла ошибка в результате обработки, с помощью функции-коллбэка, 
 * которая обязательно должна выбросить Exception письмо перекладывается в папку 'manual' (необработанное) 
 * и администратору отправляется уведомление на почту.
 * В случае, если проверка прошла успешно, письмо перекладывается в папку 'processed' (обработанное). 
 */

class IMAP_ProcMailbox {
    /**
     * Адрес эл. ящика
     * @var string 
     */
    public    $login;
    
    /**
     * Объект ящика, с которым производится работа
     * @var object Net_IMAP 
     */
    protected $mailbox;
    
    /**
     * Имя функции-обработчика
     * @var string 
     */
    protected $callback;
    
    /**
     * Маркер для удаления "старых" писем
     * @var boolean 
     */
    private   $_delete;
    
    /**
     * Устанавка соединения с IMAP сервером и создание папок, если их нет
     * @param string $mailBox адрес IMAP сервера
     * @param string $login ящик на сервере (адрес эл. почты)
     * @param string $pass пароль
     * @param string $callback имя коллбэк-функции для обработки тела письма
     * @param boolean $delete маркер на удаление писем старше, чем OLDERTHAN
     */
    public function __construct($mailBox, $login, $pass, $callback, $delete = false) {
        /* Подключение к ящику */
        $this->mailbox =& new Net_IMAP($mailBox, 143, false, 'UTF-8');
        $this->mailbox->login($login, $pass, TRUE, TRUE);
        $this->login = $login;
        /* Задаём имя ф-ии-коллбэка */
        $this->callback = $callback;
        /* Устанавливаем флаг на удаление */
        $this->_delete = $delete;
        
        /* Создаём необходимые папки, если их нет */
        if(!$this->mailbox->mailboxExist(UNRECOGNIZED)) {
            $this->mailbox->cmdCreate(UNRECOGNIZED);
        }
        if(!$this->mailbox->mailboxExist(MANUAL)) {
            $this->mailbox->cmdCreate(MANUAL);
        }
        if(!$this->mailbox->mailboxExist(PROCESSED)) {
            $this->mailbox->cmdCreate(PROCESSED);
        }
    }
    
    /**
     * Метод отсылающий сообщение об ошибке на почту администратору, в случае, 
     * если обработка письма завершилась неудачей и коллбэк функция выбросила исключение
     * сообщение этого исключения будет отослано на адрес администратору, который должен быть указан
     * в настройках класса, как переменная ADMINMAIL
     * @param string $mailBody тело письма, (в него может попадать текст исключения от коллбэк-функции 
     * или другое текстовое сообщение)
     */
    protected function mailErrorHandler($mailBody) {
        $headers="From: {robot}\r\nReply-To: {}\r\nContent-Type: text/plain; charset=utf-8";
        mail(ADMINMAIL, 'Ошибка обработки письма', $mailBody, $headers); 
    }
    
    /**
     * Метод для проверки заполненности ящика, если в ящике осталось места менее 1Мб
     * @return boolean
     */
    protected function checkMailboxUsageLimit() {
        $rootQuota = $this->mailbox->getStorageQuotaRoot();
        if($rootQuota['USED'] <= $ $rootQuota['QMAX'] - QUOTAROOTLIMIT)
            return true;
        else
            return false;
    }
    /**
     * Метод обработки писем в ящике (в папке INBOX):
     * 1. Проверяем, не заполнен ли ящик, если да - отсылаем уведомление
     * 2. Ищем непрочитанные письма и обрабатываем их, 
     * с последующим перемещением в сответствующие папки
     * 3. Ищем старые письма и удаляем их, если параметр $_deleted == true
     * @param string $filterFrom
     * @param string $filterSubject [optional]
     * @return void
     */
    public function process($filterFrom, $filterSubject = '') {
        /* Проверка на непустой фильтр */
        if(empty($filterFrom))
            throw new IMAP_Mail_Exception('You need to set up filter from');
        /* А вдруг ящик переполнен? */
        if($this->checkMailboxUsageLimit())
            $this->mailErrorHandler('В ящике ' . $this->login . ' осталось места менее '. QUOTAROOTLIMIT/1024 . ' Мб');
        
        /* Начинаем обработку почтового ящика */
        if ($this->mailbox->getNumberOfMessages()) {
            foreach ($this->mailbox->getMessages() as $mid => $msg) {
                /* Создаём объект вспомогательного класса на основе PEAR::Mail_mimeDecode */
                $mail = new IMAP_Mail($msg);
                /* Фильтрация */
                if(!empty($filterFrom) && empty($filterSubject)) {
                    $_filtered = $mail->filterFrom($filterFrom);
                } elseif(!empty($filterFrom) && !empty($filterSubject)) {
                    $_filtered = $mail->filterFrom($filterFrom);
                    $_filtered = $mail->filterSubject($filterSubject);
                }
                /* Письмо не попало под фильтр */
                if($_filtered == false) {
                    $this->mailbox->copyMessages(UNRECOGNIZED, $mid);
                } else {
                    if($this->_delete === true) {
                        if($mail->getOldStatus(OLDERTHAN)) {
                            $this->mailbox->deleteMsg($mid);
                        } else {
                            try {
                                if(call_user_func($this->callback, $mail)) {
                                    $this->mailbox->copyMessages(PROCESSED, $mid);
                                    $this->mailbox->deleteMsg($mid);
                                }
                            } catch (Exception $e) {
                                $this->mailbox->copyMessages(MANUAL, $mid);
                                $this->mailbox->deleteMsg($mid);
                                $this->mailErrorHandler($e->getMessage());
                            }
                        }
                    } else {
                        try {
                            if(call_user_func($this->callback, $mail)) {
                                $this->mailbox->copyMessages(PROCESSED, $mid);
                                $this->mailbox->deleteMsg($mid);
                            }
                        } catch (Exception $e) {
                            $this->mailbox->copyMessages(MANUAL, $mid);
                            $this->mailbox->deleteMsg($mid);
                            $this->mailErrorHandler($e->getMessage());
                        }
                    }
                }
            }
        }
        
        $this->mailbox->cmdExpunge();
        $this->mailbox->cmdLogout();
    }
}
?>
