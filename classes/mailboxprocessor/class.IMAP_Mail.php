<?php
/* PEAR::Mail_mimeDecode */
require_once 'Mail/mimeDecode.php';
/*
 * IMAP_Mail
 * Класс для обработки письма использует класс PEAR::Mail_mimeDecode
 * экземпляр класса получает на вход текст письма методом IMAP_Mailbox::getMessage($mid) 
 * и преобразовывает его в объект (PEAR::Mail_mimeDecode), 
 * с которым в дальнейшем ведётся работа
 * 
 * Публичные методы
 * - getMail() - возвращает объект PEAR::Mail_mimeDecode
 * - getSubject() - возвращает тему письма
 * - getMp3Attachments() - специальный метод, ищет mp3 вложения в письме
 * возвращает имена файлов и их содержимое
 * - process($filterFrom, $filterSubject, $callback) - фильтрует письмо по: 
 * отправителю ($filterFrom) и теме письма ($filterSubject), если она указана
 * если письмо попало под фильтр - вызываем коллбэк, если нет поднимаем исключение IMAP_Mail_Exception.
 * Коллбэк принимает на вход нужный участок письма: тело письма или тело вложения.
 * Если нужный участок не определён, то поднимаем исключение IMAP_Mail_Exception
 * Коллбэки - статические методы класса IMAP_Mailbox_Callbacks
 * Пример: 
 * $msg->process('support@kassira.net', '', 'unikassa');
 * вызовет метод IMAP_Mailbox_Callbacks::unikassaCallback($data)
 */
class IMAP_Mail {
    /**
     * Объект преобразованный из письма
     * @var object 
     */
    private $_mail;
    
    /**
     * Тема письма
     * @var string
     */
    private $_subject;
    
    /**
     * Маркер прошло ли письмо фильтрацию
     * @var boolean
     */
    private $_filtered;
    
    /**
     * Конструктор - парсит письмо в объект PEAR::Mail_mimeDecode
     * @param string $mail текст письма
     */
    public function __construct($mail) {
        $env =& new Mail_mimeDecode($mail);
        $this->_mail = $env->decode(array('include_bodies' => true));
        foreach (imap_mime_header_decode($this->_mail->headers['subject']) as $subjPart) {
            if(trim($subjPart->text) !== '')
                $this->_subject .= $subjPart->text.' ';
        }
        $this->_subject = trim($this->_subject); #Удалим последний пробел
    }
    /**
     * Фильтрация по адресу отправителя
     * @param mixed $from адрес отправителя (string|array)
     * @return boolean
     */
    public function filterFrom($from) {
        if (is_string($from)) {
            return preg_match('/'.$from.'/', $this->_mail->headers['from']);
        } elseif (is_array($from)) {
            foreach ($from as $mailFrom) {
                if(preg_match('/'.$mailFrom.'/', $this->_mail->headers['from'])) {
                    return true;
                    break;
                }
            }
        } else
            return false;
    }
    
    /**
     * Фильтрация по теме письма
     * @param string $subject тема письма
     * @return boolean
     */
    public function filterSubject($subject) {
        return preg_match('/'.$subject.'/', $this->_subject);
    }
    
    /**
     * Возвращает тело письма в читаемом виде
     * или возвращает false, если тело пиьсма не найдено
     * @return mixed
     */
    protected function getBody() {
        foreach ($this->_mail->parts as $part) {
            if(isset($part->parts)) {
                foreach ($part->parts as $_part) {
                    if($_part->headers['content-transfer-encoding'] == 'base64' && !isset($_part->headers['content-disposition']) && trim($_part->body) !== '') {
                        return imap_base64($_part->body); #Возвращаем декодированное тело письма
                    } elseif($_part->headers['content-transfer-encoding'] == 'quoted-printable' && !isset($_part->headers['content-disposition']) && trim($_part->body) !== '') {
                        return imap_qprint($_part->body); #Возвращаем декодированное тело письма
                    }
                }
            } else {
                if($part->headers['content-transfer-encoding'] == 'base64' && !isset($part->headers['content-disposition']) && trim($part->body) !== '') {
                    return imap_base64($part->body); #Возвращаем декодированное тело письма
                } elseif($part->headers['content-transfer-encoding'] == 'quoted-printable' && !isset($part->headers['content-disposition']) && trim($part->body) !== '') {
                    return imap_qprint($part->body); #Возвращаем декодированное тело письма
                }
            }
        }        
        return false;
    }
    
