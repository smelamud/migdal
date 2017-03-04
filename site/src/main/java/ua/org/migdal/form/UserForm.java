package ua.org.migdal.form;

import org.hibernate.validator.constraints.Email;
import org.hibernate.validator.constraints.NotBlank;

import ua.org.migdal.data.UserRight;

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

    private long[] rights;

    @NotBlank
    @Email
    private String email = "";

    private boolean hideEmail;

    private String birthYear = "";

    private int birthMonth;

    private String birthDay = "";

    private boolean emailEnabled;

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

}