<?php
/**
 * Created by PhpStorm.
 * User: longjinwen
 * Date: 2018/5/23
 * Time: 16:51
 */
set_time_limit(0);
date_default_timezone_set('PRC');
header("Content-type: text/html; charset=utf-8");

require './config.php';
require './class/Language.php';
require './class/TransApi.php';
$lang = new Language();
$type = $_REQUEST['type'];
switch ($type) {
    case 'getLang':
        echo json_encode($lang->getLang());
        break;
    case 'translate':
        echo json_encode($lang->translate());
        break;
    case 'getLast':
        echo json_encode($lang->getLast());
        break;
    case 'resetInput':
        echo json_encode($lang->resetInput());
        break;
    case 'autoTranslate':
        echo json_encode($lang->autoTranslate());
        break;
}
