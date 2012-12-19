<?php
namespace App\Library;

interface IDispatcher {
    public function dispatch(IRoute $route, IRequest $request, IResponse $response);
}

?>
