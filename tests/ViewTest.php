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
        
        $template  = file_get_contents(__DIR__.'/_files/render1.phtml');
        $expected = str_replace('<?php ob_flush();?>', '', $template);

        $this->assertEquals($expected, stream_get_contents($fp, -1, 0));
    }

    public function testRenderResponse()
    {
        $response = new \Zend\Diactoros\Response;
        $view = new View(__DIR__.'/_files');
        $view->renderResponse('render1.phtml', $response);

        $template  = file_get_contents(__DIR__.'/_files/render1.phtml');
        $expected = str_replace('<?php ob_flush();?>', '', $template);

        $this->assertEquals($expected, (string)$response->getBody());
    }
}
