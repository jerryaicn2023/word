<?php
header('Content-Type: application/json; charset=utf-8');

use Jerryaicn\Word\ExamParser;
use Jerryaicn\Word\WordParser;

require "../vendor/autoload.php";
if (!isset($_FILES['file']['tmp_name']) || !$_FILES['file']['tmp_name']) {
    echo json_encode(
        [
            "code" => [],
            "result" => "",
            "warning" => [
                "请选择上传文件"
            ]
        ]
    );
} else {

    $wordParser = new WordParser($_FILES['file']['tmp_name']);
    $wordParser->setDebug(true);
    $examParser = new ExamParser();
    $examParser->setDebug(true);
    $raw = $wordParser->getContentAsHtml();
    $result = $examParser->parseFromHtml($raw);

    echo json_encode([
        "code" => print_r($result->toArray(), true),
        "result" => $result->toHtml(),
        "raw" => $raw,
        "warning" => $examParser->hasError() ? $examParser->getError() : []
    ]);
}