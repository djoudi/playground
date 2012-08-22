<?php
require_once 'class.YCache.php';

class YDbCache extends YCache {
    public $cacheTableName = 'myCache';  
    public $autoCreateCacheTable = true;
    
    private $_db;
    /*
     * Не хорошо, но создадим таким вот образом соединение к БД
     * По хорошему нужен ещё 1 класс, отвечающий за соединение с БД, но
     * ... лень :-)
     */
    public function __construct($dsn, $username, $passw) {
        $this->_db = new PDO($dsn, $username, $passw);
        
        if($this->autoCreateCacheTable == true) {
            $sql="DELETE FROM {$this->cacheTableName} WHERE expire>0 AND expire<".time();
            try {
                $this->_db->prepare($sql)->execute();
            } catch (Exception $e) {
                $this->createCacheTable($this->cacheTableName);
            }
        }
    }
    protected function createCacheTable($tableName) {
        $sql=<<<EOD
CREATE TABLE $tableName
(
        id CHAR(128) PRIMARY KEY,
        expire INTEGER,
        value LONGBLOB
)
EOD;
        $this->_db->prepare($sql)->execute();
    }
    protected function getValue($key) {
        $time = time();
        $sql="SELECT value FROM {$this->cacheTableName} WHERE id=$key AND (expire=0 OR expire>$time)";
        /* Нужно вернуть скаляр !!!*/
        return $this->_db->prepare($sql)->execute();
    }
    protected function setValue($key, $value, $expire) {
        $this->deleteValue($key);
        return $this->addValue($key,$value,$expire);
    }
    protected function addValue($key, $value, $expire) {
        if($expire > 0)
            $expire += time();
        else
            $expire = 0;
        $sql = "INSERT INTO {$this->cacheTableName} (id,expire,value) VALUES ($key, $expire, :value)";
        try {
            $command = $this->_db->prepare($sql);
            $command->bindValue(':value', $value, PDO::PARAM_LOB);
            $command->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    protected function deleteValue($key) {
         $sql = "DELETE FROM {$this->cacheTableName} WHERE id=$key";
    }
    protected function flushValues() {
        $this->_db->query("DELETE FROM {$this->cacheTableName}")->execute();
        return true;
    }
}
?>
