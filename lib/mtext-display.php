<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/xml.php');
require_once('lib/mtext-shorten.php');
require_once('lib/callbacks.php');

define('MTEXT_LINE',1);
define('MTEXT_SHORT',2);
define('MTEXT_LONG',3);

$mtextRootTag=array(MTEXT_LINE  => 'mtext-line',
                    MTEXT_SHORT => 'mtext-short',
		    MTEXT_LONG  => 'mtext-long');
$mtextTagLevel=array('MTEXT-LINE' => MTEXT_LINE,
                     'A'        => MTEXT_LINE,
                     'EMAIL'    => MTEXT_LINE,
                     'B'        => MTEXT_LINE,
                     'I'        => MTEXT_LINE,
                     'U'        => MTEXT_LINE,
                     'TT'       => MTEXT_LINE,
                     'SUP'      => MTEXT_LINE,
		     'MTEXT-SHORT' => MTEXT_SHORT,
                     'P'        => MTEXT_SHORT,
                     'QUOTE'    => MTEXT_SHORT,
                     'CENTER'   => MTEXT_SHORT,
                     'H2'       => MTEXT_SHORT,
                     'H3'       => MTEXT_SHORT,
                     'H4'       => MTEXT_SHORT,
                     'BR'       => MTEXT_SHORT,
                     'UL'       => MTEXT_SHORT,
                     'OL'       => MTEXT_SHORT,
                     'DL'       => MTEXT_SHORT,
                     'LI'       => MTEXT_SHORT,
		     'MTEXT-LONG' => MTEXT_LONG,
                     'FOOTNOTE' => MTEXT_LONG,
                     'IMG'      => MTEXT_LONG,
                     'TABLE'    => MTEXT_LONG,
                     'TR'       => MTEXT_LONG,
                     'TD'       => MTEXT_LONG,
                     'TH'       => MTEXT_LONG);

