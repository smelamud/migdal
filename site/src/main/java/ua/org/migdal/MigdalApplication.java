package ua.org.migdal;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import javax.inject.Inject;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.context.properties.EnableConfigurationProperties;
import org.springframework.cache.annotation.EnableCaching;
import org.springframework.context.ApplicationContext;
import org.springframework.context.annotation.Bean;
import org.springframework.session.jdbc.config.annotation.web.http.EnableJdbcHttpSession;
import org.springframework.web.servlet.config.annotation.InterceptorRegistry;
import org.springframework.web.servlet.config.annotation.ViewResolverRegistry;
import org.springframework.web.servlet.config.annotation.WebMvcConfigurerAdapter;

import com.github.jknack.handlebars.springmvc.HandlebarsViewResolver;

import ua.org.migdal.controller.SubdomainInterceptor;
import ua.org.migdal.helper.HelperSource;

@SpringBootApplication
@EnableConfigurationProperties(Config.class)
@EnableJdbcHttpSession
@EnableCaching
public class MigdalApplication extends WebMvcConfigurerAdapter {

    private Logger log = LoggerFactory.getLogger(MigdalApplication.class);

    @Inject
    private ApplicationContext applicationContext;

    @Inject
    private SubdomainInterceptor subdomainInterceptor;

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

    @Override
    public void configureViewResolvers(ViewResolverRegistry registry) {
        registry.viewResolver(handlebarsViewResolver());
    }

    @Override
    public void addInterceptors(InterceptorRegistry registry) {
        registry.addInterceptor(subdomainInterceptor);
    }

    public static void main(String[] args) {
        SpringApplication.run(MigdalApplication.class, args);
    }

}
