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


    private function getLines($html)
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

    /**
     * @param $html
     * @return Exam
     */
    public function parseFromHtml($html)
    {
        $this->log(mb_substr($html, 0, 200));
        $html = preg_replace('/style="[^"]+"/', "", $html);
        $html = preg_replace("/style='[^']+'/", "", $html);
        $html = preg_replace("/lang='[^']+'/", "", $html);
        $html = preg_replace("/<span[\s]*>/", "", $html);
        $html = preg_replace("|</span>|", "", $html);
        $this->log(mb_substr($html, 0, 200));
        //Log::log("info", $html);
        $lastType = "";
        $lastAction = "";
        $types = ["单选题", "多选题", "判断题", "问答题"];
        $rows = [];
        $item = [];
        $brCount = 0;
        foreach ($this->getLines($html) as $line) {
            $line = trim($line);
            if (in_array($line, ['&nbsp;', ''])) {
                $brCount++;
                if (!key_exists("analysis", $item)) {
                    $this->log("空行做为题目的结束标志,仅应该出现在解析之后");
                }
                if (!key_exists("question", $item)) {
                    $this->log("空行之前没有发现题干,无效的空行");
                } elseif (!key_exists("answer", $item)) {
                    $this->log("空行之前没有发现答案,无效的空行");
                } else {
                    $this->log("下一道题");
                    $rows[] = $item;
                    $item = [];
                    $brCount = 0;
                }
            } elseif (in_array($line, $types)) {
                $this->log("发现type:" . mb_substr($line, 0, 30));
                $lastType = $item['type'] = $line;
            } elseif ("*" === substr($line, 0, 1)) {
                $this->log("发现question:" . mb_substr($line, 0, 30));
                $item["question"] = substr($line, 1);
                if (!isset($item["type"])) {
                    $item["type"] = $lastType;
                }
                $lastAction = "question";
            } elseif (preg_match("/^[a-zA-Z]{1,}./", $line)) {
                $this->log("发现选项:" . mb_substr($line, 0, 30));
                $item["options"][] = $line;
            } elseif (mb_strpos($line, "答案") === 0) {
                $lastAction = "answer";
                $this->log("发现answer:" . mb_substr($line, 0, 30));
                $exploded = explode("：", $line);
                $item["answer"] = $exploded[1] ?? "";
                $exploded = explode(":", $line);
                $item["answer"] .= $exploded[1] ?? "";
            } elseif (mb_stripos($line, "解析") === 0) {
                $this->log("发现analysis:" . mb_substr($line, 0, 30));
                $lastAction = "analysis";
                $exploded = explode("：", $line);
                $item["analysis"] = $exploded[1] ?? "";
                $exploded = explode(":", $line);
                $item["analysis"] = $exploded[1] ?? "";
            } else {
                switch ($lastAction) {
                    case "answer":
                        $item["answer"] .= $line;
                        $this->log("追加到answer:" . mb_substr($line, 0, 30));
                        break;
                    case "analysis":
                        $this->log("追加到analysis:" . mb_substr($line, 0, 30));
                        $item["analysis"] .= $line;
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

    public function hasError(): bool
    {
        return count($this->warning) > 0;
    }

    public function getError(): array
    {
        return $this->warning;
    }
}
