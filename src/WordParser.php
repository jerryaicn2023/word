<?php


namespace Jerryaicn\Word;

use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Footnote;
use PhpOffice\PhpWord\Element\Image;
use PhpOffice\PhpWord\Element\Link;
use PhpOffice\PhpWord\Element\PreserveText;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;


class WordParser
{

    private $debug = false;
    private $warning = [];
    /**
     * @var PhpWord
     */
    private $word;

    function setDebug($val)
    {
        $this->debug = $val;
    }


    function __construct($file)
    {
        $this->word = IOFactory::load($file);
    }


    private function log($message)
    {
        if ($this->debug) {
            error_log($message);
        }
    }

    public function getContentAsHtml(): string
    {
        $content = $this->getContentAsHtmlWithStyle();
        $content = preg_replace('/style="[^"]+"/', "", $content);
        $content = preg_replace("/lang=[^']+'/", "", $content);
        return $content;
    }

    private function getContentAsHtmlWithStyle(): string
    {
        try {
            $xmlWriter = IOFactory::createWriter($this->word, "HTML");
            if (!preg_match("#<body>([\S\s]+)</body>#", $xmlWriter->getContent(), $matches)) {
                return "";
            }
            return $matches[1];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    private function getTag($style): string
    {
        switch ($style) {
            case "1":
                $tag = 'h1';
                break;
            case "2":
                $tag = 'h2';
                break;
            case "3":
                $tag = 'h3';
                break;
            case "4":
                $tag = 'h4';
                break;
            case "5":
                $tag = 'h5';
                break;
            case "6":
                $tag = 'h6';
                break;
            case "7":
                $tag = 'h7';
                break;
            case "8":
                $tag = 'h8';
                break;
            default:
                $tag = 'p';
                break;
        }
        return $tag;
    }

    private function getStyle(AbstractElement $element)
    {
        if (
            $element instanceof Footnote
            || $element instanceof Link
            || $element instanceof PreserveText
            || $element instanceof Text
            || $element instanceof TextBreak
            || $element instanceof TextRun) {
            $paragraph = $element->getParagraphStyle();
            if (!$paragraph) {
                return null;
            }
            if (is_string($paragraph)) {
                return $paragraph;
            }
            return $paragraph->getStyleValues();
        }
        return get_class($element);
    }

    public function parse(): Outline
    {
        $return = [];
        $section = $this->word->getSection(0);
        foreach ($section->getElements() as $i => $element) {
            if ($element instanceof TextRun) {
                $text = [];
                foreach ($element->getElements() as $ele) {
                    $text [] = $this->element2html($ele);
                }
                $style = $this->getStyle($element);
                $return[$i] = $item = [
                    "tag" => $tag = $this->getTag($style['name'] ?? ''),
                    "content" => implode('', $text)
                ];
                $this->log("识别到TextRun:" . $element->getElementId());
            } else if ($element instanceof Table) {
                $this->log("识别到Table:" . $element->getElementId());
                $this->table2html($element);
            } else if ($element instanceof TextBreak) {
                $this->log("识别到TextBreak:" . $element->getElementId());
                $return[$i] = $item = [
                    "tag" => 'p',
                    "content" => ""
                ];
            } else {
                $this->warning[] = "忽略了" . get_class($element);
            }
        }
        return new Outline($return);
    }

    private function element2html($node): string
    {
        $return = '';
        if ($node instanceof Text) {
            $return .= $node->getText();
        } else if ($node instanceof Image) {
            $return .= $this->image2html($node);
        } else if ($node instanceof TextRun) {
            foreach ($node->getElements() as $ele) {
                $return .= $this->element2html($ele);
            }
        } else {
            $this->warning[] = $warn = sprintf("未处理的节点:%s", get_class($node));
        }
        return $return;
    }


    public function image2html(Image $image): string
    {
        $style = $image->getStyle();
        return sprintf(
            '<img width="%s" height="%s" src="data:%s;base64,%s" />',
            $style->getWidth(),
            $style->getHeight(),
            $image->getImageType(),
            $image->getImageStringData(true)
        );
    }

    /**
     * @param Table $element
     */
    private function table2html(Table $element)
    {
        $this->warning[] = "忽略了Table";
        $this->log("跳过表格解析" . get_class($element));
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
