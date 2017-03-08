package ua.org.migdal.mail;

import javax.mail.internet.MimeMessage;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.mail.javamail.JavaMailSender;
import org.springframework.mail.javamail.MimeMessageHelper;
import org.springframework.mail.javamail.MimeMessagePreparator;
import org.springframework.stereotype.Service;

@Service
public class MailService {

    @Autowired
    private JavaMailSender mailSender;

    public void sendMail() {
        MimeMessagePreparator messagePreparator = new MimeMessagePreparator() {

            @Override
            public void prepare(MimeMessage mimeMessage) throws Exception {
                MimeMessageHelper message = new MimeMessageHelper(mimeMessage);
                message.setTo("smelamud@redhat.com");
                message.setFrom("mailrobot@migdal.ru");
                message.setSubject("Subject of the test");
                message.setText("Just a test");
            }

        };
        mailSender.send(messagePreparator);
    }

}
