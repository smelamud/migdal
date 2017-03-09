package ua.org.migdal.mail;

import java.io.IOException;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.ArrayDeque;
import java.util.Deque;
import java.util.HashMap;
import java.util.Map;
import java.util.concurrent.BlockingQueue;
import java.util.concurrent.LinkedBlockingQueue;

import javax.annotation.PostConstruct;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.mail.javamail.JavaMailSender;
import org.springframework.mail.javamail.MimeMessageHelper;
import org.springframework.mail.javamail.MimeMessagePreparator;
import org.springframework.stereotype.Service;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Template;
import com.github.jknack.handlebars.springmvc.HandlebarsViewResolver;

import ua.org.migdal.Config;
import ua.org.migdal.data.User;
import ua.org.migdal.mail.exception.MailServiceException;
import ua.org.migdal.mail.exception.SendMailInterruptedException;
import ua.org.migdal.mail.exception.TemplateCompilingException;
import ua.org.migdal.util.XmlConverter;

@Service
public class MailService {

    private Logger log = LoggerFactory.getLogger(MailService.class);

    @Autowired
    private Config config;

    @Autowired
    private JavaMailSender mailSender;

    @Autowired
    private HandlebarsViewResolver handlebarsViewResolver;

    private BlockingQueue<MimeMessagePreparator> mailQueue = new LinkedBlockingQueue<>();

    private Map<String, Template> compiledTemplates = new HashMap<>();

    private Handlebars getHandlebars() {
        return handlebarsViewResolver.getHandlebars();
    }

    @PostConstruct
    public void init() {
        Thread thread = new Thread(this::runMailQueue);
        thread.setDaemon(true);
        thread.start();
    }

    public void sendMail(User to, String templateName, Map<String, Object> model) throws MailServiceException {
        try {
            mailQueue.put(mimeMessage -> {
                String document = getDocument(to, templateName, model);
                MailXmlToText handler = new MailXmlToText();
                XmlConverter.convert(document, handler);

                MimeMessageHelper message = new MimeMessageHelper(mimeMessage, "UTF-8");
                message.setTo(to.getEmail());
                message.setFrom(config.getMailFromAddress());
                message.setReplyTo(config.getMailReplyToAddress());
                message.setSubject(handler.getResult().getSubject().toString());
                message.setText(handler.getResult().getBody().toString());
            });
        } catch (InterruptedException e) {
            throw new SendMailInterruptedException();
        }
    }

    private String getDocument(User to, String templateName, Map<String, Object> model) throws MailServiceException {
        Template template = getTemplate(templateName);
        Map<String, Object> fullModel = new HashMap<>();
        fullModel.putAll(model);
        fullModel.put("to", to);
        fullModel.put("config", config);
        try {
            return template.apply(fullModel);
        } catch (IOException e) {
            log.error("I/O error when compiling template: " + templateName, e);
            throw new TemplateCompilingException(templateName, e);
        }
    }

    private Template getTemplate(String templateName) throws MailServiceException {
        Template template = compiledTemplates.get(templateName);
        if (template == null) {
            try {
                template = getHandlebars().compile("mailings/" + templateName);
            } catch (IOException e) {
                log.error("I/O error when compiling template: " + templateName, e);
                throw new TemplateCompilingException(templateName, e);
            }
            compiledTemplates.put(templateName, template);
        }
        return template;
    }

    private void runMailQueue() {
        Deque<Instant> sent = new ArrayDeque<>();

        while (true) {
            try {
                while (sent.size() >= config.getMailSendLimit()) {
                    while (sent.peekFirst() != null && sent.peekFirst()
                            .isBefore(Instant.now().minus(config.getMailSendPeriod(), ChronoUnit.MINUTES))) {
                        sent.pollFirst();
                    }
                    if (sent.size() < config.getMailSendLimit()) {
                        break;
                    }
                    Thread.sleep(1000);
                }
                mailSender.send(mailQueue.take());
                sent.offerLast(Instant.now());
            } catch (InterruptedException e) {
            }
        }
    }

}
