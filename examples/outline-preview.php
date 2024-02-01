<?php
header('Content-Type: application/json; charset=utf-8');

use Jerryaicn\Word\ExamParser;

require "../vendor/autoload.php";


$_PUT = array();
if ('put' == strtolower($_SERVER['REQUEST_METHOD'])) {
    parse_str(file_get_contents('php://input'), $_PUT);
}
if (!isset($_PUT["raw"])) {
    echo json_encode(
        [
            "code" => [],
            "result" => "",
            "warning" => [
                "预览内容不能为空"
            ]
        ]
    );
    exit(0);
}
$outline = \Jerryaicn\Word\Outline::loadFromHtml($_PUT["raw"]);
echo json_encode([
    "code" => print_r($outline->getOutline(), true),
    "result" => $outline->toHtml(),
    "warning" => $outline->hasError() ? $outline->getError() : []
]);