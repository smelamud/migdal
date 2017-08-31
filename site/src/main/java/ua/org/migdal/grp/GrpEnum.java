package ua.org.migdal.grp;

import java.io.IOException;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

import javax.annotation.PostConstruct;
import javax.inject.Inject;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.context.ApplicationContext;
import org.springframework.expression.ExpressionParser;
import org.springframework.expression.spel.standard.SpelExpressionParser;
import org.springframework.stereotype.Component;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.dataformat.yaml.YAMLFactory;

import ua.org.migdal.util.Utils;

@Component
public class GrpEnum {

    private static Logger log = LoggerFactory.getLogger(GrpEnum.class);

    private static GrpEnum instance;

    public long all;
    private Map<String, Long> groups = new HashMap<>();

    private List<GrpDescriptor> grps;
    private Map<Long, GrpDescriptor> grpMap = new HashMap<>();

    @Inject
    private ApplicationContext applicationContext;

    @PostConstruct
    public void init() throws IOException {
        instance = this;

        ObjectMapper mapper = new ObjectMapper(new YAMLFactory());
        grps = mapper.readValue(applicationContext.getResource("classpath:grps.yaml").getInputStream(),
                new TypeReference<List<GrpDescriptor>>() {
                });
        log.info("Loaded {} grps:", grps.size());
        ExpressionParser parser = new SpelExpressionParser();
        for (GrpDescriptor grp : grps) {
            log.info("- {}({})", grp.getName(), grp.getBit());
            grp.parseExpressions(parser);
            all |= grp.getValue();
            grpMap.put(grp.getValue(), grp);
            for (String groupName : grp.getGroups()) {
                if (!groups.containsKey(groupName)) {
                    groups.put(groupName, grp.getValue());
                } else {
                    groups.put(groupName, groups.get(groupName) | grp.getValue());
                }
            }
        }
    }

    public static GrpEnum getInstance() {
        return instance;
    }

    public List<GrpDescriptor> getGrps() {
        return grps;
    }

    public GrpDescriptor grp(long grp) {
        return grpMap.get(grp);
    }

    public long[] group(String name) {
        return parse(groupValue(name));
    }

    public long groupValue(String name) {
        Long value = groups.get(name);
        return value != null ? value : 0;
    }

    public long[] parse(long grp) {
        return Utils.toArray(grps.stream()
                .map(GrpDescriptor::getValue)
                .filter(value -> (grp & value) != 0)
                .collect(Collectors.toList()));
    }

}