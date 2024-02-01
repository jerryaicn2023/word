<?php


namespace Jerryaicn\Word;


use DOMDocument;

class Outline
{

    private $content = [];
    private $outline = [];
    private $warning = [];

    function __construct($lines)
    {
        $h = [];
        foreach ($lines as $i => $line) {
            $this->content[] = $line;
            if (stripos($line["tag"], 'h') === 0) {
                $h[$i] = $line["tag"];
            }
        }
        $tags = array_unique(array_values($h));
        sort($tags);
        foreach ($h as $i => $v) {
            $index = array_search($v, $tags);
            if ($index < 8) {
                $this->content[$i]["tag"] = "h" . ($index + 1);
                $this->outline[] = $this->content[$i];
            } else {
                $this->content[$i]["tag"] = "p";
            }
        }
    }

    static function loadFromHtml($html): Outline
    {
        $reg = "/<([a-z]+[1-6]?)\b[^>]*>(.*?)<\/[a-z]+[1-6]?>/i";
        preg_match_all($reg, $html, $matches);
        $lines = [];
        foreach ($matches[2] as $i => $line) {
            $lines[] = [
                "tag" => $matches[1][$i],
                "content" => $line
            ];
        }
        return new self($lines);
    }

    function toArray(): array
    {
        return $this->content;
    }

    public function getOutline(): array
    {
        return $this->outline;
    }

    public function toHtml(): string
    {
        $html = "";
        foreach ($this->content as $line) {
            $html .= $this->_toHtml($line);
        }
        return $html;
    }

    private function _toHtml($line): string
    {
        return sprintf('<%s>%s</%s>',
            $line['tag'],
            $line['content'],
            $line['tag']
        );
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