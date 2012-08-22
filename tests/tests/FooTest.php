<?php
class Foo {
    public function bar($baz) {
        return $baz;
    }
}
class FooTest extends PHPUnit_Framework_TestCase {
    protected $f;
    
    protected function setUp() {
        $this->f = new Foo();
    }
    public function testBar() {
        $this->assertEquals('test', $this->f->bar('test'));
    }
}
?>
