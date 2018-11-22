package ua.org.migdal;

import javax.inject.Inject;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.autoconfigure.http.HttpMessageConverters;
import org.springframework.boot.context.properties.EnableConfigurationProperties;
import org.springframework.cache.annotation.EnableCaching;
import org.springframework.context.ApplicationContext;
import org.springframework.context.annotation.Bean;
import org.springframework.scheduling.annotation.EnableScheduling;
import org.springframework.session.jdbc.config.annotation.web.http.EnableJdbcHttpSession;
import org.springframework.session.web.http.CookieSerializer;
import org.springframework.session.web.http.DefaultCookieSerializer;
import org.springframework.web.servlet.config.annotation.InterceptorRegistry;
import org.springframework.web.servlet.config.annotation.ResourceHandlerRegistry;
import org.springframework.web.servlet.config.annotation.ViewResolverRegistry;
import org.springframework.web.servlet.config.annotation.WebMvcConfigurer;
import org.springframework.web.servlet.resource.PathResourceResolver;

import com.github.jknack.handlebars.springmvc.HandlebarsViewResolver;

import ua.org.migdal.controller.SaveCookiesInterceptor;
import ua.org.migdal.controller.SubdomainInterceptor;
import ua.org.migdal.controller.TrapInterceptor;
import ua.org.migdal.converter.MtextHttpMessageConverter;
import ua.org.migdal.converter.SyndFeedHttpMessageConverter;
import ua.org.migdal.helper.HelperSource;

@SpringBootApplication
@EnableConfigurationProperties(Config.class)
@EnableJdbcHttpSession
@EnableCaching
@EnableScheduling
public class MigdalApplication implements WebMvcConfigurer {

    private static Logger log = LoggerFactory.getLogger(MigdalApplication.class);

    @Inject
    private ApplicationContext applicationContext;

    @Inject
    private SubdomainInterceptor subdomainInterceptor;

    @Inject
    private TrapInterceptor trapInterceptor;

    @Inject
    private SaveCookiesInterceptor saveCookiesInterceptor;

    @Inject
    private Config config;

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

    @Bean
    public CookieSerializer cookieSerializer() {
        DefaultCookieSerializer serializer = new DefaultCookieSerializer();
        serializer.setDomainName(config.getSiteDomain());
        return serializer;
    }

    @Override
    public void configureViewResolvers(ViewResolverRegistry registry) {
        registry.viewResolver(handlebarsViewResolver());
    }

    @Override
    public void addInterceptors(InterceptorRegistry registry) {
        registry.addInterceptor(subdomainInterceptor);
        registry.addInterceptor(trapInterceptor).order(100); // To be executed after Hibernate session is created
        registry.addInterceptor(saveCookiesInterceptor);
    }

    @Override
    public void addResourceHandlers(ResourceHandlerRegistry registry) {
        registry.addResourceHandler(config.getImageUrl() + "/**")
                .addResourceLocations("file:" + config.getImageDir() + "/")
                .resourceChain(true)
                .addResolver(new PathResourceResolver());
    }

    @Bean
    public HttpMessageConverters customMessageConverters() {
        return new HttpMessageConverters(
                new SyndFeedHttpMessageConverter(),
                new MtextHttpMessageConverter());
    }

    public static void main(String[] args) {
        SpringApplication.run(MigdalApplication.class, args);
    }

}
