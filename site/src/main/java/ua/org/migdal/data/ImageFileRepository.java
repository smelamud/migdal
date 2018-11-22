package ua.org.migdal.data;

import java.util.Set;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;

public interface ImageFileRepository extends JpaRepository<ImageFile, Long> {

    @Query("select imf.id from ImageFile imf")
    Set<Long> findAllIds();

    @Modifying
    @Query("delete from ImageFile imf"
            + " where not exists(select e.id from Entry e where e.smallImage.id=imf.id)"
            + " and not exists(select e.id from Entry e where e.largeImage=imf.id)")
    void deleteObsolete();

}
