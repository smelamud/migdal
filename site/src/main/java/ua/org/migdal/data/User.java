package ua.org.migdal.data;

import java.sql.Date;
import java.sql.Timestamp;

import javax.persistence.Entity;
import javax.persistence.EnumType;
import javax.persistence.Enumerated;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

@Entity
@Table(name = "users")
public class User {

    @Id
    @GeneratedValue
    private long id;

    @NotNull
    @Size(max=30)
    private String login;

    @NotNull
    @Size(max=40)
    private String password;

    @NotNull
    @Size(max=30)
    private String name;

    @NotNull
    @Size(max=30)
    private String jewishName;

    @NotNull
    @Size(max=30)
    private String surname;

    @Enumerated(EnumType.STRING)
    private Gender gender = Gender.MINE;

    @NotNull
    private String info;

    @NotNull
    private String infoXml;

    @NotNull
    private Date birthday;

    private Timestamp created;

    private Timestamp modified;

    private Timestamp lastOnline;

    private Timestamp confirmDeadline;

    @NotNull
    private String confirmCode;

    @NotNull
    @Size(max=70)
    private String email;

    @NotNull
    private boolean hideEmail;

    @NotNull
    @Size(max=15)
    private String icq;

    @NotNull
    private boolean emailDisabled;

    @NotNull
    private boolean shames;

    @NotNull
    private boolean guest;

    @NotNull
    private long rights;

    @NotNull
    private boolean hidden;

    @NotNull
    private boolean noLogin;

    @NotNull
    private boolean hasPersonal;

    @NotNull
    @Size(max=70)
    private String settings;

    public User() {
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

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

}
