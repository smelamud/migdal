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

    /**
     * Grp mask that includes all grps
     */
    public long all;
    /**
     * Mapping from name of a grp or a compound group to its grp mask
     */
    private Map<String, Long> groups = new HashMap<>();

    /**
     * Descriptor of the "NONE" grp with default settings
     */
    private GrpDescriptor grpNone;
    /**
     * List of descriptors of all defined grps
     */
    private List<GrpDescriptor> grps = new ArrayList<>();
    /**
     * Mapping from a grp value to its descriptor
     */
    private Map<Long, GrpDescriptor> grpMap = new HashMap<>();
    /**
     * Mapping from a grp name to its descriptor
     */
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

    /**
     * Get the list of descriptors of all defined grps
     */
    public List<GrpDescriptor> getGrps() {
        return grps;
    }

    /**
     * Get the descriptor of the "NONE" grp with default settings
     */
    public GrpDescriptor getGrpNone() {
        return grpNone;
    }

    /**
     * Get the descriptor by grp value
     */
    public GrpDescriptor grp(long grp) {
        return grpMap.get(grp);
    }

    /**
     * Get the descriptor by grp name
     */
    public GrpDescriptor grp(String name) {
        return grpNameMap.get(name);
    }

    /**
     * Check if a grp with the given value exists
     */
    public boolean exists(long grp) {
        return grp(grp) != null;
    }

    /**
     * Check if a grp with the given name exists
     */
    public boolean exists(String name) {
        return grp(name) != null;
    }

    /**
     * Get the grp value by its name
     */
    public long grpValue(String name) {
        return grp(name).getValue();
    }

    /**
     * Get the array of grp values that are included into a compound group by its name
     */
    public long[] group(String name) {
        return parse(groupValue(name));
    }

    /**
     * Get the grp mask by the name of a compound group
     */
    public long groupValue(String name) {
        Long value = groups.get(name);
        return value != null ? value : 0;
    }

    /**
     * Check if a grp with the given value is included into the compound group designated by name
     */
    public boolean inGroup(String name, long grp) {
        return (grpValue(name) & grp) != 0;
    }

    /**
     * Convert a grp mask to array of values
     */
    public long[] parse(long grp) {
        return Utils.toArray(grps.stream()
                .map(GrpDescriptor::getValue)
                .filter(value -> (grp & value) != 0)
                .collect(Collectors.toList()));
    }

}