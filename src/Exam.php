<?php


namespace Jerryaicn\Word;


class Exam
{

    private $data = [];

    function __construct($data)
    {
        $this->data = $data;
    }

    function toArray(): array
    {
        return $this->data;
    }

    function hasError()
    {

    }

    function toHtml(): string
    {

        $html = "<div>";
        foreach ($this->data as $item) {
            $html .= "<div class='item'>";
            $html .= sprintf("<div class='title'><small class='type'>%s</small>%s</div>", $item["type"] ?? 'ERROR', $item["question"] ?? 'ERROR');
            if (isset($item["options"])) {
                foreach ($item["options"] as $option) {
                    if ($item['type'] == '多选题') {
                        $html .= sprintf("<div><input type='checkbox' %s>%s</div>", false !== stripos(($item["answer"] ?? 'ERROR'), substr($option, 0, 1)) ? 'checked' : '', $option);
                    } else {
                        $html .= sprintf("<div><input type='radio' %s>%s</div>", substr($option, 0, 1) == ($item["answer"] ?? 'ERROR') ? 'checked' : '', $option);
                    }
                }
            }
            $html .= sprintf("<div class='label'>答案</div><div class='answer'>%s</div>", $item["answer"] ?? 'ERROR');
            $html .= sprintf("<div class='label'>解析</div><div class='analysis'>%s</div>", $item["analysis"] ?? 'ERROR');
            $html .= "</div>";
        }
        $html .= "</div>";
        return $html;
    }
}