class MTextToHTMLXML
      extends XMLParser
{
var $html;
var $htmlBody='';
var $inFootnote=false;
var $footnoteTitle;
var $htmlFootnote;
var $xmlFootnote;
var $htmlFootnotes='';
var $format;
var $id;
var $listFonts=array('i');
var $listStyles=array();
var $noteNo=1;

function MTextToHTMLXML($format,$id)
{
$this->XMLParser();
$this->format=$format;
$this->id=$id;
$this->html=& $this->htmlBody;
}

function xmlError($message)
{
$this->html.="<b>$message</b>";
}

function parse($body)
{
global $mtextRootTag;

parent::parse($mtextRootTag[$this->format],$body);
}

function getBodyHTML()
{
return $this->htmlBody;
}

function getFootnotesHTML()
{
return $this->htmlFootnotes;
}

function getInplaceFootnote()
{
global $inplaceSize,$inplaceSizeMinus,$inplaceSizePlus;

return strtr(shortenNote($this->xmlFootnote,$inplaceSize,
                         $inplaceSizeMinus,$inplaceSizePlus),"\n",' ');
}

function startElement($parser,$name,$attrs)
{
global $mtextTagLevel;

if(!isset($mtextTagLevel[$name]) || $mtextTagLevel[$name]>$this->format)
  {
  $this->html.="<b>** &lt;$name&gt; **</b>";
  return;
  }
if($this->inFootnote)
  $this->xmlFootnote.=makeTag($name,$attrs);
switch($name)
      {
      case 'MTEXT-LINE':
      case 'MTEXT-SHORT':
      case 'MTEXT-LONG':
           break;
      case 'A':
	   if(!isset($attrs['HREF']))
	     {
	     $this->html.='<b>&lt;A HREF?&gt;</b>';
	     break;
	     }
           if(!isset($attrs['LOCAL']) || $attrs['LOCAL']=='false')
	     $this->html.=makeTag($name,
			    array('href' => 'actions/link.php?id='.$this->id.
					    '&okdir='.urlencode($attrs{'HREF'}),
				  'target' => '_blank'));
	   else
             $this->html.=makeTag($name,array('href' => $attrs{'HREF'}));
           break; 
      case 'EMAIL':
	   if(!isset($attrs['ADDR']))
	     {
	     $this->html.='<b>&lt;EMAIL ADDR?&gt;</b>';
	     break;
	     }
           $this->html.=makeTag('a',array('href' => 'mailto:'.$attrs['ADDR']));
           $this->html.=$attrs['ADDR'];
	   $this->html.=makeTag('/a');
	   break;
      case 'BR':
           $this->html.=makeTag($name,$attrs,true);
	   break;
      case 'LI':
           if(count($this->listStyles)==0)
	     {
	     $this->html.='<b>&lt;LI?&gt;</b>';
	     break;
	     }
	   if($this->listStyles[0]!='d')
	     $this->html.=makeTag($name);
	   if(isset($attrs['TITLE']))
	     if($this->listStyles[0]!='d')
	       {
	       $this->html.=makeTag($this->listFonts[0]);
	       $this->html.=$attrs['TITLE'];
	       $this->html.=makeTag('/'.$this->listFonts[0]);
	       $this->html.=' ';
	       }
	     else
	       {
	       $this->html.=makeTag('dt');
	       $this->html.=makeTag($this->listFonts[0]);
	       $this->html.=$attrs['TITLE'];
	       $this->html.=makeTag('/'.$this->listFonts[0]);
	       $this->html.=makeTag('/dt');
	       }
	   if($this->listStyles[0]=='d')
	     $this->html.=makeTag('dd');
	   break;
      case 'UL':
           array_unshift($this->listStyles,'u');
           array_unshift($this->listFonts,'i');
           $this->html.=makeTag($name,$attrs);
	   break;
      case 'OL':
           array_unshift($this->listStyles,'o');
           array_unshift($this->listFonts,'i');
           $this->html.=makeTag($name,$attrs);
	   break;
      case 'DL':
           array_unshift($this->listStyles,'d');
           array_unshift($this->listFonts,
	                 !isset($attrs['FONT']) ? 'b' : $attrs['FONT']);
           $this->html.=makeTag($name,$attrs);
	   break;
      case 'QUOTE':
           $this->html.=makeTag('div',array('class' => 'quote'));
	   break;
      case 'FOOTNOTE':
	   $this->htmlFootnote='';
	   $this->xmlFootnote='';
	   $this->html=& $this->htmlFootnote;
	   $this->inFootnote=true;
           $this->html.=makeTag('a',array('href'  => '#_ref'.$this->noteNo));
           if(!isset($attrs['TITLE']))
	     {
	     $this->footnoteTitle='';
	     $this->html.=makeTag('sup');
	     $this->html.=$this->noteNo;
	     $this->html.=makeTag('/sup');
	     $this->html.=makeTag('/a');
	     }
	   else
	     {
	     $this->footnoteTitle=$attrs['TITLE'];
	     $this->html.=$this->footnoteTitle;
	     $this->html.=makeTag('/a');
	     $this->html.=' &mdash; ';
	     }
           $this->html.=makeTag('a',array('name' => '_note'.$this->noteNo));
	   $this->html.=makeTag('/a');
	   break;
      default:
           $this->html.=makeTag($name,$attrs);
      }
}

function endElement($parser,$name)
{
global $mtextTagLevel;

if(!isset($mtextTagLevel[$name]) || $mtextTagLevel[$name]>$this->format)
  {
  $this->html.="<b>** &lt;/$name&gt; **</b>";
  return;
  }
switch($name)
      {
      case 'MTEXT-LINE':
      case 'MTEXT-SHORT':
      case 'MTEXT-LONG':
      case 'BR':
           break;
      case 'LI':
           if(count($this->listStyles)==0)
	     {
	     $this->html.='<b>&lt;/LI?&gt;</b>';
	     break;
	     }
	   if($this->listStyles[0]!='d')
	     $this->html.=makeTag("/$name");
	   else
	     $this->html.=makeTag('/dd');
	   break;
      case 'UL':
      case 'OL':
      case 'DL':
           array_shift($this->listStyles);
           array_shift($this->listFonts);
           $this->html.=makeTag("/$name");
	   break;
      case 'QUOTE':
           $this->html.=makeTag('/div');
	   break;
      case 'FOOTNOTE':
	   $this->html.=makeTag('br',array(),true);
	   $this->htmlFootnotes.=$this->htmlFootnote;
           $this->html=& $this->htmlBody;
	   $this->inFootnote=false;
           $this->html.=makeTag('a',array('name' => '_ref'.$this->noteNo));
	   $this->html.=makeTag('/a');
           if($this->footnoteTitle=='')
	     {
	     $this->html.=makeTag('sup');
	     $this->html.=makeTag('a',
				  array('href'  => '#_note'.$this->noteNo,
					'title' => $this->getInplaceFootnote()));
	     $this->html.=$this->noteNo;
	     $this->html.=makeTag('/a');
	     $this->html.=makeTag('/sup');
	     }
	   else
	     {
	     $this->html.=makeTag('a',
				  array('href'  => '#_note'.$this->noteNo,
					'title' => $this->getInplaceFootnote()));
	     $this->html.=$this->footnoteTitle;
	     $this->html.=makeTag('/a');
	     }
	   $this->noteNo++;
	   break;
      default:
           $this->html.=makeTag("/$name");
      }
if($this->inFootnote)
  $this->xmlFootnote.=makeTag("/$name");
}

function characterData($parser,$data)
{
$s=utf8DecodeMarkup($data);
$this->html.=$s;
if($this->inFootnote)
  $this->xmlFootnote.=$s;
}

}

function mtextToHTML($body,$format=MTEXT_LINE,$id=0,$showFootnotes=false)
{
$xml=new MTextToHTMLXML($format,$id);
$xml->parse($body);
$xml->free();
return $showFootnotes ? array('body'      => $xml->getBodyHTML(),
                              'footnotes' => $xml->getFootnotesHTML())
                      : $xml->getBodyHTML();
}
?>
