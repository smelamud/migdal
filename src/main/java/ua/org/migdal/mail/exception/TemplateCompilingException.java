package ua.org.migdal.mail.exception;

public class TemplateCompilingException extends MailServiceException {

    public TemplateCompilingException(String templateName) {
        super("Template compiling exception: " + templateName);
    }

    public TemplateCompilingException(String templateName, Throwable cause) {
        super("Template compiling exception: " + templateName, cause);
    }

}