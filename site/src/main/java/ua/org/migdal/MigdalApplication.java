package ua.org.migdal;

import com.github.jknack.handlebars.springmvc.HandlebarsViewResolver;
import org.springframework.boot.context.properties.EnableConfigurationProperties;
import ua.org.migdal.helper.HelperSource;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.context.ApplicationContext;
import org.springframework.context.annotation.Bean;

@SpringBootApplication
@EnableConfigurationProperties(Config.class)
public class MigdalApplication {

	private Logger log = LoggerFactory.getLogger(MigdalApplication.class);

	@Autowired
	private ApplicationContext applicationContext;

	@Bean
	public HandlebarsViewResolver handlebarsViewResolver() {
		HandlebarsViewResolver resolver = new HandlebarsViewResolver();
		resolver.setPrefix("classpath:/templates/");
		resolver.setSuffix(".hbs.html");
		for (Object helperSource : applicationContext.getBeansWithAnnotation(HelperSource.class).values()) {
			log.info("Registering Handlebars helper class {}", helperSource.getClass().getName());
			resolver.registerHelpers(helperSource);
		}
        return resolver;
	}

	public static void main(String[] args) {
		SpringApplication.run(MigdalApplication.class, args);
	}

}
