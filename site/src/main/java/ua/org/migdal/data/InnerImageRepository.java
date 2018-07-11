package ua.org.migdal.data;

import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;

public interface InnerImageRepository extends JpaRepository<InnerImage, Long> {

    @Query("from InnerImage where entry_id=?1 order by paragraph, y, x")
    List<InnerImage> findByEntryId(long entryId);

}