package ua.org.migdal.data;

import javax.persistence.Access;
import javax.persistence.AccessType;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

@Entity
@Table(name="prisoners")
public class Prisoner {

    @Id
    @GeneratedValue
    @Access(AccessType.PROPERTY)
    private long id;

    @NotNull
    @Size(max=255)
    private String name = "";

    @NotNull
    @Size(max=255)
    private String nameRussian = "";

    @NotNull
    @Size(max=1)
    private String gender = "";

    @NotNull
    @Size(max=31)
    private String age = "";

    @NotNull
    @Size(max=255)
    private String location = "";

    @NotNull
    @Size(max=255)
    private String ghettoName = "";

    @NotNull
    @Size(max=255)
    private String senderName = "";

    @NotNull
    private int sum;

    @NotNull
    @Size(max=255)
    private String searchData = "";

    public Prisoner() {
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getNameRussian() {
        return nameRussian;
    }

    public void setNameRussian(String nameRussian) {
        this.nameRussian = nameRussian;
    }

    public String getGender() {
        return gender;
    }

    public void setGender(String gender) {
        this.gender = gender;
    }

    public String getAge() {
        return age;
    }

    public void setAge(String age) {
        this.age = age;
    }

    public String getLocation() {
        return location;
    }

    public void setLocation(String location) {
        this.location = location;
    }

    public String getGhettoName() {
        return ghettoName;
    }

    public void setGhettoName(String ghettoName) {
        this.ghettoName = ghettoName;
    }

    public String getSenderName() {
        return senderName;
    }

    public void setSenderName(String senderName) {
        this.senderName = senderName;
    }

    public int getSum() {
        return sum;
    }

    public void setSum(int sum) {
        this.sum = sum;
    }

    public String getSearchData() {
        return searchData;
    }

    public void setSearchData(String searchData) {
        this.searchData = searchData;
    }

}
