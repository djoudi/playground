<?php
namespace App\Library;

interface IRoute {
    public function match(IRequest $request);
    public function createController();
}

?>
