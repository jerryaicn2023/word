<?php


namespace Jerryaicn\Word;


class Exam
{

    private $data = [];
    function __construct($data)
    {
        $this->data = $data;
    }

    function toArray()
    {
        return $this->data;
    }

    function hasError(){

    }

    function toHtml()
    {

        $html = "<article>";
        foreach ($this->data as $item) {
            $html .= sprintf("<h3>(%s)%s</h3>", $item["type"], $item["question"]);
            if (isset($item["options"])) {
                foreach ($item["options"] as $option) {
                    $html .= sprintf("<div>%s</div>", $option);
                }

            }

            $html .= sprintf("<h4>答案</h4><p>%s</p>", $item["answer"]);
            $html .= sprintf("<h4>解析</h4><p>%s</p>", $item["analysis"]);
        }
        $html .= "<article>";
        return $html;
    }
}