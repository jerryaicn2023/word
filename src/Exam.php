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

        $html = "<style>
        .question {
            border: 1px solid #639;
            padding: 10px;
            margin: 10px
        }

        .question-title {
            font-weight: 700
        }

        .question-type {
            border: 1px solid #00f;
            font-size: 92%;
        }

        .question-label {
            font-weight: 700
        }

        .question-answer {

        }

        .question-analysis {

        }
    </style><div>";
        foreach ($this->data as $item) {
            $html .= "<div class='question'>";
            $html .= sprintf("<div class='question-title'><small class='question-type'>%s</small>%s</div>", $item["type"] ?? 'ERROR', $item["question"] ?? 'ERROR');
            if (isset($item["options"])) {
                foreach ($item["options"] as $option) {
                    if ($item['type'] == '多选题') {
                        $html .= sprintf("<div><input type='checkbox' %s>%s</div>", false !== stripos(($item["answer"] ?? 'ERROR'), substr($option, 0, 1)) ? 'checked' : '', $option);
                    } else {
                        $html .= sprintf("<div><input type='radio' %s>%s</div>", substr($option, 0, 1) == ($item["answer"] ?? 'ERROR') ? 'checked' : '', $option);
                    }
                }
            }
            $html .= sprintf("<div class='question-label'>答案</div><div class='question-answer'>%s</div>", $item["answer"] ?? 'ERROR');
            $html .= sprintf("<div class='question-label'>解析</div><div class='question-analysis'>%s</div>", $item["analysis"] ?? 'ERROR');
            $html .= "</div>";
        }
        $html .= "</div>";
        return $html;
    }
}