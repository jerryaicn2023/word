<?php


namespace Jerryaicn\Word;

class ExamParser
{
    private $debug = false;
    private $warning = [];

    function setDebug($val)
    {
        $this->debug = $val;
    }


    private function getLines($html): array
    {
        if (!preg_match_all("@<p>([\S\s]*?)</p>@", $html, $matches)) {
            return [];
        }
        return $matches[1];
    }

    private function log($message)
    {
        if ($this->debug) {
            error_log($message);
        }
    }


    public function parseFromHtml($html): Exam
    {
        $this->log("收到原始内容：" . mb_substr($html, 0, 1000));
        $html = preg_replace('/style="[^"]+"/', "", $html);
        $html = preg_replace("/style='[^']+'/", "", $html);
        $html = preg_replace("/lang='[^']+'/", "", $html);
        $html = preg_replace("/<span[\s]*>/", "", $html);
        $html = preg_replace("|</span>|", "", $html);
        $html = str_replace("<br />", "</p><p>", $html);
        $html = str_replace("<p >", "<p>", $html);
        $html .= '<p>&nbsp;</p>';
        $this->log("格式化后的内容：" . mb_substr($html, 0, 1000));
        $lastAction = "";
        $rows = [];
        $item = [];
        $itemCount = 0;
        $lines = $this->getLines($html);
        $total = count($lines);
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (in_array($line, ['&nbsp;', ''])) {
                if (empty($item) || !isset($item['type']) || !isset($item['question'])) {
                    $this->log("在没有解析到题型和题干这前，所有的空行都忽略");
                }
                if ($total - $i == 1) {
                    if (!empty($item)) {
                        $this->log("最后一题");
                        $rows[] = $item;
                        if (!isset($item['type'])
                            || !isset($item['question'])
                            || !isset($item['answer'])
                            || !isset($item['analysis'])
                        ) {
                            $warn = "最后一道题解析不全，{$i}请检查{$total}";
                            $this->warning[] = $warn;
                            $this->log($warn);
                        }
                        $item = [];
                    }
                }

            } elseif (preg_match('/^\d+\.[\[](单选题|多选题|判断题|问答题|简答题)[\]].*/', $line, $matches)) {
                if (!empty($item)) {
                    $this->log("结束上一题");
                    $itemCount++;
                    if (!isset($item['type'])
                        || !isset($item['question'])
                        || !isset($item['answer'])
                        || !isset($item['analysis'])
                    ) {
                        $warn = (isset($item['question']) ? $item['question'] : "第{$itemCount}道试题") . "解析不全，请检查";
                        $this->warning[] = $warn;
                        $this->log($warn);
                    }
                    $rows[] = $item;
                    $item = [];
                }
                $this->log("发现question和type:" . mb_substr($line, 0, 30));
                $item["type"] = $lastType = $matches[1];
                $item["question"] = str_replace('[' . $item["type"] . ']', '', $line);
                $lastAction = "question";
            } elseif (preg_match("/^[a-zA-Z]+./", $line)) {
                $this->log("发现选项:" . mb_substr($line, 0, 30));
                $item["options"][] = $line;
            } elseif
            (mb_strpos($line, "答案") === 0) {
                $lastAction = "answer";
                $this->log("发现answer:" . mb_substr($line, 0, 30));
                $exploded = explode("：", $line);
                $item["answer"] = $exploded[1] ?? "";
                $exploded = explode(":", $line);
                $item["answer"] .= $exploded[1] ?? "";
            } elseif
            (mb_stripos($line, "解析") === 0) {
                $this->log("发现analysis:" . mb_substr($line, 0, 30));
                $lastAction = "analysis";
                $exploded = explode("：", $line);
                $item["analysis"] = $exploded[1] ?? "";
                if (!$item["analysis"]) {
                    $exploded = explode(":", $line);
                    $item["analysis"] = $exploded[1] ?? "";
                }
            } else {
                switch ($lastAction) {
                    case "answer":
                        $item["answer"] .= $line;
                        $this->log("追加到answer:" . mb_substr($line, 0, 30));
                        break;
                    case "analysis":
                        $this->log("追加到analysis:" . mb_substr($line, 0, 30));
                        isset($item['analysis']) ? $item["analysis"] .= $line : $item["analysis"] = $line;
                        break;
                    case "question":
                        $this->log("追加到question:" . mb_substr($line, 0, 30));
                        $item["question"] .= $line;
                        break;
                    default:
                        $this->log("发现无效内容:" . mb_substr($line, 0, 30));
                        break;
                }
            }
        }
        return new Exam($rows);
    }

    public
    function hasError(): bool
    {
        return count($this->warning) > 0;
    }

    public
    function getError(): array
    {
        return $this->warning;
    }
}