    /**
     * Возвращает тело вложения в читаемом виде
     * или возвращает false, если тело вложения не найдено
     * @return mixed
     */
    protected function getAttachment() {
        foreach ($this->_mail->parts as $part) {
            if(isset($part->parts)) {
                foreach ($part->parts as $_part) {
                    if($_part->headers['content-transfer-encoding'] == 'base64' && $_part->disposition == 'attachment' && isset($_part->headers['content-disposition']) && trim($_part->body) !== '') {
                        return imap_base64($_part->body); #Возвращаем декодированное тело письма
                    } elseif($_part->headers['content-transfer-encoding'] != 'quoted-printable' && $_part->disposition == 'attachment' && isset($_part->headers['content-disposition']) && trim($_part->body) !== '') {
                        return imap_qprint($_part->body); #Возвращаем декодированное тело письма
                    } elseif($_part->headers['content-transfer-encoding'] == 'base64' && $_part->headers['ctype_primary'] == 'application' && $_part->headers['ctype_secondary'] == 'octet-stream' && trim($_part->body) !== '') {
                        return imap_base64($_part->body);
                    }
                }
            } else {
                if($part->headers['content-transfer-encoding'] == 'base64' && $part->disposition == 'attachment' && isset($part->headers['content-disposition']) && trim($part->body) !== '') {
                    return imap_base64($part->body); #Возвращаем декодированное тело письма
                } elseif($part->headers['content-transfer-encoding'] != 'quoted-printable' && $part->disposition == 'attachment' && isset($part->headers['content-disposition']) && trim($part->body) !== '') {
                    return imap_qprint($part->body); #Возвращаем декодированное тело письма
                } elseif($part->headers['content-transfer-encoding'] == 'base64' && $part->ctype_primary == 'application' && $part->ctype_secondary == 'octet-stream' && trim($part->body) !== '') {
                    return imap_base64($part->body);
                }
            }
        }
        return false;
    }
    
    /**
     * Возвращает письмо в виде объекта
     * @return object
     */
    public function getMail() {
        return $this->_mail;
    }
    
    /**
     * Возвращает тему письма
     * @return string
     */
    public function getSubject() {
        return $this->_subject;
    }
    
    /**
     * Возвращает адрес отправителя
     * @return string
     */
    public function getFrom() {
        return $this->_mail->headers['from'];
    }
    
    /**
     * Старше ли письмо даты $time
     * @param integer $time дата в секундах от 1 января 1970
     * @return boolean
     */
    public function getOldStatus($time) {
        return time() - strtotime($this->_mail->headers['delivery-date']) > $time;
    }
    
    /**
     * Возвращает вложения, если они являются файлами mp3
     * или поднимает исключение что соответствующих вложений не найдено
     * @return mixed
     */
    public function getMp3Attachments() {
        $attachments = array();
        foreach ($this->_mail->parts as $part) {
            if(isset($part->parts)) { 
                foreach ($part->parts as $_part) {
                    if($_part->headers['content-transfer-encoding'] == 'base64' && $_part->disposition == 'attachment' && isset($_part->headers['content-disposition']) && trim($_part->body) !== '' && isset($_part->d_parameters['filename'])) {
                        if(substr($_part->d_parameters['filename'], -3) == 'mp3')
                            $attachments[$part->d_parameters['filename']] = imap_base64($part->body);
                    }
                }
            } else {
                if($part->headers['content-transfer-encoding'] == 'base64' && $part->disposition == 'attachment' && isset($part->headers['content-disposition']) && trim($part->body) !== '' && isset($part->d_parameters['filename'])) {
                    if(substr($part->d_parameters['filename'], -3) == 'mp3')
                        $attachments[$part->d_parameters['filename']] = imap_base64($part->body);
                }
            }
        }
        return !empty($attachments) ? $attachments : false;
    }
}
/**
 * Класс для исключений
 */
class IMAP_Mail_Exception extends Exception { }
?>
