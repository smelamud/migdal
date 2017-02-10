<?php
# @(#) $Id$

require_once('lib/mtext-html.php');
require_once('lib/inner-images.php');

class LargeText {

    private $id;
    private $text_xml;
    private $text_html;
    private $footnotes_html;

    public function __construct($text_xml, $id) {
        $this->text_xml = $text_xml;
        $this->id = $id;
        $text = mtextToHTML($this->text_xml, MTEXT_LONG, $this->id, true,
                            new InnerImagesIterator($this->id));
        $this->text_html = $text['body'];
        $this->footnotes_html = $text['footnotes'];
    }

    public function getId() {
        return $this->id;
    }

    public function getTextXML() {
        return $this->text_xml;
    }

    public function getTextHTML() {
        return $this->text_html;
    }

    public function getFootnotesHTML() {
        return $this->footnotes_html;
    }

}
?>
