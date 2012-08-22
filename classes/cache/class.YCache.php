<?php
require_once 'interface.YICache.php';

abstract class YCache implements IYCache, ArrayAccess {
    public function get($id) {
        if(($value = $this->getValue($id)) !== false) {
            $data = unserialize($value);
            if(!is_array($data))
                return false;
            
            return $data[0];
        }
        return false;
    }
    public function set($id, $value, $expire = 0) {
        $data = array($value);
        return $this->setValue($id, serialize($data), $expire);
    }
    public function add($id, $value, $expire = 0) {
        $data = array($value);
        return $this->addValue($id, serialize($data), $expire);
    }
    public function delete($id) {
        return $this->deleteValue($id);
    }
    public function flush() {
        return $this->flushValues();
    }
    
    protected function getValue($key) {
        throw new Exception(get_class($this) . ' doesn\'t support get() functionality');
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
    
    /* Implementing ArrayAccess */
    public function offsetExists($id)
    {
        return $this->get($id)!==false;
    }
    public function offsetGet($id)
    {
        return $this->get($id);
    }
    public function offsetSet($id, $value)
    {
        $this->set($id, $value);
    }
    public function offsetUnset($id)
    {
        $this->delete($id);
    }
}
?>
