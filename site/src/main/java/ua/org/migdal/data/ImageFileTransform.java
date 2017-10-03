package ua.org.migdal.data;

import javax.persistence.Access;
import javax.persistence.AccessType;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.EnumType;
import javax.persistence.Enumerated;
import javax.persistence.FetchType;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToOne;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;

@Entity
@Table(name="image_file_transforms")
public class ImageFileTransform {

    @Id
    @GeneratedValue
    @Access(AccessType.PROPERTY)
    private long id;

    @NotNull
    @ManyToOne(fetch=FetchType.LAZY)
    @JoinColumn(name="dest_id")
    private ImageFile destination;

    @NotNull
    @ManyToOne(fetch=FetchType.LAZY)
    @JoinColumn(name="orig_id")
    private ImageFile original;

    @NotNull
    @Enumerated(EnumType.ORDINAL)
    private ImageFileTransformType transform;

    @NotNull
    @Column(name="size_x")
    private short sizeX;

    @NotNull
    @Column(name="size_y")
    private short sizeY;

    public ImageFileTransform() {
    }

    public ImageFileTransform(ImageFile destination, ImageFile original, ImageFileTransformType transform,
                              short sizeX, short sizeY) {
        this.destination = destination;
        this.original = original;
        this.transform = transform;
        this.sizeX = sizeX;
        this.sizeY = sizeY;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public ImageFile getDestination() {
        return destination;
    }

    public void setDestination(ImageFile destination) {
        this.destination = destination;
    }

    public ImageFile getOriginal() {
        return original;
    }

    public void setOriginal(ImageFile original) {
        this.original = original;
    }

    public ImageFileTransformType getTransform() {
        return transform;
    }

    public void setTransform(ImageFileTransformType transform) {
        this.transform = transform;
    }

    public short getSizeX() {
        return sizeX;
    }

    public void setSizeX(short sizeX) {
        this.sizeX = sizeX;
    }

    public short getSizeY() {
        return sizeY;
    }

    public void setSizeY(short sizeY) {
        this.sizeY = sizeY;
    }

}