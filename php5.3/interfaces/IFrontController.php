<?php

/**
 *
 * @author dmitrij
 */
namespace Libray\Controller;

interface IFrontController {
    public function setController($controller);
    public function setAction($action);
    public function setParams(array $params);
    public function run();
}

?>
