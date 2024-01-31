<?php

use Jerryaicn\Word\ExamParser;

require "../vendor/autoload.php";


$_PUT = array();
if ('put' == strtolower($_SERVER['REQUEST_METHOD'])) {
    parse_str(file_get_contents('php://input'), $_PUT);
}
if (!isset($_PUT["raw"])) {
    exit("dd");
}
$examParser = new ExamParser();
$examParser->setDebug(true);
$result = $examParser->parseFromHtml($_PUT["raw"]);
echo json_encode([
    "code" => print_r($result->toArray(), true),
    "result" => $result->toHtml()
]);