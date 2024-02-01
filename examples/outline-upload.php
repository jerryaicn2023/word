<?php
header('Content-Type: application/json; charset=utf-8');
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
    $outline = $wordParser->parse();

    echo json_encode([
        "code" => print_r($outline->getOutline(), true),
        "result" => $outline->toHtml(),
        "raw" => $outline->toHtml(),
        "warning" => $outline->hasError()?$outline->getError():[]
    ]);
}