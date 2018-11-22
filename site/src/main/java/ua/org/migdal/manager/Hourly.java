package ua.org.migdal.manager;

import java.lang.annotation.ElementType;
import java.lang.annotation.Retention;
import java.lang.annotation.RetentionPolicy;
import java.lang.annotation.Target;

import org.springframework.scheduling.annotation.Scheduled;

@Target(ElementType.METHOD)
@Retention(RetentionPolicy.RUNTIME)
@Scheduled(fixedDelayString = "PT1H")
public @interface Hourly {
}
