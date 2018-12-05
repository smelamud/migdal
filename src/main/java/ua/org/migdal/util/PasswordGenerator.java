/*
 * Based on pw_phonemes.c --- generate secure passwords using phoneme rules
 * from pwgen
 *
 * Copyright (C) 2001,2002 by Theodore Ts'o
 * 
 * This file may be distributed under the terms of the GNU Public
 * License.
 */

package ua.org.migdal.util;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import org.springframework.data.util.Pair;

public class PasswordGenerator {
    
    private static final int PWG_CONSONANT = 0x0001;
    private static final int PWG_VOWEL     = 0x0002;
    private static final int PWG_DIPTHONG  = 0x0004;
    private static final int PWG_NOT_FIRST = 0x0008;

    private static final List<Pair<String, Integer>> PASSWORD_ELEMENTS;

    static {
        List<Pair<String, Integer>> passwordElements = new ArrayList<>();
        passwordElements.add(Pair.of("a",  PWG_VOWEL));
        passwordElements.add(Pair.of("ae", PWG_VOWEL | PWG_DIPTHONG));
        passwordElements.add(Pair.of("ah", PWG_VOWEL | PWG_DIPTHONG));
        passwordElements.add(Pair.of("ai", PWG_VOWEL | PWG_DIPTHONG));
        passwordElements.add(Pair.of("b",  PWG_CONSONANT));
        passwordElements.add(Pair.of("c",  PWG_CONSONANT));
        passwordElements.add(Pair.of("ch", PWG_CONSONANT | PWG_DIPTHONG));
        passwordElements.add(Pair.of("d",  PWG_CONSONANT));
        passwordElements.add(Pair.of("e",  PWG_VOWEL));
        passwordElements.add(Pair.of("ee", PWG_VOWEL | PWG_DIPTHONG));
        passwordElements.add(Pair.of("ei", PWG_VOWEL | PWG_DIPTHONG));
        passwordElements.add(Pair.of("f",  PWG_CONSONANT));
        passwordElements.add(Pair.of("g",  PWG_CONSONANT));
        passwordElements.add(Pair.of("gh", PWG_CONSONANT | PWG_DIPTHONG | PWG_NOT_FIRST));
        passwordElements.add(Pair.of("h",  PWG_CONSONANT));
        passwordElements.add(Pair.of("i",  PWG_VOWEL));
        passwordElements.add(Pair.of("ie", PWG_VOWEL | PWG_DIPTHONG));
        passwordElements.add(Pair.of("j",  PWG_CONSONANT));
        passwordElements.add(Pair.of("k",  PWG_CONSONANT));
        passwordElements.add(Pair.of("l",  PWG_CONSONANT));
        passwordElements.add(Pair.of("m",  PWG_CONSONANT));
        passwordElements.add(Pair.of("n",  PWG_CONSONANT));
        passwordElements.add(Pair.of("ng", PWG_CONSONANT | PWG_DIPTHONG | PWG_NOT_FIRST));
        passwordElements.add(Pair.of("o",  PWG_VOWEL));
        passwordElements.add(Pair.of("oh", PWG_VOWEL | PWG_DIPTHONG));
        passwordElements.add(Pair.of("oo", PWG_VOWEL | PWG_DIPTHONG));
        passwordElements.add(Pair.of("p",  PWG_CONSONANT));
        passwordElements.add(Pair.of("ph", PWG_CONSONANT | PWG_DIPTHONG));
        passwordElements.add(Pair.of("qu", PWG_CONSONANT | PWG_DIPTHONG));
        passwordElements.add(Pair.of("r",  PWG_CONSONANT));
        passwordElements.add(Pair.of("s",  PWG_CONSONANT));
        passwordElements.add(Pair.of("sh", PWG_CONSONANT | PWG_DIPTHONG));
        passwordElements.add(Pair.of("t",  PWG_CONSONANT));
        passwordElements.add(Pair.of("th", PWG_CONSONANT | PWG_DIPTHONG));
        passwordElements.add(Pair.of("u",  PWG_VOWEL));
        passwordElements.add(Pair.of("v",  PWG_CONSONANT));
        passwordElements.add(Pair.of("w",  PWG_CONSONANT));
        passwordElements.add(Pair.of("x",  PWG_CONSONANT));
        passwordElements.add(Pair.of("y",  PWG_CONSONANT));
        passwordElements.add(Pair.of("z",  PWG_CONSONANT));
        PASSWORD_ELEMENTS = Collections.unmodifiableList(passwordElements);
    }

    public static String generatePassword() {
        return generatePassword(8);
    }

    public static String generatePassword(int size) {
        StringBuilder password = new StringBuilder();
        int prev = 0;
        int shouldBe = Utils.random(0, 2) == 0 ? PWG_VOWEL : PWG_CONSONANT;
        while (password.length() < size) {
            int i = Utils.random(0, PASSWORD_ELEMENTS.size());
            Pair<String, Integer> element = PASSWORD_ELEMENTS.get(i);
            String str = element.getFirst();
            int flags = element.getSecond();
            /* Filter on the basic type of the next element */
            if ((flags & shouldBe) == 0) {
                continue;
            }
            /* Handle the PWG_NOT_FIRST flag */
            if (password.length() == 0 && (flags & PWG_NOT_FIRST) != 0) {
                continue;
            }
            /* Don't allow PWG_VOWEL followed a Vowel/Dipthong pair */
            if ((prev & PWG_VOWEL) != 0 && (flags & PWG_VOWEL) != 0 && (flags & PWG_DIPTHONG) != 0) {
                continue;
            }
            /* Don't allow us to overflow the buffer */
            if (str.length() > size - password.length()) {
                continue;
            }
            /*
             * OK, we found an element which matches our criteria,
             * let's do it!
             */
            password.append(str);
            /*
             * OK, figure out what the next element should be
             */
            if (shouldBe == PWG_CONSONANT) {
                shouldBe = PWG_VOWEL;
            } else {
                /* $shouldBe == PWG_VOWEL */
                if ((prev & PWG_VOWEL) != 0 || (flags & PWG_DIPTHONG) != 0 || Utils.random(0, 11) > 3) {
                    shouldBe = PWG_CONSONANT;
                } else {
                    shouldBe = PWG_VOWEL;
                }
            }
            prev = flags;
        }
        return password.toString();
    }

}