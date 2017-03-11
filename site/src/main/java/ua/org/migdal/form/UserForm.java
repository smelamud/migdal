package ua.org.migdal.form;

import java.security.NoSuchAlgorithmException;
import java.sql.Timestamp;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.time.temporal.TemporalUnit;

import org.hibernate.validator.constraints.Email;
import org.hibernate.validator.constraints.NotBlank;

import org.springframework.util.StringUtils;
import ua.org.migdal.Config;
import ua.org.migdal.data.Gender;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.util.Password;
import ua.org.migdal.util.Utils;

public class UserForm {

    private long id;

    @NotBlank
    private String newLogin = "";

    private String newPassword = "";

    private String dupPassword = "";

    @NotBlank
    private String name = "";

    private String jewishName = "";

    @NotBlank
    private String surname = "";

    private boolean gender;

    private String info = "";

    private long[] rights;

    @NotBlank
    @Email
    private String email = "";

    private boolean hideEmail;

    private String birthYear = "";

    private int birthMonth;

    private String birthDay = "";

    private boolean emailEnabled = true;

    private short hidden;

    private boolean noLogin;

    private boolean hasPersonal;

    public UserForm() {
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getNewLogin() {
        return newLogin;
    }

    public void setNewLogin(String newLogin) {
        this.newLogin = newLogin;
    }

    public String getNewPassword() {
        return newPassword;
    }

    public void setNewPassword(String newPassword) {
        this.newPassword = newPassword;
    }

    public String getDupPassword() {
        return dupPassword;
    }

    public void setDupPassword(String dupPassword) {
        this.dupPassword = dupPassword;
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

    public boolean isGender() {
        return gender;
    }

    public void setGender(boolean gender) {
        this.gender = gender;
    }

    public String getInfo() {
        return info;
    }

    public void setInfo(String info) {
        this.info = info;
    }

    public long[] getRights() {
        return rights;
    }

    public void setRights(long[] rights) {
        this.rights = rights;
    }

    private boolean hasRight(long right) {
        if (rights == null) {
            return false;
        }

        for (long r : rights) {
            if (r == right) {
                return true;
            }
        }
        return false;
    }

    public boolean isMigdalStudent() {
        return hasRight(UserRight.MIGDAL_STUDENT.getValue());
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

    public String getBirthYear() {
        return birthYear;
    }

    public void setBirthYear(String birthYear) {
        this.birthYear = birthYear;
    }

    public int getBirthMonth() {
        return birthMonth;
    }

    public void setBirthMonth(int birthMonth) {
        this.birthMonth = birthMonth;
    }

    public String getBirthDay() {
        return birthDay;
    }

    public void setBirthDay(String birthDay) {
        this.birthDay = birthDay;
    }

    public boolean isEmailEnabled() {
        return emailEnabled;
    }

    public void setEmailEnabled(boolean emailEnabled) {
        this.emailEnabled = emailEnabled;
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

    public void toUser(User user, boolean isAdmin, Config config) throws NoSuchAlgorithmException {
        user.setLogin(getNewLogin());
        user.setName(getName());
        user.setJewishName(getJewishName());
        user.setSurname(getSurname());
        user.setGender(!isGender() ? Gender.MINE : Gender.FEMINE);
        user.setInfo(getInfo());
        // TODO  $this->info_xml = anyToXML($this->info, $tfUser, MTEXT_SHORT);
        user.setModified(Utils.now());
        user.setBirthdayDay(!StringUtils.isEmpty(getBirthDay()) ? Short.parseShort(getBirthDay()) : 0);
        user.setBirthdayMonth((short) getBirthMonth());
        user.setBirthdayYear(!StringUtils.isEmpty(getBirthDay()) ? Short.parseShort(getBirthDay()) : 0);
        long rights = 0;
        if (getRights() != null) {
            for (long right : getRights()) {
                rights |= right;
            }
        }
        user.setRights(rights);
        user.setEmail(getEmail());
        user.setHideEmail(isHideEmail());
        user.setEmailDisabled(isEmailEnabled() ? (short) 0 : (short) 1);
        if (isAdmin) {
            user.setHidden(getHidden());
            user.setNoLogin(isNoLogin());
            user.setHasPersonal(isHasPersonal());
        } else {
            user.setNoLogin(true);
            user.setConfirmCode(generateConfirmCode());
            user.setConfirmDeadline(Timestamp.from(Instant.now().plus(config.getRegConfirmTimeout(), ChronoUnit.DAYS)));
        }
        if (user.getId() <= 0 || !StringUtils.isEmpty(getNewPassword())) {
            Password.assign(user, getNewPassword());
        }
        if (user.getId() <= 0) {
            user.setCreated(Utils.now());
        }
    }

    private String generateConfirmCode() {
        StringBuilder buf = new StringBuilder();
        for (int i = 0; i < 20; i++) {
            buf.append((char) Utils.random((int) 'A', (int) 'Z' + 1));
        }
        return buf.toString();
    }

}