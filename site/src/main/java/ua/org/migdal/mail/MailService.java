package ua.org.migdal.mail;

import java.util.Map;
import java.util.concurrent.BlockingQueue;
import java.util.concurrent.LinkedBlockingQueue;

import javax.annotation.PostConstruct;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.mail.javamail.JavaMailSender;
import org.springframework.mail.javamail.MimeMessageHelper;
import org.springframework.mail.javamail.MimeMessagePreparator;
import org.springframework.stereotype.Service;

import ua.org.migdal.data.User;
import ua.org.migdal.mail.exception.MailServiceException;
import ua.org.migdal.mail.exception.SendMailInterruptedException;

@Service
public class MailService {

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
                MimeMessageHelper message = new MimeMessageHelper(mimeMessage);
                message.setTo(to.getEmail());
                message.setFrom("mailrobot@migdal.ru");
                message.setSubject("Subject of the test");
                message.setText("Just a test");
            });
        } catch (InterruptedException e) {
            throw new SendMailInterruptedException();
        }
    }

    private void runMailQueue() {
        while (true) {
            try {
                mailSender.send(mailQueue.take());
            } catch (InterruptedException e) {
            }
        }
    }

}
