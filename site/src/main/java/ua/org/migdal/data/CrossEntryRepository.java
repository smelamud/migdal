package ua.org.migdal.data;

import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;

public interface CrossEntryRepository extends JpaRepository<CrossEntry, Long> {

    CrossEntry findByLinkTypeAndPeerId(LinkType linkType, long peerId);

    List<CrossEntry> findAllByLinkTypeAndSourceNameOrderByPeerSubject(LinkType linkType, String sourceName);

}