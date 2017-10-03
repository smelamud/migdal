package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;

public interface ImageFileTransformRepository extends JpaRepository<ImageFileTransform, Long> {

    @Query("from ImageFileTransform t where t.original.id=?1 and t.transform=?2 and t.sizeX=?3 and t.sizeY=?4")
    ImageFileTransform findBySource(long sourceId, ImageFileTransformType transform, short transformX, short transformY);

    @Query("from ImageFileTransform t where t.original.id=?1 and t.transform=?2"
           + " and t.destination.sizeX=?3 and t.destination.sizeY=?4")
    ImageFileTransform findByResult(long sourceId, ImageFileTransformType transform, short sizeX, short sizeY);

}