package ua.org.migdal.grp;

import java.io.IOException;
import java.util.List;
import java.util.stream.Collectors;

import javax.annotation.PostConstruct;
import javax.inject.Inject;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.context.ApplicationContext;
import org.springframework.stereotype.Component;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.dataformat.yaml.YAMLFactory;

import ua.org.migdal.util.Utils;

@Component
public class GrpEnum {

    private static Logger log = LoggerFactory.getLogger(GrpEnum.class);

    private static GrpEnum instance;

    private List<GrpDescriptor> grps;

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
        for (GrpDescriptor grp : grps) {
            log.info("- {}({})", grp.getName(), grp.getBit());
        }
    }

    public static GrpEnum getInstance() {
        return instance;
    }

    public List<GrpDescriptor> getGrps() {
        return grps;
    }

    public long[] parse(long grp) {
        return Utils.toArray(grps.stream()
                .map(GrpDescriptor::getValue)
                .filter(value -> (grp & value) != 0)
                .collect(Collectors.toList()));
    }

}