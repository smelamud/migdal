<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/utils.php');

function charsetName($name) {
    $pname = '';
    for ($i = 0; $i < strlen($name); $i++)
        if ($name{$i} != '-' && $name{$i} != '_')
            $pname .= strtoupper($name{$i});
    return $pname;
}

function charToEntity($c, $_charset = 'UTF-16', $lsb = 0) {
    $charset = charsetName($_charset);
    if ($charset != 'UTF16') {
        $c = @iconv($_charset, 'UTF-16', $c);
        $lsb = ord($c{0}) == 0xfe ? 1 : 0;
        $c = substr($c, 2);
    }
    return '&#x'.sprintf('%02x', ord($c{1 - $lsb}))
                .sprintf('%02x', ord($c{$lsb})).';';
}

function entityToChar($c, $_charset = 'UTF-16') {
    if (preg_match('/^&#(\d+);$/', $c, $matches)) {
        $code = (int)$matches[1];
        if ($code == 39) // do not convert single quotes
            return $c;
    } elseif (preg_match('/^&#x([\dA-Fa-f]+);$/', $c, $matches)) {
        $code = (int)hexdec($matches[1]);
    } else {
        return $c;
    }
    $s = pack('SS', 0xfeff, $code);
    $charset = charsetName($_charset);
    if ($charset != 'UTF16')
        $s = @iconv('UTF-16', $_charset, $s);
    else
        $s = substr($s, 2);
    return $s;
}

// Converts from $icharset to $ocharset and decodes all character entities.
// Does not create character entities for unknown characters, so use this
// function only for conversions into UTF-8 or UTF-16 encoding.
function convertToUTF($s, $i_charset, $o_charset) {
    $icharset = charsetName($i_charset);
    $ocharset = charsetName($o_charset);
    if ($icharset != 'UTF8') {
        $s = @iconv($i_charset, 'UTF-8', $s);
        $i_charset = 'UTF-8';
        $icharset = 'UTF8';
    }
    $s = preg_replace('/&#x?([\dA-Fa-f]+);/e',
                      "entityToChar('\\0','$i_charset')", $s);
    return $icharset == $ocharset ? $s : @iconv($i_charset, $o_charset, $s);
}

function iconvMaxSubstr($s, $i_charset, $o_charset, $icsize) {
    global $brokenIconv;

    if (!$brokenIconv)
        return @iconv($i_charset, $o_charset, $s);
    $c = '';
    $pos = $icsize == 2 ? 1 : 0;
    $len = (int)(strlen($s) / ($icsize * 2));
    while ($len != 0) {
        $ichunk = substr($s, $pos * $icsize, $len * $icsize);
        if ($icsize == 2)
            $ichunk = substr($s, 0, 2).$ichunk;
        $ochunk = @iconv($i_charset, $o_charset, $ichunk);
        if ($ochunk != '') {
            $c .= $ochunk;
            $pos += strlen($ochunk); // Output charset is 8-bit
        } else {
            $len = (int)($len / 2);
        }
    }
    return $c;
}

function convertCharset($s,$i_charset,$o_charset) {
    $icharset = charsetName($i_charset);
    $ocharset = charsetName($o_charset);
    if (substr($ocharset, 0, 3) == 'UTF') {
        $s = convertToUTF($s, $i_charset, $o_charset);
        return $s;
    }
    if ($icharset == $ocharset)
        return $s;
    $lsb = 0;
    if ($icharset == 'UTF8') {
        $s = @iconv($i_charset, 'UTF-16', $s);
        $lsb = $s != '' && ord($s{0}) == 0xfe ? 1 : 0;
        $s = substr($s, 2);
        $i_charset = 'UTF-16';
        $icharset = 'UTF16';
        $icsize = 2;
    } else {
        $icsize = 1;
    }
    $c = '';
    $pos = 0;
    while (true) {
        $chunk = substr($s, $pos * $icsize);
        if ($icsize == 2)
            $chunk = ($lsb == 0 ? "\xff\xfe" : "\xfe\xff").$chunk;
        $chunk = iconvMaxSubstr($chunk, $i_charset, $o_charset, $icsize);
        if (strlen($chunk) >= strlen($s) / $icsize - $pos) {
            $c .= $chunk;
            break;
        }
        $pos += strlen($chunk);
        $c .= $chunk;
        $c .= charToEntity(substr($s, $pos * $icsize, $icsize),
                           $i_charset, $lsb);
        $pos++;
    }
    return $c;
}

function convertFromXMLText($s) {
    global $charsetInternal;

    debugLog(LL_DEBUG, 'convertFromXMLText(s=%)', array($s));
    return convertCharset($s, 'UTF-8', $charsetInternal);
}

