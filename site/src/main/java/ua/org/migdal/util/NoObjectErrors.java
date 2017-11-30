package ua.org.migdal.util;

import org.springframework.lang.Nullable;
import org.springframework.validation.AbstractBindingResult;

public class NoObjectErrors extends AbstractBindingResult {

    public NoObjectErrors(String objectName) {
        super(objectName);
    }

    @Nullable
    @Override
    public Object getTarget() {
        return null;
    }

    @Nullable
    @Override
    protected Object getActualFieldValue(String s) {
        return null;
    }

}
