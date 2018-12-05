package ua.org.migdal.util;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import org.springframework.web.client.RestTemplate;
import org.springframework.web.util.UriComponentsBuilder;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import ua.org.migdal.Config;

@Service
public class Captcha {

    @JsonIgnoreProperties(ignoreUnknown = true)
    private static class SiteVerifyResponse {

        public boolean success;

        @JsonProperty("error-codes")
        public String[] errorCodes; // for debug

    }

    @Inject
    private Config config;

    public boolean valid(String captchaResponse) {
        UriComponentsBuilder builder = UriComponentsBuilder.fromHttpUrl(
                "https://www.google.com/recaptcha/api/siteverify");
        builder.queryParam("secret", config.getCaptchaSecretKey());
        builder.queryParam("response", captchaResponse);
        SiteVerifyResponse response =
                new RestTemplate().postForObject(builder.build(true).toUri(), null, SiteVerifyResponse.class);
        return response != null && response.success;
    }

}
