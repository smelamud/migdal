<?php
# @(#) $Id$
/*
 * Based on pw_phonemes.c --- generate secure passwords using phoneme rules
 * from pwgen
 *
 * Copyright (C) 2001,2002 by Theodore Ts'o
 * 
 * This file may be distributed under the terms of the GNU Public
 * License.
 */

require_once('lib/random.php');

define('PWG_CONSONANT',0x0001);
define('PWG_VOWEL',    0x0002);
define('PWG_DIPTHONG', 0x0004);
define('PWG_NOT_FIRST',0x0008);

$passwordElements = array(
	array('a',	PWG_VOWEL),
	array('ae',	PWG_VOWEL | PWG_DIPTHONG),
	array('ah',	PWG_VOWEL | PWG_DIPTHONG),
	array('ai',	PWG_VOWEL | PWG_DIPTHONG),
	array('b',	PWG_CONSONANT),
	array('c',	PWG_CONSONANT),
	array('ch',	PWG_CONSONANT | PWG_DIPTHONG),
	array('d',	PWG_CONSONANT),
	array('e',	PWG_VOWEL),
	array('ee',	PWG_VOWEL | PWG_DIPTHONG),
	array('ei',	PWG_VOWEL | PWG_DIPTHONG),
	array('f',	PWG_CONSONANT),
	array('g',	PWG_CONSONANT),
	array('gh',	PWG_CONSONANT | PWG_DIPTHONG | PWG_NOT_PWG_FIRST),
	array('h',	PWG_CONSONANT),
	array('i',	PWG_VOWEL),
	array('ie',	PWG_VOWEL | PWG_DIPTHONG),
	array('j',	PWG_CONSONANT),
	array('k',	PWG_CONSONANT),
	array('l',	PWG_CONSONANT),
	array('m',	PWG_CONSONANT),
	array('n',	PWG_CONSONANT),
	array('ng',	PWG_CONSONANT | PWG_DIPTHONG | PWG_NOT_PWG_FIRST),
	array('o',	PWG_VOWEL),
	array('oh',	PWG_VOWEL | PWG_DIPTHONG),
	array('oo',	PWG_VOWEL | PWG_DIPTHONG),
	array('p',	PWG_CONSONANT),
	array('ph',	PWG_CONSONANT | PWG_DIPTHONG),
	array('qu',	PWG_CONSONANT | PWG_DIPTHONG),
	array('r',	PWG_CONSONANT),
	array('s',	PWG_CONSONANT),
	array('sh',	PWG_CONSONANT | PWG_DIPTHONG),
	array('t',	PWG_CONSONANT),
	array('th',	PWG_CONSONANT | PWG_DIPTHONG),
	array('u',	PWG_VOWEL),
	array('v',	PWG_CONSONANT),
	array('w',	PWG_CONSONANT),
	array('x',	PWG_CONSONANT),
	array('y',	PWG_CONSONANT),
	array('z',	PWG_CONSONANT)
);

function generatePassword($size = 8) {
    global $passwordElements;

    $password = '';
    $prev = 0;
    $shouldBe = random(0, 2) ? PWG_VOWEL : PWG_CONSONANT;
    while (strlen($password) < $size) {
        $i = random(0, count($passwordElements));
        list($str, $flags) = $passwordElements[$i];
        /* Filter on the basic type of the next element */
        if (($flags & $shouldBe) == 0)
            continue;
        /* Handle the PWG_NOT_FIRST flag */
        if ($password == '' && ($flags & PWG_NOT_FIRST))
            continue;
        /* Don't allow PWG_VOWEL followed a Vowel/Dipthong pair */
        if (($prev & PWG_VOWEL) && ($flags & PWG_VOWEL)
            && ($flags & PWG_DIPTHONG))
            continue;
        /* Don't allow us to overflow the buffer */
        if (strlen($str) > $size - strlen($password))
            continue;
        /*
         * OK, we found an element which matches our criteria,
         * let's do it!
         */
        $password .= $str;
        /*
         * OK, figure out what the next element should be
         */
        if ($shouldBe == PWG_CONSONANT)
            $shouldBe = PWG_VOWEL;
        else
            /* $shouldBe == PWG_VOWEL */
            if (($prev & PWG_VOWEL) || ($flags & PWG_DIPTHONG)
                || random(0, 11) > 3)
                $shouldBe = PWG_CONSONANT;
            else
                $shouldBe = PWG_VOWEL;
        $prev = $flags;
    }
    return $password;
}
?>
