package ua.org.migdal.session;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.springframework.data.util.Pair;
import org.springframework.stereotype.Component;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.helper.calendar.Tables;

@Component
public class Constants {

    private List<Pair<Integer, String>> gregorianMonthRuGenLcLong = new ArrayList<>();
    private Map<String, Long> userRight = new HashMap<>();

    public Constants() {
    }

    public List<Pair<Integer, String>> getGregorianMonthRuGenLcLong() {
        if (gregorianMonthRuGenLcLong.isEmpty()) {
            int i = 1;
            for (String month : Tables.GREGORIAN_MONTH_RU_GEN_LC_LONG) {
                gregorianMonthRuGenLcLong.add(Pair.of(i++, month));
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

}
