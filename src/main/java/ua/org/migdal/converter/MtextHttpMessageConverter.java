package ua.org.migdal.converter;

import static java.nio.charset.StandardCharsets.UTF_8;

import java.io.IOException;
import java.io.OutputStreamWriter;
import java.io.Writer;

import javax.annotation.Nullable;

import org.springframework.http.ContentDisposition;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpInputMessage;
import org.springframework.http.HttpOutputMessage;
import org.springframework.http.MediaType;
import org.springframework.http.converter.AbstractHttpMessageConverter;
import org.springframework.http.converter.HttpMessageNotReadableException;
import org.springframework.http.converter.HttpMessageNotWritableException;

import ua.org.migdal.mtext.Mtext;

public class MtextHttpMessageConverter extends AbstractHttpMessageConverter<Mtext> {

    public MtextHttpMessageConverter() {
        super(UTF_8, MediaType.TEXT_XML);
    }

    @Override
    protected void addDefaultHeaders(HttpHeaders headers, Mtext mtext, @Nullable MediaType contentType)
            throws IOException {

        super.addDefaultHeaders(headers, mtext, contentType);
        headers.setContentDisposition(
                ContentDisposition.builder("inline")
                                  .filename(String.format("migdal-%d.xml", mtext.getId()))
                                  .build());
    }

    @Override
    protected boolean supports(Class<?> clazz) {
        return Mtext.class.isAssignableFrom(clazz);
    }

    @Override
    protected Mtext readInternal(Class<? extends Mtext> clazz, HttpInputMessage inputMessage)
            throws HttpMessageNotReadableException {

        throw new HttpMessageNotReadableException("Reading Mtext is not implemented");
    }

    @Override
    protected void writeInternal(Mtext mtext, HttpOutputMessage outputMessage)
            throws IOException, HttpMessageNotWritableException {

        Writer writer = new OutputStreamWriter(outputMessage.getBody(), UTF_8);
        writer.write("<?xml version=\"1.0\"?>\n");
        writer.write(mtext.getXmlStrict());
        writer.flush();
    }

}
