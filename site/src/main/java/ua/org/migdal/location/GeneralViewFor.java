package ua.org.migdal.location;

import java.lang.annotation.ElementType;
import java.lang.annotation.Retention;
import java.lang.annotation.RetentionPolicy;
import java.lang.annotation.Target;

@Target(ElementType.METHOD)
@Retention(RetentionPolicy.RUNTIME)
public @interface GeneralViewFor {

    String value() default "^.*$";
    GeneralViewPriority priority() default GeneralViewPriority.REGULAR;

}
