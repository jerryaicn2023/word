<?php


namespace Jerryaicn\Word;

use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;


class WordParser
{

    private $debug = false;

    function setDebug($val)
    {
        $this->debug = $val;
    }


    function __construct($file)
    {
        $this->word = IOFactory::load($file);
    }


    public function getContentAsHtml(): string
    {
        $content = $this->getContentAsHtmlWithStyle();
        $content = preg_replace('/style="[^"]+"/', "", $content);
        $content = preg_replace("/lang=[^']+'/", "", $content);
        return $content;
    }

    private function getContentAsHtmlWithStyle()
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
}
