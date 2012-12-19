<?php
namespace App\Library;

class Response implements IResponse {
    const VERSION_11 = "HTTP/1.1";
    const VERSION_10 = "HTTP/1.0";
    
    protected $version;
    protected $headers = array();
    
    public function __construct($version = self::VERSION_11) {
        $this->version = $version;
    }
    
    public function getVersion() {
        return $this->version;
    }
    
    public function addHeader($header) {
        $this->headers[] = $header;
        return $this;
    }
    
    public function addHeaders(array $headers) {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }
        return $this;
    }
    
    public function getHeaders() {
        return $this->headers;
    }
    
    public function send() {
        if (!headers_sent()) {
            foreach($this->headers as $header) {
                header("$this->version $header", true);
            }
        } 
    }
}

?>
