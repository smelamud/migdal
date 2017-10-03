package ua.org.migdal.data;

import java.sql.Timestamp;
import javax.persistence.Access;
import javax.persistence.AccessType;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.Table;
import javax.persistence.Transient;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

import ua.org.migdal.imageupload.UploadedImageFile;
import ua.org.migdal.util.ImageFileUtils;
import ua.org.migdal.util.Utils;

@Entity
@Table(name="image_files")
public class ImageFile {

    @Id
    @GeneratedValue
    @Access(AccessType.PROPERTY)
    private long id;

    @Size(max=30)
    private String mimeType;

    @NotNull
    @Column(name="size_x")
    private short sizeX;

    @NotNull
    @Column(name="size_y")
    private short sizeY;

    @NotNull
    private long fileSize;

    private Timestamp created;

    private Timestamp accessed;

    public ImageFile() {
    }

    public ImageFile(UploadedImageFile imageInfo) {
        mimeType = imageInfo.getFormat();
        sizeX = imageInfo.getSizeX();
        sizeY = imageInfo.getSizeY();
        fileSize = imageInfo.getFileSize();
        created = Utils.now();
        accessed = Utils.now();
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getMimeType() {
        return mimeType;
    }

    public void setMimeType(String mimeType) {
        this.mimeType = mimeType;
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

    public long getFileSize() {
        return fileSize;
    }

    public void setFileSize(long fileSize) {
        this.fileSize = fileSize;
    }

    public Timestamp getCreated() {
        return created;
    }

    public void setCreated(Timestamp created) {
        this.created = created;
    }

    public Timestamp getAccessed() {
        return accessed;
    }

    public void setAccessed(Timestamp accessed) {
        this.accessed = accessed;
    }

    @Transient
    public String getPath() {
        return ImageFileUtils.imagePath(getMimeType(), getId());
    }

}