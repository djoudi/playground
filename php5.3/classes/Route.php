<?php
namespace App\Library;

class Route implements IRoute {
    protected $path;
    protected $controllerClass;

    public function __construct($path, $controllerClass) {
        if (!is_string($path) || empty($path)) {
            throw new \InvalidArgumentException("The path is invalid.");
        }
        if (!class_exists($controllerClass)) {
            throw new \InvalidArgumentException("The controller class is invalid.");
        }
        $this->path = $path;
        $this->controllerClass = $controllerClass;
    }
    
    public function match(IRequest $request) {
        return $this->path === $request->getUri();
    }
    
    public function createController() {
        return new $this->controllerClass;
    }
}

?>
