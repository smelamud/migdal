package ua.org.migdal.helper;

import java.io.IOException;

import javax.inject.Inject;

import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.session.RequestContext;

@HelperSource
public class LocationHelperSource {

    @Inject
    private RequestContext requestContext;

    public CharSequence location(Object paramName, Object paramValue) throws IOException {
        return UriComponentsBuilder.fromUriString(requestContext.getLocation())
                .replaceQueryParam(paramName.toString(), paramValue.toString())
                .build(true)
                .toUriString();
    }

}