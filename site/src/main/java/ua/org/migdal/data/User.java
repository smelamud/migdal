package ua.org.migdal.data;

import javax.persistence.Entity;
import javax.persistence.EnumType;
import javax.persistence.Enumerated;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.Index;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;

import org.springframework.util.StringUtils;

@Entity
@Table(name = "users",
        indexes = @Index(name = "users_email_idx", columnList = "email", unique = true))
public class User {

    @Id
    @GeneratedValue
    private long id;

    @NotNull
    private String email;

    @NotNull
    private String password;

    @Enumerated(EnumType.ORDINAL)
    private UserRole role = UserRole.REGULAR;

    public User() {
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public String getDisplayNameAdministrative() {
        if (!StringUtils.isEmpty(getEmail())) {
            return getEmail();
        } else {
            return "@" + id;
        }
    }

    public UserRole getRole() {
        return role;
    }

    public void setRole(UserRole role) {
        this.role = role;
    }

}
