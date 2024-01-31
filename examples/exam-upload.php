<?php

use Jerryaicn\Word\ExamParser;
use Jerryaicn\Word\WordParser;

require "../vendor/autoload.php";
if (!isset($_FILES['file']['tmp_name']) || !$_FILES['file']['tmp_name']) {
    echo "请选择上传文件";
    exit(0);
}
$wordParser = new WordParser($_FILES['file']['tmp_name']);
$wordParser->setDebug(true);
$examParser = new ExamParser();
$examParser->setDebug(true);
$raw = $wordParser->getContentAsHtml();
$result = $examParser->parseFromHtml($raw);

echo json_encode([
    "code" => print_r($result->toArray(), true),
    "result" => $result->toHtml(),
    "raw" => $raw
]);