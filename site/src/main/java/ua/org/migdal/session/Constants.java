package ua.org.migdal.session;

import java.util.ArrayList;
import java.util.List;

import org.springframework.data.util.Pair;
import org.springframework.stereotype.Component;
import ua.org.migdal.helper.calendar.Tables;

@Component
public class Constants {

    private List<Pair<Integer, String>> gregorianMonthRuGenLcLong = new ArrayList<>();

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

}
