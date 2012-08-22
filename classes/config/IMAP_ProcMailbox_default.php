<?php
#Почта администратора, на которую будут приходить уведомления
define('ADMINMAIL', 'd.belikov@sprinthost.ru');
#Имя папки для писем, не попаших под фильтр, например INBOX.unrecognized
define('UNRECOGNIZED', 'INBOX.unrecognized');
#Имя папки для писем, в результате обработки которых произошла ошибка, например INBOX.manual
define('MANUAL', 'INBOX.manual');
#Имя папки для обработанных писем, например INBOX.processed
define('PROCESSED', 'INBOX.processed');
#Параметр, определяющий на сколько "старое" должно быть письмо, 
#чтобы его удалить (при IMAP_MailboxProc::_delete == true), в секундах
define('OLDERTHAN', 3600*24*3);
?>
