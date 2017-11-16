package ua.org.migdal.grp;

import java.io.IOException;
import java.util.ArrayList;
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

import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.util.Utils;

@Component
public class GrpEnum {

    private static Logger log = LoggerFactory.getLogger(GrpEnum.class);

    private static GrpEnum instance;

    public long all;
    private Map<String, Long> groups = new HashMap<>();

    private GrpDescriptor grpNone;
    private List<GrpDescriptor> grps = new ArrayList<>();
    private Map<Long, GrpDescriptor> grpMap = new HashMap<>();
    private Map<String, GrpDescriptor> grpNameMap = new HashMap<>();

    @Inject
    private ApplicationContext applicationContext;

    @Inject
    private IdentManager identManager;

    @PostConstruct
    public void init() throws IOException {
        instance = this;

        ObjectMapper mapper = new ObjectMapper(new YAMLFactory());
        List<GrpDescriptor> data = mapper.readValue(
                applicationContext.getResource("classpath:grps.yaml").getInputStream(),
                new TypeReference<List<GrpDescriptor>>() {
                });
        log.info("Loaded {} grps:", data.size());

        ExpressionParser parser = new SpelExpressionParser();
        for (GrpDescriptor grpDesc : data) {
            if (grpDesc.getName().equals("NONE")) {
                log.info("- NONE");
                grpNone = grpDesc;
                continue;
            }

            log.info("- {}({})", grpDesc.getName(), grpDesc.getBit());
            grps.add(grpDesc);
            grpDesc.parseExpressions(identManager, parser);
            all |= grpDesc.getValue();
            grpMap.put(grpDesc.getValue(), grpDesc);
            grpNameMap.put(grpDesc.getName(), grpDesc);
            groups.put(grpDesc.getName(), grpDesc.getValue());
            for (String groupName : grpDesc.getGroups()) {
                if (!groups.containsKey(groupName)) {
                    groups.put(groupName, grpDesc.getValue());
                } else {
                    groups.put(groupName, groups.get(groupName) | grpDesc.getValue());
                }
            }
        }
        grps.forEach(grp -> grp.fillHiddenEditors(grpNone));
    }

    public static GrpEnum getInstance() {
        return instance;
    }

    public List<GrpDescriptor> getGrps() {
        return grps;
    }

    public GrpDescriptor getGrpNone() {
        return grpNone;
    }

    public GrpDescriptor grp(long grp) {
        return grpMap.get(grp);
    }

    public GrpDescriptor grp(String name) {
        return grpNameMap.get(name);
    }

    public boolean exists(long grp) {
        return grp(grp) != null;
    }

    public long grpValue(String name) {
        return grp(name).getValue();
    }

    public long[] group(String name) {
        return parse(groupValue(name));
    }

    public long groupValue(String name) {
        Long value = groups.get(name);
        return value != null ? value : 0;
    }

    public boolean inGroup(String name, long grp) {
        return (grpValue(name) & grp) != 0;
    }

    public long[] parse(long grp) {
        return Utils.toArray(grps.stream()
                .map(GrpDescriptor::getValue)
                .filter(value -> (grp & value) != 0)
                .collect(Collectors.toList()));
    }

}