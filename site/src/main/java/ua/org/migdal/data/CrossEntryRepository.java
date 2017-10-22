package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;

public interface CrossEntryRepository extends JpaRepository<CrossEntry, Long> {

    CrossEntry findByLinkTypeAndPeerId(LinkType linkType, long peerId);

}