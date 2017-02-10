<?php
# @(#) $Id$

require_once('lib/ctypes.php');
require_once('lib/xml.php');
require_once('lib/text.php');
require_once('lib/text-xml.php');

function is_delim($c) {
    return c_cntrl($c) || c_space($c) || c_punct($c);
}

function is_space($c) {
    return c_cntrl($c) || c_space($c);
}

function flipReplace($foo, $bar, $_bar, $s, $delim = true) {
    beginProfiling(POBJ_FUNCTION, 'flipReplace');
    $c = '';
    $tag = 0;
    $intag = 0;
    for ($n = 0; $n < strlen($s); $n++) {
        if ($s[$n] == '<')
            $intag++;
        if ($s[$n] == '>' && ($n == strlen($s) - 1 || $s[$n + 1] != '>')
            && $s[$n - 1] != '>')
            if ($intag > 0)
                $intag--;
        if (!$intag && !$tag && $s[$n] == $foo
            && ($n == 0
                || (!$delim || is_delim($s[$n - 1])) && $s[$n - 1] != '&'
                                    # &#entity; combinations are not replaced
                                                     && $s[$n - 1] != $foo)
            && $n != strlen($s) - 1
            && (!$delim || !is_delim($s[$n + 1]) || $s[$n + 1] == '&'
                        || $s[$n + 1] == '=' || $s[$n + 1] == '~'
                        || $s[$n + 1] == '-' || $s[$n + 1] == '<'
                        || $s[$n + 1] == '(' || $s[$n + 1] == '[')
                                    # word may start by entity or font
                                    # style markup or by dash or by tag
                                    # or by parenthesis or by bracket
            && $s[$n + 1] != $foo) {
            $c .= $bar;
            $tag = 1;
        } elseif (!$intag && $tag && $s[$n] == $foo
                  && ($n == strlen($s) || (!$delim || is_delim($s[$n + 1])))
                  && $n != 0 && (!$delim || !is_space($s[$n - 1]))) {
                                    # final punctuation is part
                                    # of the word
            $c .= $_bar;
            $tag = 0;
        } else {
            $c .= $s[$n];
        }
    }
    if ($tag)
        $c .= $_bar;
    endProfiling();
    return $c;
}

function getURLTag($whole, $url, $protocol, $content) {
    if (strchr($whole, "'") || strchr($whole, '<') || strchr($whole, '>'))
        return $whole;
    for ($i = 0; $i < strlen($url); $i++)
        if (ord($url{$i}) > 127)
            return $whole;
    return "<a href=\"$url\" local=\"".($protocol == '' ? 'true' : 'false').
           "\">$content</a>";
}

function goFurther(&$out, $in, &$start, &$end, &$state, $target = 0) {
    beginProfiling(POBJ_FUNCTION, 'goFurther');
    if ($end > $start) {
        $out .= preg_replace('/(^|[\s.,:;\(\)])(([^\s\(\)]+:\/)?\/[^\s&;]\S*[^\s.,:;\(\)&\\\\])/e',
                             "'\\1'.getURLTag('\\2','\\2','\\3','\\2')",
                             substr($in, $start, $end - $start));
        $start = $end;
    }
    $state = $target;
    endProfiling();
}

function replaceURLs($s) {
    beginProfiling(POBJ_FUNCTION, 'replaceURLs');
    $c = '';
    $state = 0;
    $st = 0;
    $ed = 0;
    while($ed < strlen($s)) {
        switch($state) {
            case 0:
                $ed = strpos($s, "'", $st);
                $ed = $ed === false ? strlen($s) : $ed;
                goFurther($c, $s, $st, $ed, $state, 1);
                break;

            case 1:
                $ed = $st + 1;
                if ($st != 0 && !is_delim($s[$st - 1]))
                    goFurther($c, $s, $st, $ed, $state);
                else
                    $state = 2;
                break;

            case 2:
                $ed = strpos($s, "'", $ed);
                $ed = $ed === false ? strlen($s) : $ed + 1;
                $state = 3;
                break;

            case 3:
                if (!preg_match('/^\s+((\S+:\/)?\/[^\s&;]\S*[^\s.,:;\(\)&\\\\])/',
                                substr($s, $ed), $matches)) {
                    $ed -= 1;
                    goFurther($c, $s, $st, $ed, $state);
                } else {
                    $state = 4;
                }
                break;

            case 4:
                $c .= getURLTag($matches[0], $matches[1], $matches[2],
                                substr($s, $st + 1, $ed - $st - 2));
                $st = $ed + strlen($matches[0]);
                $ed = $st;
                $state = 0;
                break;
        }
    }
    if ($ed > $st)
        $c .= substr($s, $st, $ed - $st);
    $c = preg_replace('/[A-Za-z0-9-_]+(\.[A-Za-z0-9-_]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*/',
                      '<email addr="\\0" />', $c);
    endProfiling();
    return $c;
}

function globalReplaceURLs($s) {
    $c = '';
    $st = 0;
    while ($st < strlen($s)) {
        $ed = strpos($s, '<', $st);
        $ed = $ed === false ? strlen($s) : $ed;
        $c .= replaceURLs(substr($s, $st, $ed - $st));
        if ($ed >= strlen($s))
            return $c;
        $st = strpos($s, '>', $ed);
        $st = $st === false ? strlen($s) - 1 : $st;
        $c .= substr($s, $ed, $st - $ed + 1);
        $st++;
    }
    return $c;
}

