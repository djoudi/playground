<?php
namespace App\Library;

class Request implements IRequest {
    protected $uri;
    protected $params = array();
   
    public function __construct($uri, array $params = array()) {
        if(!filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("The uri is invalid");
        }
        $this->uri = $uri;
        $this->params = $params;
    }
    
    public function getUri() {
        return $this->uri;
    }
    
    public function setParam($key, $value) {
        $this->params[$key] = $value;
        return $this;
    }
    
    public function getParam($key) {
        if(!isset($this->params[$key])) {
            throw new \InvalidArgumentException("The request paramert '$key' is invalid.");
        }
        
        return $this->params[$key];
    }
    
    public function getParams() {
        return $this->params;
    }
}

?>
