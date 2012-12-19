<?php
namespace App\Library;

interface IResponse {
    public function getVersion();
    public function addHeader($header);
    public function addHeaders(array $headers);
    public function getHeaders();
    public function send();
}

?>