function getProperQuoting($s) {
    $matches = array();
    $n = preg_match_all('/>/', $s, $matches);
    $c = '';
    for ($i = 0; $i < $n; $i++)
       $c .= '> ';
    return $c;
}

function getQuoteLevel($s) {
    $n = 0;
    for ($i = 0; $i <= strlen($s) - 1; $i += 2)
        if ($s{$i} == '>')
            $n++;
        else
            return $n;
    return $n;
}

function replaceQuoting($s) {
    global $showQuoteChars;

    beginProfiling(POBJ_FUNCTION, 'replaceQuoting');
    $lines = explode("\n", $s);
    $level = 0;
    for ($i = 0; $i < count($lines); $i++) {
        $lines[$i] = preg_replace('/^((?:>\s*)+)/e', "getProperQuoting('\\1')",
                                  $lines[$i]);
        $l = getQuoteLevel($lines[$i]);
        if (!$showQuoteChars)
            $lines[$i] = substr($lines[$i], 2 * $l);
        if ($l >= $level)
            for ($j = 0; $j < $l - $level; $j++)
                $lines[$i] = '<quote><p>'.$lines[$i];
        else
            for ($j = 0; $j < $level - $l; $j++)
                $lines[$i - 1] = $lines[$i - 1].'</p></quote>';
        $level = $l;
    }
    endProfiling();
    return join("\n", $lines);
}

function replaceCenter($s) {
    return preg_replace('/(^|\n)[^\S\n]{10}[^\S\n]*([^\n]+)(\n|$)/',
                        '\\1<center>\\2</center>\\3', $s);
}

function replaceHeading($s, $n, $c) {
    $lines = explode("\n", $s);
    $out = array();
    for ($i = 0; $i < count($lines); $i++)
        if ($i + 1 < count($lines)) {
            // Reverse ligature
            $next = str_replace(array('&#8212;', '&#x2014;'),
                                array('---', '---'), $lines[$i + 1]);
            if (preg_match("/^\s*$c{3}$c*\s*$/", $next)
                && !preg_match('/^\s*$/', $lines[$i])) {
                $out[] = "<h$n>".$lines[$i]."</h$n>";
                $i++;
            } else {
                $out[] = $lines[$i];
            }
        } else {
            $out[] = $lines[$i];
        }
    return join("\n", $out);
}

function replaceHeadings($s) {
    return replaceHeading(
               replaceHeading(
                   replaceHeading($s, 2, '='), 3, '-'), 4, '~');
}

function replaceFootnotes($s) {
    $pattern = "/(?:^|(?<=>)|\s+)(?:'([^']+)'\s)?{{((?:[^}]|}[^}])+)}}/";
    do {
        $matches = array();
        if (!preg_match($pattern, $s, $matches))
            break;
        if ($matches[1] == '')
            $s = preg_replace($pattern, '<footnote>\\2</footnote>', $s, 1);
        else
            $s = preg_replace($pattern, '<footnote title="\\1">\\2</footnote>',
                              $s, 1);
    } while (true);
    return $s;
}

function wikiToXML($s, $format, $dformat) {
    beginProfiling(POBJ_FUNCTION, 'wikiToXML');
    $c = cleanupXML($s);
    switch($format) {
        default:
        case TF_MAIL:
            if ($dformat >= MTEXT_SHORT)
                $c = replaceQuoting($c);
        case TF_PLAIN:
            $c = globalReplaceURLs($c);
            if ($dformat >= MTEXT_SHORT) {
                $c = replaceHeadings($c);
                $c = replaceCenter($c);
                $c = replaceParagraphs($c);
            }
            if ($dformat >= MTEXT_LONG)
                $c = replaceFootnotes($c);
            $c = str_replace("\n", '<br />', $c);
            $c = str_replace('\\\\', '<br />', $c);
            $c = flipReplace('_', '<u>', '</u>', $c);
            $c = flipReplace('~', '<b>', '</b>', $c);
            $c = flipReplace('=', '<i>', '</i>', $c);
            $c = flipReplace('^', '<sup>', '</sup>', $c, false);
            $c = flipReplace('#', '<tt>', '</tt>', $c);
            break;

        case TF_TEX:
             $c = globalReplaceURLs($c);
             if ($dformat >= MTEXT_SHORT) {
                 $c = replaceHeadings($c);
                 $c = replaceCenter($c);
                 $c = replaceParagraphs($c);
             }
             if ($dformat >= MTEXT_LONG)
                 $c = replaceFootnotes($c);
             $c = str_replace('\\\\', '<br />', $c);
             $c = flipReplace('_', '<u>', '</u>', $c);
             $c = flipReplace('~', '<b>', '</b>', $c);
             $c = flipReplace('=', '<i>', '</i>', $c);
             $c = flipReplace('^', '<sup>', '</sup>', $c, false);
             $c = flipReplace('#', '<tt>', '</tt>', $c);
             break;
    }
    endProfiling();
    return delicateAmps(cleanupXML($c));
}
?>