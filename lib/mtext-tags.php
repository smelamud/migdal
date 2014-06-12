<?php
# @(#) $Id$

require_once('lib/text-formats.php');

$mtextRootTag = array(
    MTEXT_LINE  => 'mtext-line',
    MTEXT_SHORT => 'mtext-short',
    MTEXT_LONG  => 'mtext-long'
);

$mtextTagLevel = array(
    'MTEXT-LINE' => MTEXT_LINE,
    'A'        => MTEXT_LINE,
    'EMAIL'    => MTEXT_LINE,
    'USER'     => MTEXT_LINE,
    'IMG'      => MTEXT_LINE,
    'B'        => MTEXT_LINE,
    'I'        => MTEXT_LINE,
    'U'        => MTEXT_LINE,
    'S'        => MTEXT_LINE,
    'STRIKE'   => MTEXT_LINE,
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
    'OBJECT'   => MTEXT_SHORT,
    'PARAM'    => MTEXT_SHORT,
    'EMBED'    => MTEXT_SHORT,
    'MTEXT-LONG' => MTEXT_LONG,
    'FOOTNOTE' => MTEXT_LONG,
    'TABLE'    => MTEXT_LONG,
    'TR'       => MTEXT_LONG,
    'TD'       => MTEXT_LONG,
    'TH'       => MTEXT_LONG,
    'INCUT'    => MTEXT_LONG
);

$mtextEmptyTags = array(
    'A'        => false,
    'EMAIL'    => true,
    'USER'     => true,
    'B'        => false,
    'I'        => false,
    'U'        => false,
    'S'        => false,
    'STRIKE'   => false,
    'TT'       => false,
    'SUP'      => false,
    'P'        => false,
    'QUOTE'    => false,
    'CENTER'   => false,
    'H2'       => false,
    'H3'       => false,
    'H4'       => false,
    'BR'       => true,
    'UL'       => false,
    'OL'       => false,
    'DL'       => false,
    'LI'       => false,
    'FOOTNOTE' => false,
    'IMG'      => true,
    'TABLE'    => false,
    'TR'       => false,
    'TD'       => false,
    'TH'       => false,
    'INCUT'    => false,
    'OBJECT'   => false,
    'PARAM'    => true,
    'EMBED'    => false
);
?>
