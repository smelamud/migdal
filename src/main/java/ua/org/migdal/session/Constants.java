package ua.org.migdal.session;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import javax.inject.Inject;

import com.google.common.collect.Lists;
import org.springframework.stereotype.Component;

import ua.org.migdal.data.ImagePlacement;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.PostingModbit;
import ua.org.migdal.data.TopicModbit;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.helper.calendar.Tables;
import ua.org.migdal.helper.util.Constant;
import ua.org.migdal.text.TextFormat;

@Component
public class Constants {

    @Inject
    private GrpEnum grpEnum;

    private List<Constant<Integer>> gregorianMonthRuGenLcLong = new ArrayList<>();
    private List<Constant<Integer>> gregorianMonthRuNomLcLong = new ArrayList<>();
    private Map<String, Long> userRight = new HashMap<>();
    private Map<String, Long> postingModbit = new HashMap<>();
    private Map<String, Integer> linkType = new HashMap<>();
    private List<Constant<String>> langs = new ArrayList<>();
    private Map<String, Long> grp = new HashMap<>();
    private Map<String, Short> imagePlacement = new HashMap<>();
    
    public Constants() {
    }

    public List<Constant<Integer>> getGregorianMonthRuNomLcLong() {
        if (gregorianMonthRuNomLcLong.isEmpty()) {
            int i = 1;
            for (String month : Tables.GREGORIAN_MONTH_RU_NOM_LC_LONG) {
                gregorianMonthRuNomLcLong.add(new Constant<>(month, i++));
            }
        }
        return gregorianMonthRuNomLcLong;
    }

    public List<Constant<Integer>> getGregorianMonthRuNomLcLongReverse() {
        return Lists.reverse(getGregorianMonthRuNomLcLong());
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

    public Map<String, Long> getGrp() {
        if (grp.isEmpty()) {
            for (GrpDescriptor grpDescriptor : grpEnum.getGrps()) {
                grp.put(grpDescriptor.getName(), grpDescriptor.getValue());
            }
        }
        return grp;
    }

    public Map<String, Short> getImagePlacement() {
        if (imagePlacement.isEmpty()) {
            imagePlacement.put("CENTERLEFT", ImagePlacement.CENTERLEFT);
            imagePlacement.put("CENTER", ImagePlacement.CENTER);
            imagePlacement.put("CENTERRIGHT", ImagePlacement.CENTERRIGHT);
            imagePlacement.put("BOTTOMLEFT", ImagePlacement.BOTTOMLEFT);
            imagePlacement.put("BOTTOMRIGHT", ImagePlacement.BOTTOMRIGHT);
        }
        return imagePlacement;
    }
    
}