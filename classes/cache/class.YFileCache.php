<?php
require_once 'class.YCache.php';

class YFileCache extends YCache {
    public $cachePath;
    public $cacheFileSuffix = '.bin';
    
    public function __construct() {
        /*
         * Создаём папку куда будем записывать файлы
         */
        if($this->cachePath===null)
            // лучше задать абсолютный путь, но и так пока сойдёт
            $this->cachePath='runtime'.DIRECTORY_SEPARATOR.'cache'; 
        if(!is_dir($this->cachePath))
            mkdir($this->cachePath,0777,true);
    }
    protected function getCacheFile($key)
    {
        if($this->directoryLevel>0)
        {
            $base=$this->cachePath;
            for($i=0;$i<$this->directoryLevel;++$i)
            {
                if(($prefix=substr($key,$i+$i,2))!==false)
                    $base.=DIRECTORY_SEPARATOR.$prefix;
            }
            return $base.DIRECTORY_SEPARATOR.$key.$this->cacheFileSuffix;
         }
         else
                        return $this->cachePath.DIRECTORY_SEPARATOR.$key.$this->cacheFileSuffix;
    }
    protected function getValue($key) {
       ;
    }
    protected function setValue($key, $value, $expire) {
        throw new Exception(get_class($this) . ' doesn\'t support set() functionality');
    }
    protected function addValue($key, $value, $expire) {
        throw new Exception(get_class($this) . ' doesn\'t support add() functionality');
    }
    protected function deleteValue($key) {
        throw new Exception(get_class($this) . ' doesn\'t support delete() functionality');
    }
    protected function flushValues() {
        throw new Exception(get_class($this) . ' doesn\'t support flush() functionality');
    }
    protected function flushValues()
    {
        $this->gc(false);
        return true;
    }
    public function gc($expiredOnly=true,$path=null)
    {
        if($path===null)
            $path=$this->cachePath;
        if(($handle=opendir($path))===false)
            return;
        while(($file=readdir($handle))!==false)
        {
            if($file[0]==='.')
                continue;
            $fullPath=$path.DIRECTORY_SEPARATOR.$file;
                if(is_dir($fullPath))
                    $this->gc($expiredOnly,$fullPath);
                else if($expiredOnly && @filemtime($fullPath)<time() || !$expiredOnly)
                    @unlink($fullPath);
         }
         closedir($handle);
     }
}
?>
