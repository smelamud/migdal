<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/xml.php');
require_once('lib/mtext-shorten.php');
require_once('lib/callbacks.php');
require_once('lib/mtext-callback-data.php');
require_once('lib/text.php');
require_once('lib/inner-images.php');
require_once('lib/mtext-tags.php');

class InnerImageBlock {

    private $sizeX = 0;
    private $sizeY = 0;
    private $placement = IPL_CENTER;
    private $images = array();

    public function getSizeX() {
        return $this->sizeX;
    }

    public function setSizeX($sizeX) {
        $this->sizeX = $sizeX;
    }

    public function getSizeY() {
        return $this->sizeY;
    }

    public function setSizeY($sizeY) {
        $this->sizeY = $sizeY;
    }

    public function getPlacement() {
        return $this->placement;
    }

    public function isPlaced($place) {
        $hplace = $place & IPL_HORIZONTAL;
        $h = $hplace == 0 || ($this->placement & IPL_HORIZONTAL) == $hplace;
        $vplace = $place & IPL_VERTICAL;
        $v = $vplace == 0 || ($this->placement & IPL_VERTICAL) == $vplace;
        return $h && $v;
    }

    public function setPlacement($placement) {
        $this->placement = $placement;
    }

    public function addImage($image) {
        $this->images[] = $image;
    }

    public function getImage() {
        return isset($this->images[0]) ? $this->images[0] : null;
    }

}

