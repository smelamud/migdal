package ua.org.migdal.manager;

import java.lang.annotation.ElementType;
import java.lang.annotation.Retention;
import java.lang.annotation.RetentionPolicy;
import java.lang.annotation.Target;

import javax.transaction.Transactional;

import org.springframework.scheduling.annotation.Scheduled;

@Target(ElementType.METHOD)
@Retention(RetentionPolicy.RUNTIME)
@Scheduled(fixedDelayString = "P1D")
@Transactional
public @interface Daily {
}
