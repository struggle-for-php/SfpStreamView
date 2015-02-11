<?php
use Phly\Http\Stream;

require_once __DIR__.'/../vendor/autoload.php';

$view = new SfpStreamView\View('template.phtml', __DIR__);

$view->stackLayout('layout.phtml');
$view->stackLayout('parent_layout.phtml');

$view->title = 'SfpStreamView Example';

$fp = fopen("php://temp/maxmemory:". 1 * 1024 * 1024, 'wb+');
$fp = $view->render($fp);

rewind($fp); fpassthru($fp);
var_dump(memory_get_peak_usage(true));
echo `ls -la /tmp`;
