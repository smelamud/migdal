package ua.org.migdal.mail.exception;

public class SendMailInterruptedException extends MailServiceException {

    public SendMailInterruptedException() {
        super("Send mail was interrupted");
    }

}