class MTextToHTMLXML
        extends XMLParser {

    private $html;
    private $htmlBody = '';
    private $inFootnote = false;
    private $footnoteTitle;
    private $htmlFootnote;
    private $xmlFootnote;
    private $htmlFootnotes = '';
    private $format;
    private $id;
    private $listFonts = array('i');
    private $listStyles = array();
    private $noteNo = 1;
    private $imageBlocks = array();
    private $par = 0;
    private $inHx = false;
    private $brInHx = false;

    public function __construct($format, $id, $imageBlocks = array()) {
        parent::__construct();
        $this->format = $format;
        $this->id = $id;
        $this->html =& $this->htmlBody;
        $this->imageBlocks = $imageBlocks;
    }

    protected function xmlError($message) {
        $this->html .= "<b>$message</b>";
    }

    public function parse($body) {
        global $mtextRootTag;

        parent::parse($mtextRootTag[$this->format], $body);
        $this->htmlBody = delicateAmps($this->htmlBody, false);
    }

    public function getBodyHTML() {
        return $this->htmlBody;
    }

    public function getFootnotesHTML() {
        return $this->htmlFootnotes;
    }

    private function getInplaceFootnote() {
        global $inplaceSize, $inplaceSizeMinus, $inplaceSizePlus;

        return strtr(shortenNote($this->xmlFootnote, $inplaceSize,
                                 $inplaceSizeMinus, $inplaceSizePlus),
                     "\n", ' ');
    }

    private function getParagraphClear() {
        if ($this->format < MTEXT_LONG)
            return 'none';
        if (!isset($this->imageBlocks[$this->par]))
            return 'none';
        $block = $this->imageBlocks[$this->par];
        if ($block->isPlaced(IPL_CENTERLEFT))
            return 'left';
        if ($block->isPlaced(IPL_CENTERRIGHT))
            return 'right';
        return 'none';
    }

    private function putImage($image, $par) {
        if (!is_null($image)) {
            if ($image->isPlaced(IPL_LEFT))
                $align='left';
            if ($image->isPlaced(IPL_HCENTER))
                $align='center';
            if ($image->isPlaced(IPL_RIGHT))
                $align='right';
            $data = new ImageCallbackData($this->id, $par,
                                          $image->getImage(), $align);
        } else {
            $data = new ImageCallbackData($this->id, $par);
        }
        $this->html .= callback('image', $data);
    }

    private function putImageBlock() {
        if ($this->format < MTEXT_LONG)
            return;

        $par = $this->par;
        $this->par++;
        if (!isset($this->imageBlocks[$par])) {
            $image = null;
        } else {
            $block = $this->imageBlocks[$par];
            $image = $block->getImage();
        }

        $this->putImage($image, $par);
    }

    private function verifyURL($url) {
        if (strpos(strtolower($url), 'javascript:') !== false)
            return '';
        return $url;
    }

    protected function startElement($parser, $name, $attrs) {
        global $mtextTagLevel;

        if (!isset($mtextTagLevel[$name])
            || $mtextTagLevel[$name] > $this->format) {
            $this->html .= "<b>** &lt;$name&gt; **</b>";
            return;
        }
        if ($this->inFootnote)
            $this->xmlFootnote .= makeTag($name, $attrs);
        switch ($name) {
            case 'MTEXT-LINE':
            case 'MTEXT-SHORT':
            case 'MTEXT-LONG':
                break;

            case 'A':
                if (!isset($attrs['HREF'])) {
                    $this->html .= '<b>&lt;A HREF?&gt;</b>';
                    break;
                }
                $href = $this->verifyURL($attrs['HREF']);
                if (!isset($attrs['LOCAL']) || $attrs['LOCAL'] == 'false')
                    $this->html .= makeTag($name,
                        array('href' => '/actions/link/'.$this->id
                                        .'?okdir='.urlencode($href)));
                else
                    $this->html .= makeTag($name, array('href' => $href));
                break; 

            case 'EMAIL':
                if (!isset($attrs['ADDR'])) {
                    $this->html .= '<b>&lt;EMAIL ADDR?&gt;</b>';
                    break;
                }
                $this->html .= makeTag('a',
                    array('href' => 'mailto:'.$attrs['ADDR']));
                $this->html .= makeText($attrs['ADDR']);
                $this->html .= makeTag('/a');
                break;

            case 'USER':
                if (!isset($attrs['NAME']) && !isset($attrs['GUEST-NAME'])) {
                    $this->html .= '<b>&lt;USER NAME?&gt;</b>';
                    break;
                }
                if (isset($attrs['NAME']))
                    $data = new UserNameCallbackData(
                        false, makeText($attrs['NAME'])
                    );
                else
                    $data = new UserNameCallbackData(
                        true, makeText($attrs['GUEST-NAME'])
                    );
                $this->html .= callback('user_name', $data);
                break;

            case 'H2':
            case 'H3':
            case 'H4':
                $this->html .= makeTag($name, $attrs);
                $this->inHx = true;
                $this->brInHx = false;
                break;

            case 'BR':
                $this->html .= makeTag($name, $attrs, true);
                if ($this->inHx && !$this->brInHx) {
                    $this->brInHx = true;
                    $this->html .= makeTag('span',
                        array('class' => 'subheading'));
                }
                break;

            case 'P':
                $clear = $this->getParagraphClear();
                $this->putImageBlock();
                $clear = isset($attrs['CLEAR']) ? $attrs['CLEAR'] : $clear;
                if ($clear == 'none')
                    $this->html .= makeTag($name);
                else
                    $this->html .= makeTag($name,
                        array('style' => "clear: $clear"));
                break;

            case 'LI':
                if (count($this->listStyles) == 0) {
                    $this->html .= '<b>&lt;LI?&gt;</b>';
                    break;
                }
                if ($this->listStyles[0] != 'd')
                    $this->html .= makeTag($name);
                if (isset($attrs['TITLE'])) {
                    if ($this->listStyles[0] != 'd') {
                        $this->html .= makeTag($this->listFonts[0]);
                        $this->html .= makeText($attrs['TITLE']);
                        $this->html .= makeTag('/'.$this->listFonts[0]);
                        $this->html .= ' ';
                    } else {
                        $this->html .= makeTag('dt');
                        $this->html .= makeTag($this->listFonts[0]);
                        $this->html .= makeText($attrs['TITLE']);
                        $this->html .= makeTag('/'.$this->listFonts[0]);
                        $this->html .= makeTag('/dt');
                    }
                }
                if ($this->listStyles[0] == 'd')
                    $this->html .= makeTag('dd');
                break;

            case 'UL':
                array_unshift($this->listStyles, 'u');
                array_unshift($this->listFonts, 'i');
                $this->html .= makeTag($name, $attrs);
                break;

            case 'OL':
                array_unshift($this->listStyles, 'o');
                array_unshift($this->listFonts, 'i');
                $this->html .= makeTag($name, $attrs);
                break;

            case 'DL':
                array_unshift($this->listStyles, 'd');
                array_unshift($this->listFonts,
                        !isset($attrs['FONT']) ? 'b' : $attrs['FONT']);
                $this->html .= makeTag($name, $attrs);
                break;

            case 'QUOTE':
                $this->html .= makeTag('div', array('class' => 'quote'));
                break;

            case 'FOOTNOTE':
                $this->htmlFootnote = '';
                $this->xmlFootnote = '';
                $this->html =& $this->htmlFootnote;
                $this->inFootnote = true;
                $this->html .= makeTag('a',
                    array('href'  => '#_ref'.$this->noteNo));
                if (!isset($attrs['TITLE'])) {
                    $this->footnoteTitle = '';
                    $this->html .= makeTag('sup');
                    $this->html .= $this->noteNo;
                    $this->html .= makeTag('/sup');
                    $this->html .= makeTag('/a');
                } else {
                    $this->footnoteTitle = $attrs['TITLE'];
                    $this->html .= $this->footnoteTitle;
                    $this->html .= makeTag('/a');
                    $this->html .= ' &mdash; ';
                }
                $this->html .= makeTag('a',
                    array('name' => '_note'.$this->noteNo));
                $this->html .= makeTag('/a');
                break;

            case 'INCUT':
                $data=new IncutCallbackData(
                        isset($attrs['ALIGN']) ? $attrs['ALIGN'] : 'right',
                        isset($attrs['WIDTH']) ? $attrs['WIDTH'] : '50%');
                $this->html .= callback('incut', $data);
                break;

            default:
                $this->html .= makeTag($name, $attrs);
        }
    }

    protected function endElement($parser, $name) {
        global $mtextTagLevel;

        if (!isset($mtextTagLevel[$name])
            || $mtextTagLevel[$name] > $this->format) {
            $this->html .= "<b>** &lt;/$name&gt; **</b>";
            return;
        }
        switch ($name) {
            case 'MTEXT-LINE':
            case 'MTEXT-SHORT':
            case 'MTEXT-LONG':
            case 'BR':
                break;

            case 'H2':
            case 'H3':
            case 'H4':
                if ($this->brInHx)
                    $this->html .= makeTag("/span");
                $this->inHx = false;
                $this->brInHx = false;
                $this->html .= makeTag("/$name");
                break;

            case 'LI':
                if (count($this->listStyles) == 0) {
                    $this->html .= '<b>&lt;/LI?&gt;</b>';
                    break;
                }
                if ($this->listStyles[0] != 'd')
                    $this->html .= makeTag("/$name");
                else
                    $this->html .= makeTag('/dd');
                break;

            case 'UL':
            case 'OL':
            case 'DL':
                array_shift($this->listStyles);
                array_shift($this->listFonts);
                $this->html .= makeTag("/$name");
                break;

            case 'QUOTE':
                $this->html .= makeTag('/div');
                break;

            case 'FOOTNOTE':
                $this->html .= makeTag('br', array(), true);
                $this->htmlFootnotes .= $this->htmlFootnote;
                $this->html =& $this->htmlBody;
                $this->inFootnote = false;
                $this->html .= makeTag('a',
                    array('name' => '_ref'.$this->noteNo));
                $this->html .= makeTag('/a');
                if($this->footnoteTitle == '') {
                    $this->html .= makeTag('sup');
                    $this->html .= makeTag('a',
                        array('href'  => '#_note'.$this->noteNo,
                              'title' => $this->getInplaceFootnote()));
                    $this->html .= $this->noteNo;
                    $this->html .= makeTag('/a');
                    $this->html .= makeTag('/sup');
                } else {
                    $this->html .= makeTag('a',
                        array('href'  => '#_note'.$this->noteNo,
                              'title' => $this->getInplaceFootnote()));
                    $this->html .= makeText($this->footnoteTitle);
                    $this->html .= makeTag('/a');
                }
                $this->noteNo++;
                break;

            case 'INCUT':
                $this->html .= callback('_incut', null);
                break;

            default:
                $this->html .= makeTag("/$name");
        }
        if ($this->inFootnote)
            $this->xmlFootnote .= makeTag("/$name");
    }

    protected function characterData($parser, $data) {
        $s = makeText($data);
        $this->html .= $s;
        if ($this->inFootnote)
            $this->xmlFootnote .= $s;
    }

}

function getImageBlocks($iterator) {
    $blocks = array();
    if (is_null($iterator))
        return $blocks;
    foreach ($iterator as $image) {
        $par = $image->getPar();
        if (!isset($blocks[$par]))
            $blocks[$par] = new InnerImageBlock();
        $block = $blocks[$par];
        $block->addImage($image);
        $block->setSizeX(max($block->getSizeX(), $image->getX() + 1));
        $block->setSizeY(max($block->getSizeY(), $image->getY() + 1));
        $block->setPlacement($image->getPlacement());
    }
    return $blocks;
}

function mtextToHTML($body, $format = MTEXT_LINE, $id = 0,
                     $showFootnotes = false, $iterator = null) {
    $xml = new MTextToHTMLXML($format, $id, getImageBlocks($iterator));
    $xml->parse(convertToXMLText($body));
    $xml->free();
    return $showFootnotes ? array('body'      => $xml->getBodyHTML(),
                                  'footnotes' => $xml->getFootnotesHTML())
                          : $xml->getBodyHTML();
}
?>
