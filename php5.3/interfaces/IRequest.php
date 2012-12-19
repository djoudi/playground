<?php
namespace App\Library;

interface IRequest {
    public function getUri();
    public function setParam($key, $value);
    public function getParam($key);
    public function getParams();
}

?>
