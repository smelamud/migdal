<?php

$spamTerms = array('clickcashmoney.com', 'porno-video-free', 'porno-exe',
                   'rem-stroi.com', 'hiphoprussia.ru', 'retrade.ru', 't35.com',
                   'snapbackneweracap', 'viagra', 'zarplatt',
                   'stomatolog-stargard', 'off-rabota', '[/url]', 'cialis',
                   'levitra', 'kamagra', 'tadacip', 'apcalis');

function isSpam($subject, $body) {
    global $spamTerms;

    if (preg_match('/^[0-9a-z]{8}$/', $subject)) {
        return true;
    }
    foreach ($spamTerms as $term) {
        if (strpos($subject, $term) !== false) {
            return true;
        }
        if (strpos($body, $term) !== false) {
            return true;
        }
    }
    return false;
}
?>
