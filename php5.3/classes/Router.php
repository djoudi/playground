<?php
namespace App\Library;

class Router implements IRouter {
    protected $routes = array();
    
    public function __construct(array $routes = array()) {
        if (!empty($routes)) {
            $this->addRoutes($routes);
        }
    }
    
    public function addRoute(IRoute $route) {
        $this->routes[] = $route;
        return $this;
    }
    
    public function addRoutes(array $routes) {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
        return $this;
    }
    
    public function getRoutes() {
        return $this->routes;
    }
    
    public function route(IRequest $request, IResponse $response) {
        foreach ($this->routes as $route) {
            if ($route->match($request)) {
                return $route;
            }
        }
        $response->addHeader("404 Page Not Found")->send();
        throw new \OutOfRangeException("No route matched the given URI.");
    }
}

?>
