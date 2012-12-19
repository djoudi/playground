<?php
namespace App\Library;

interface IRouter {
    public function addRoute(IRoute $route);
    public function addRoutes(array $routes);
    public function getRoutes();
    public function route(IRequest $request, IResponse $response);
}

?>
