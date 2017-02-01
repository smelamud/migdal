package ua.org.migdal.form;

import java.util.regex.Pattern;

import org.springframework.util.StringUtils;

public class InterruptingForm {

    private final static Pattern LOCATION_REGEX = Pattern.compile("^(/[a-zA-z0-9-~@]*)+(\\?.*)?$");

    private String back;

    public InterruptingForm() {
    }

    public InterruptingForm(String back, String defaultBack) {
        this.back = !StringUtils.isEmpty(back) ? back : defaultBack;
    }

    public String getBack() {
        return back;
    }

    public void setBack(String back) {
        this.back = back;
    }

    public String getBackUrlSafe() {
        return back != null && LOCATION_REGEX.matcher(back).matches() ? back : "/";
    }

}
