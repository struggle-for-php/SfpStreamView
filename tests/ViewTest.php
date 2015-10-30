<?php

namespace SfpStreamViewTest;

use SfpStreamView\View;
use Backbeard\RouteMatch;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $view = new View(__DIR__.'/_files');
        $fp = fopen('php://memory', 'r+b');
        $view->render('render1.phtml', $fp);
        $this->assertEquals(file_get_contents(__DIR__.'/_files/render1.phtml'), stream_get_contents($fp, -1, 0));
    }

}