function convertToXMLText($s) {
    global $charsetInternal;

    return @iconv($charsetInternal, 'UTF-8', $s);
}

function isKOI($s) {
    $c = 0;
    for ($i = 0; $i < strlen($s); $i++) {
        if ($s{$i} >= 'Ю' && $s{$i} <= 'Ъ')
            $c++;
        if ($s{$i} >= 'ю' && $s{$i} <= 'ъ')
            $c--;
    }
    return $c <= 0;
}

function isUTF8($s) {
    $errc = 0;
    $cc = 0;
    for ($i = 0; $i < strlen($s); $i++) {
        $byte = ord($s{$i});
        if (($byte & 0xc0) == 0x80) {
            if ($cc > 0)
                $cc--;
            else
                $errc++;
        } else {
            if ($cc > 0)
                $errc++;
            if (($byte & 0x80) == 0)
                $cc = 0;
            elseif (($byte & 0xe0) == 0xc0)
                $cc = 1;
            elseif (($byte & 0xf0) == 0xe0)
                $cc = 2;
            elseif (($byte & 0xf8) == 0xf0)
                $cc = 3;
        }
    }
    return $errc / strlen($s) <= 0.01;
}

function convertLigatures($s) {
    return str_replace(
        array('<<', '&lt;&lt;', '>>', '&gt;&gt;', '&LT;&LT;', '&GT;&GT;',
              '---', '``', '&#039;&#039;', "''", '(c)', '(C)', '(r)', '(R)',
              '(tm)', '(TM)', 'No.', '&sp;', '&nil;'),
        array('&#171;', '&#171;', '&#187;', '&#187;', '&#171;', '&#187;',
              '&#8212;', '&#8220;', '&#8221;', '&#8221;', '&#169;', '&#169;',
              '&#174;', '&#174;', '&#8482;', '&#8482;', '&#8470;', ' ', ''),
        $s);
}

$hebrewCodes = array(
    'а' => '05D0',
    'А' => 'FB2E',
    'о' => 'FB2F',
    'б' => 'FB31',
    'в' => '05D1',
    'Б' => '05D1',
    'г' => '05D2',
    'д' => '05D3',
    'h' => '05D4',
    'у' => '05D5',
    'У' => 'FB35',
    'В' => '05F0',
    'О' => '05F1',
    'з' => '05D6',
    'ч' => '05D7',
    'т' => '05D8',
    'и' => '05D9',
    'Й' => 'FB1D',
    'ы' => '05F2',
    'Ы' => 'FB1F',
    'к' => 'FB3B',
    'х' => '05DB',
    'Х' => '05DA',
    'л' => '05DC',
    'м' => '05DE',
    'М' => '05DD',
    'н' => '05E0',
    'Н' => '05DF',
    'с' => '05E1',
    'э' => '05E2',
    'п' => 'FB44',
    'ф' => '05E4',
    'Ф' => '05E3',
    'ц' => '05E6',
    'Ц' => '05E5',
    'К' => '05E7',
    'р' => '05E8',
    'ш' => '05E9',
    'Ш' => 'FB2A',
    'Щ' => 'FB2B',
    'Т' => 'FB4A',
    'С' => '05EA',
    '+' => 'FB29',
    '-' => '05BE',
    "'" => '05F3',
    '"' => '05F4'
);

function convertHebrewBlock($s) {
    global $hebrewCodes;

    $c = '';
    for ($i = 0; $i < strlen($s); $i++)
        $c .= '&#x'.$hebrewCodes[$s{$i}].';';
    return "$c&lrm;";
}

function convertHebrew($s) {
    return preg_replace('/\$(-?)(\S+)\$/e',
                        "'\\1'=='-' ? convertHebrewBlock(strrev('\\2'))
                                    : convertHebrewBlock('\\2')",
                        $s);
}

function convertInputString($s) {
    global $charsetInternal, $charsetExternal;

    $s = convertLigatures($s);
    $s = convertCharset($s, $charsetExternal, $charsetInternal);
    $s = convertHebrew($s);
    return $s;
}

function convertUploadedText($s) {
    global $charsetInternal;

    $s = convertLigatures($s);
    if (isUTF8($s))
        $icharset = 'UTF-8';
    elseif (isKOI($s))
        $icharset = 'KOI8-R';
    else
        $icharset = 'CP1251';
    $s = convertCharset($s, $icharset, $charsetInternal);
    $s = convertHebrew($s);
    return $s;
}

function convertOutputString($s) {
    return delicateSpecialChars($s, DSC_SQ);
}

function convertOutput($s) {
    global $charsetInternal, $charsetExternal;

    $c = convertCharset($s, $charsetInternal, $charsetExternal);
    return $c;
}
?>
