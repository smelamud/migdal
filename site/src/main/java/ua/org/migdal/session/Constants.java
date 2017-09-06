package ua.org.migdal.session;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.springframework.stereotype.Component;

import ua.org.migdal.data.PostingModbit;
import ua.org.migdal.data.TopicModbit;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.helper.calendar.Tables;
import ua.org.migdal.helper.util.Constant;

@Component
public class Constants {

    private List<Constant> gregorianMonthRuGenLcLong = new ArrayList<>();
    private Map<String, Long> userRight = new HashMap<>();
    private List<Constant> langs = new ArrayList<>();

    public Constants() {
    }

    public List<Constant> getGregorianMonthRuGenLcLong() {
        if (gregorianMonthRuGenLcLong.isEmpty()) {
            int i = 1;
            for (String month : Tables.GREGORIAN_MONTH_RU_GEN_LC_LONG) {
                gregorianMonthRuGenLcLong.add(new Constant(month, i++));
            }
        }
        return gregorianMonthRuGenLcLong;
    }

    public Map<String, Long> getUserRight() {
        if (userRight.isEmpty()) {
            for (UserRight right : UserRight.values()) {
                userRight.put(right.name(), right.getValue());
            }
        }
        return userRight;
    }

    public PostingModbit[] getPostingModbits() {
        return PostingModbit.values();
    }

    public TopicModbit[] getTopicModbits() {
        return TopicModbit.values();
    }

    public List<Constant> getLangs() {
        if (langs.isEmpty()) {
            langs.add(new Constant("Русский", "ru"));
            langs.add(new Constant("Английский", "en"));
            langs.add(new Constant("Иврит", "he"));
            langs.add(new Constant("Украинский", "uk"));
            langs.add(new Constant("Белорусский", "be"));
            langs.add(new Constant("Идиш", "yi"));
            langs.add(new Constant("Немецкий", "de"));
        }
        return langs;
    }

}