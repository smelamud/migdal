package ua.org.migdal.data;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;

import org.hibernate.Hibernate;

@Entity
@DiscriminatorValue("4")
public class Image extends Entry {

    public Image() {
    }

    public Image(Posting posting) {
        setUp(posting);
    }

    public static String validateHierarchy(Entry parent, Entry up, long id) {
        String errorCode = Entry.validateHierarchy(parent, up, id);
        if (errorCode != null) {
            return errorCode;
        }
        if (parent != null) {
            return "hierarchyIncorrect";
        }
        if (up != null) {
            Class upClass = Hibernate.getClass(up);
            if (upClass != Posting.class && upClass != Comment.class && upClass != Topic.class) {
                return "hierarchyIncorrect";
            }
        }
        return null;
    }

}