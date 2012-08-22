<?php
/*
 * Общий интерфейс для всех кэшей
 */
interface YIСache {
    /*
     * Получаем значение из кэша по ключу
     * @param integer $id идентификатор, примем его за целочисленный
     * @return mixed возвращать будем значение из кэша
     */
    public function get($id);
    /*
     * Загружает значение по целочисленному ключу если такое уже имеется - 
     * будет произведена замена
     * @param integer $id идентификатор
     * @return boolean
     */
    public function set($id, $value, $expire = 0);
    /*
     * Загружает значение по целочисленному ключу если такое уже имеется - 
     * ничего не добавится
     * @param integer $id идентификатор
     * @return boolean true в случае успеха добавления значения в кэш, false при неудаче 
     */
    public function add($id, $value, $expire = 0);
    /*
     * Удаляём значение из кэша по идентификтору
     * @param integer $id
     * @return boolean 
     */
    public function delete($id);
    /*
     * Полная очистка кэша
     * @return boolean
     */
    public function flush();
}
?>
