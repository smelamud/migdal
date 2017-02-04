package ua.org.migdal;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.context.properties.EnableConfigurationProperties;
import org.springframework.context.ApplicationContext;
import org.springframework.web.servlet.config.annotation.InterceptorRegistry;
import org.springframework.web.servlet.config.annotation.ViewResolverRegistry;
import org.springframework.web.servlet.config.annotation.WebMvcConfigurerAdapter;

import com.github.jknack.handlebars.springmvc.HandlebarsViewResolver;

import ua.org.migdal.controller.SubdomainInterceptor;
import ua.org.migdal.helper.HelperSource;

@SpringBootApplication
@EnableConfigurationProperties(Config.class)
public class MigdalApplication extends WebMvcConfigurerAdapter {

	private Logger log = LoggerFactory.getLogger(MigdalApplication.class);

	@Autowired
	private ApplicationContext applicationContext;

	@Autowired
    private SubdomainInterceptor subdomainInterceptor;

    @Override
    public void configureViewResolvers(ViewResolverRegistry registry) {
        HandlebarsViewResolver resolver = new HandlebarsViewResolver();
        resolver.setPrefix("classpath:/templates/");
        resolver.setSuffix(".hbs.html");
        for (Object helperSource : applicationContext.getBeansWithAnnotation(HelperSource.class).values()) {
            log.info("Registering Handlebars helper class {}", helperSource.getClass().getName());
            resolver.registerHelpers(helperSource);
        }
        registry.viewResolver(resolver);
    }

    @Override
    public void addInterceptors(InterceptorRegistry registry) {
        registry.addInterceptor(subdomainInterceptor);
    }

	public static void main(String[] args) {
		SpringApplication.run(MigdalApplication.class, args);
	}

}
