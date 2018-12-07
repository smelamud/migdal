package ua.org.migdal.data;

import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;

public interface CrossEntryRepository extends JpaRepository<CrossEntry, Long> {

    CrossEntry findByLinkTypeAndPeerId(LinkType linkType, long peerId);

    // We need @Query here to force join
    @Query("from CrossEntry ce left join ce.peer where ce.linkType=?1 and ce.sourceName=?2 order by ce.peer.subject")
    List<CrossEntry> findAllByLinkTypeAndSourceNameOrderByPeerSubject(LinkType linkType, String sourceName);

    // We need @Query here to force join
    @Query("from CrossEntry ce left join ce.peer where ce.linkType=?1 and ce.source.id=?2 order by ce.peer.subject")
    List<CrossEntry> findAllByLinkTypeAndSourceIdOrderByPeerSubject(LinkType linkType, long sourceId);

}
