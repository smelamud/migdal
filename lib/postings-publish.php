<?php
require_once('conf/migdal.conf');

require_once('lib/postings.php');
require_once('lib/cross-entries.php');

function publishPosting(Posting $posting) {
    global $publishingInterval;

    $publishGrp = $posting->getPublish();
    if ($publishGrp == GRP_NONE)
        return;
    $publishId = getPostingId($publishGrp, -1, $posting->getTopicId(),
                              $posting->getUserId());
    $publish = getPostingById($publishId, $publishGrp, $posting->getTopicId(),
                              SELECT_GENERAL, $posting->getTopicId());
    if ($publish->getId() > 0
        && ourtime() - $publish->getModified() > $publishingInterval * 3600)
        $publish = getRootPosting($publishGrp, $posting->getTopicId(),
                                  $posting->getTopicId());
    $publish->setIndex1($publish->getIndex1() + 1);
    storePosting($publish);
    $cross = new CrossEntry();
    $cross->setSourceId($publish->getId());
    $cross->setLinkType(LINKT_PUBLISH);
    $cross->setPeerId($posting->getId());
    storeCrossEntry($cross);
}

function unpublishPosting(Posting $posting) {
    $publishGrp = $posting->getPublish();
    if ($publishGrp == GRP_NONE)
        return;
    $cross = getCrossEntry(LINKT_PUBLISH, 0, $posting->getId());
    if (is_null($cross))
        return;
    deleteCrossEntry($cross->getId());
    $publish = getPostingById($cross->getSourceId());
    if ($publish->getId() <= 0)
        return;
    if ($publish->getIndex1() > 1) {
        $publish->setIndex1($publish->getIndex1() - 1);
        storePosting($publish);
    } else {
        deletePosting($publish->getId());
    }
}
?>
