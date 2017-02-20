package ua.org.migdal.data;

import java.sql.Date;
import java.sql.Timestamp;
import java.util.Set;

import javax.persistence.Access;
import javax.persistence.AccessType;
import javax.persistence.Entity;
import javax.persistence.Enumerated;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.JoinTable;
import javax.persistence.ManyToMany;
import javax.persistence.Table;
import javax.persistence.Transient;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Utils;

@Entity
@Table(name = "users")
public class User implements Editable {

    @Id
    @GeneratedValue
    @Access(AccessType.PROPERTY)
    private long id;

    @NotNull
    @Size(max=30)
    private String login = "";

    @NotNull
    @Size(max=40)
    private String password ="";

    @NotNull
    @Size(max=30)
    private String name = "";

    @NotNull
    @Size(max=30)
    private String jewishName = "";

    @NotNull
    @Size(max=30)
    private String surname = "";

    @Enumerated
    private Gender gender = Gender.MINE;

    @NotNull
    private String info = "";

    @NotNull
    private String infoXml = "";

    @NotNull  // TODO make it nullable
    private Date birthday = new Date(0);

    private Timestamp created;

    private Timestamp modified;

    private Timestamp lastOnline;

    private Timestamp confirmDeadline;

    @NotNull
    private String confirmCode = "";

    @NotNull
    @Size(max=70)
    private String email = "";

    @NotNull
    private boolean hideEmail;

    @NotNull
    private boolean emailDisabled;

    @NotNull
    private boolean shames;

    @NotNull
    private boolean guest;

    @NotNull
    private long rights;

    @NotNull
    private short hidden;

    @NotNull
    private boolean noLogin;

    @NotNull
    private boolean hasPersonal;

    @NotNull
    @Size(max=70)
    private String settings = "";

    @ManyToMany
    @JoinTable(name = "groups",
            joinColumns = {@JoinColumn(name = "user_id", referencedColumnName = "id")},
            inverseJoinColumns = {@JoinColumn(name = "group_id", referencedColumnName = "id")})
    private Set<User> groups;

    @ManyToMany(mappedBy = "groups")
    private Set<User> users;

    public User() {
    }

    @Override
    public boolean isEditable(RequestContext requestContext) {
        return requestContext.getUserId() > 0
                && (getId() == requestContext.getUserId() || requestContext.isUserAdminUsers());
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getLogin() {
        return login;
    }

    public void setLogin(String login) {
        this.login = login;
    }

    @Transient
    public String getFolder() {
        return Utils.isAsciiNoWhitespace(getLogin()) ? getLogin() : Long.toString(getId());
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getJewishName() {
        return jewishName;
    }

    public void setJewishName(String jewishName) {
        this.jewishName = jewishName;
    }

    public String getSurname() {
        return surname;
    }

    public void setSurname(String surname) {
        this.surname = surname;
    }

    public Gender getGender() {
        return gender;
    }

    public void setGender(Gender gender) {
        this.gender = gender;
    }

    public boolean isMan() {
        return getGender() == null || getGender() == Gender.MINE;
    }

    public boolean isWoman() {
        return getGender() == Gender.FEMINE;
    }

    public String getInfo() {
        return info;
    }

    public void setInfo(String info) {
        this.info = info;
    }

    public String getInfoXml() {
        return infoXml;
    }

    public void setInfoXml(String infoXml) {
        this.infoXml = infoXml;
    }

    public Date getBirthday() {
        return birthday;
    }

    public void setBirthday(Date birthday) {
        this.birthday = birthday;
    }

    public Timestamp getCreated() {
        return created;
    }

    public void setCreated(Timestamp created) {
        this.created = created;
    }

    public Timestamp getModified() {
        return modified;
    }

    public void setModified(Timestamp modified) {
        this.modified = modified;
    }

    public Timestamp getLastOnline() {
        return lastOnline;
    }

    public void setLastOnline(Timestamp lastOnline) {
        this.lastOnline = lastOnline;
    }

    public Timestamp getConfirmDeadline() {
        return confirmDeadline;
    }

    public void setConfirmDeadline(Timestamp confirmDeadline) {
        this.confirmDeadline = confirmDeadline;
    }

    public String getConfirmCode() {
        return confirmCode;
    }

    public void setConfirmCode(String confirmCode) {
        this.confirmCode = confirmCode;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public boolean isHideEmail() {
        return hideEmail;
    }

    public void setHideEmail(boolean hideEmail) {
        this.hideEmail = hideEmail;
    }

    public boolean isEmailDisabled() {
        return emailDisabled;
    }

    public void setEmailDisabled(boolean emailDisabled) {
        this.emailDisabled = emailDisabled;
    }

    public boolean isShames() {
        return shames;
    }

    public void setShames(boolean shames) {
        this.shames = shames;
    }

    public boolean isGuest() {
        return guest;
    }

    public void setGuest(boolean guest) {
        this.guest = guest;
    }

    public long getRights() {
        return rights;
    }

    public void setRights(long rights) {
        this.rights = rights;
    }

    public short getHidden() {
        return hidden;
    }

    public void setHidden(short hidden) {
        this.hidden = hidden;
    }

    public boolean isNoLogin() {
        return noLogin;
    }

    public void setNoLogin(boolean noLogin) {
        this.noLogin = noLogin;
    }

    public boolean isHasPersonal() {
        return hasPersonal;
    }

    public void setHasPersonal(boolean hasPersonal) {
        this.hasPersonal = hasPersonal;
    }

    public String getSettings() {
        return settings;
    }

    public void setSettings(String settings) {
        this.settings = settings;
    }

    public boolean isMigdalStudent() {
        return (getRights() & UserRight.MIGDAL_STUDENT.getValue()) != 0;
    }

    public boolean isAdminUsers() {
        return (getRights() & UserRight.ADMIN_USERS.getValue()) != 0;
    }

    public boolean isAdminTopics() {
        return (getRights() & UserRight.ADMIN_TOPICS.getValue()) != 0;
    }

    public boolean isModerator() {
        return (getRights() & UserRight.MODERATOR.getValue()) != 0;
    }

    public boolean isAdminDomain() {
        return (getRights() & UserRight.ADMIN_DOMAIN.getValue()) != 0;
    }

    public Set<User> getGroups() {
        return groups;
    }

    public void setGroups(Set<User> groups) {
        this.groups = groups;
    }

    public Set<User> getUsers() {
        return users;
    }

    public void setUsers(Set<User> users) {
        this.users = users;
    }

}
