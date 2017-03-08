package ua.org.migdal.mail;

import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.ArrayDeque;
import java.util.Deque;
import java.util.Map;
import java.util.concurrent.BlockingQueue;
import java.util.concurrent.LinkedBlockingQueue;

import javax.annotation.PostConstruct;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.mail.javamail.JavaMailSender;
import org.springframework.mail.javamail.MimeMessageHelper;
import org.springframework.mail.javamail.MimeMessagePreparator;
import org.springframework.stereotype.Service;

import ua.org.migdal.Config;
import ua.org.migdal.data.User;
import ua.org.migdal.mail.exception.MailServiceException;
import ua.org.migdal.mail.exception.SendMailInterruptedException;

@Service
public class MailService {

    @Autowired
    private Config config;

    @Autowired
    private JavaMailSender mailSender;

    private BlockingQueue<MimeMessagePreparator> mailQueue = new LinkedBlockingQueue<>();

    @PostConstruct
    public void init() {
        Thread thread = new Thread(this::runMailQueue);
        thread.setDaemon(true);
        thread.start();
    }

    public void sendMail(User to, String templateName, Map<String, Object> model) throws MailServiceException {
        try {
            mailQueue.put(mimeMessage -> {
                MimeMessageHelper message = new MimeMessageHelper(mimeMessage, "UTF-8");
                message.setTo(to.getEmail());
                message.setFrom(config.getMailFromAddress());
                message.setReplyTo(config.getMailReplyToAddress());
                message.setSubject("Subject of the test");
                message.setText("Just a test");
            });
        } catch (InterruptedException e) {
            throw new SendMailInterruptedException();
        }
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
