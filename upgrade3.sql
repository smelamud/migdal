DROP TABLE `instants`;
INSERT INTO cross_entries( source_id, source_grp, peer_id, peer_grp )
SELECT sources.entry_id, topic_grp, peers.entry_id, peer_grp
FROM `cross_topics`
LEFT JOIN old_ids AS sources ON cross_topics.topic_id = sources.old_id
AND sources.table_name = 'topics'
LEFT JOIN old_ids AS peers ON cross_topics.peer_id = peers.old_id
AND peers.table_name = 'topics';
DROP TABLE `cross_topics`;
