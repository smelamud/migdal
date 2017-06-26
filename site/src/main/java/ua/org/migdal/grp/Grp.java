package ua.org.migdal.grp;

import java.io.IOException;
import java.util.List;

import javax.annotation.PostConstruct;
import javax.inject.Inject;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.context.ApplicationContext;
import org.springframework.stereotype.Component;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.dataformat.yaml.YAMLFactory;

@Component
public class Grp {

    private static Logger log = LoggerFactory.getLogger(Grp.class);

    private List<GrpDescriptor> grps;

    @Inject
    private ApplicationContext applicationContext;

    @PostConstruct
    public void init() throws IOException {
        ObjectMapper mapper = new ObjectMapper(new YAMLFactory());
        grps = mapper.readValue(applicationContext.getResource("classpath:grps.yaml").getInputStream(),
                new TypeReference<List<GrpDescriptor>>() {
                });
        log.info("Loaded {} grps:", grps.size());
        for (GrpDescriptor grp : grps) {
            log.info("- {}", grp.getName());
        }
    }

}