package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;

public interface OldIdRepository extends JpaRepository<OldId, OldEntryId> {
}
