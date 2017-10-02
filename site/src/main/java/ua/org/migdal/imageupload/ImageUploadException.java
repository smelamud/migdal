package ua.org.migdal.imageupload;

public class ImageUploadException extends RuntimeException {

    private String fieldName;
    private String errorCode;

    public ImageUploadException(String errorCode) {
        super(getMessageText(errorCode));
        this.errorCode = errorCode;
    }

    public ImageUploadException(String errorCode, Throwable cause) {
        super(getMessageText(errorCode), cause);
        this.errorCode = errorCode;
    }

    private static String getMessageText(String errorCode) {
        return "Error uploading image: " + errorCode;
    }

    public String getFieldName() {
        return fieldName;
    }

    public void setFieldName(String fieldName) {
        this.fieldName = fieldName;
    }

    public String getFieldErrorCode() {
        return fieldName != null ? String.format("%s.%s", fieldName, errorCode) : errorCode;
    }

}