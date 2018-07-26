package ua.org.migdal.data;

import javax.persistence.Access;
import javax.persistence.AccessType;
import javax.persistence.Entity;
import javax.persistence.FetchType;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.ManyToOne;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;

@Entity
@Table(name="inner_images")
public class InnerImage {

    @Id
    @GeneratedValue
    @Access(AccessType.PROPERTY)
    private long id;

    @ManyToOne(fetch=FetchType.LAZY)
    private Entry entry;

    @NotNull
    private int paragraph;

    @NotNull
    private int x;

    @NotNull
    private int y;

    @ManyToOne(fetch=FetchType.EAGER)
    private Image image;

    @NotNull
    private short placement;

    public InnerImage(Entry entry, Image image) {
        this.entry = entry;
        this.image = image;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public Entry getEntry() {
        return entry;
    }

    public void setEntry(Entry entry) {
        this.entry = entry;
    }

    public int getParagraph() {
        return paragraph;
    }

    public void setParagraph(int paragraph) {
        this.paragraph = paragraph;
    }

    public int getX() {
        return x;
    }

    public void setX(int x) {
        this.x = x;
    }

    public int getY() {
        return y;
    }

    public void setY(int y) {
        this.y = y;
    }

    public Image getImage() {
        return image;
    }

    public void setImage(Image image) {
        this.image = image;
    }

    public short getPlacement() {
        return placement;
    }

    public void setPlacement(short placement) {
        this.placement = placement;
    }

    public boolean isPlaced(short place) {
        return ImagePlacement.isPlaced(placement, place);
    }

}