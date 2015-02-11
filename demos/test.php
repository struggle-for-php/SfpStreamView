<?php
use Phly\Http\Stream;

require_once __DIR__.'/../vendor/autoload.php';

$view = new SfpStreamView\View('template.phtml', __DIR__);

$view->stackLayout('layout.phtml');
$view->stackLayout('parent_layout.phtml');

$view->title = 'SfpStreamView Example';

ob_start();
$fp = fopen('php://output', 'wb');
$fp = $view->render($fp);

var_dump(memory_get_peak_usage(true));
echo `ls -la /tmp`;
