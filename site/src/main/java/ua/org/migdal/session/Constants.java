package ua.org.migdal.session;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.springframework.stereotype.Component;

import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.PostingModbit;
import ua.org.migdal.data.TopicModbit;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.helper.calendar.Tables;
import ua.org.migdal.helper.util.Constant;
import ua.org.migdal.text.TextFormat;

@Component
public class Constants {

    private List<Constant<Integer>> gregorianMonthRuGenLcLong = new ArrayList<>();
    private Map<String, Long> userRight = new HashMap<>();
    private Map<String, Long> postingModbit = new HashMap<>();
    private Map<String, Integer> linkType = new HashMap<>();
    private List<Constant<String>> langs = new ArrayList<>();

    public Constants() {
    }

    public List<Constant<Integer>> getGregorianMonthRuGenLcLong() {
        if (gregorianMonthRuGenLcLong.isEmpty()) {
            int i = 1;
            for (String month : Tables.GREGORIAN_MONTH_RU_GEN_LC_LONG) {
                gregorianMonthRuGenLcLong.add(new Constant<>(month, i++));
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

    public Map<String, Long> getPostingModbit() {
        if (postingModbit.isEmpty()) {
            for (PostingModbit modbit : PostingModbit.values()) {
                postingModbit.put(modbit.name(), modbit.getValue());
            }
        }
        return postingModbit;
    }

    public TopicModbit[] getTopicModbits() {
        return TopicModbit.values();
    }

    public List<Constant<String>> getLangs() {
        if (langs.isEmpty()) {
            langs.add(new Constant<>("Русский", "ru"));
            langs.add(new Constant<>("Английский", "en"));
            langs.add(new Constant<>("Иврит", "he"));
            langs.add(new Constant<>("Украинский", "uk"));
            langs.add(new Constant<>("Белорусский", "be"));
            langs.add(new Constant<>("Идиш", "yi"));
            langs.add(new Constant<>("Немецкий", "de"));
        }
        return langs;
    }

    public TextFormat[] getTextFormats() {
        return TextFormat.values();
    }

    public Map<String, Integer> getLinkType() {
        if (linkType.isEmpty()) {
            for (LinkType type : LinkType.values()) {
                linkType.put(type.name(), type.ordinal());
            }
        }
        return linkType;
    }

}