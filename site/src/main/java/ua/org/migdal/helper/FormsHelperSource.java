package ua.org.migdal.helper;

import java.io.IOException;

import javax.inject.Inject;

import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.helper.exception.AmbiguousArgumentsException;
import ua.org.migdal.helper.util.HelperUtils;

@HelperSource
public class FormsHelperSource {

    @Inject
    private ImagesHelperSource imagesHelperSource;

    public CharSequence hidden(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"hidden\"");
        HelperUtils.appendMandatoryArgAttr(buf, "name", options);
        HelperUtils.appendArgAttr(buf, "value", "", options);
        buf.append('>');
        return new SafeString(buf);
    }

    CharSequence hidden(String name, Object value, CharSequence klass) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"hidden\" name=\"");
        buf.append(name);
        buf.append("\" value=\"");
        buf.append(value);
        if (klass != null) {
            buf.append("\" class=\"");
            buf.append(klass);
        }
        buf.append("\">");
        return new SafeString(buf);
    }

    public CharSequence checkboxButton(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"checkbox\"");
        HelperUtils.appendMandatoryArgAttr(buf, "name", options);
        HelperUtils.appendArgAttr(buf, "value", "1", options);
        HelperUtils.appendArgAttr(buf, "checked", false, options);
        HelperUtils.appendOptionalArgAttr(buf, "id", options);
        HelperUtils.appendOptionalArgAttr(buf, "class", options);
        buf.append('>');
        return new SafeString(buf);
    }

    CharSequence checkboxButton(CharSequence name, Object value, boolean checked, CharSequence id, CharSequence cls) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"checkbox\"");
        HelperUtils.appendAttr(buf, "name", name);
        HelperUtils.appendAttr(buf, "value", value);
        HelperUtils.appendAttr(buf, "checked", checked);
        HelperUtils.appendAttr(buf, "id", id);
        HelperUtils.appendAttr(buf, "class", cls);
        buf.append('>');
        return new SafeString(buf);
    }

    public CharSequence checkbox(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(checkboxButton(options));
        buf.append(' ');
        HelperUtils.safeAppend(buf, options.hash("title", ""));
        buf.append("</label>");
        return new SafeString(buf);
    }

    CharSequence checkbox(CharSequence title, CharSequence name, Object value, boolean checked, CharSequence id,
                          CharSequence cls) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(checkboxButton(name, value, checked, id, cls));
        buf.append(' ');
        HelperUtils.safeAppend(buf, title);
        buf.append("</label>");
        return new SafeString(buf);
    }

    public CharSequence radioButton(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"radio\"");
        HelperUtils.appendMandatoryArgAttr(buf, "name", options);
        HelperUtils.appendMandatoryArgAttr(buf, "value", options);
        HelperUtils.appendArgAttr(buf, "checked", false, options);
        HelperUtils.appendOptionalArgAttr(buf, "id", options);
        HelperUtils.appendOptionalArgAttr(buf, "class", options);
        HelperUtils.appendOptionalArgAttr(buf, "onclick", options);
        buf.append('>');
        return new SafeString(buf);
    }

    CharSequence radioButton(CharSequence name, Object value, boolean checked, CharSequence id, CharSequence cls) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"radio\"");
        HelperUtils.appendAttr(buf, "name", name);
        HelperUtils.appendAttr(buf, "value", value);
        HelperUtils.appendAttr(buf, "checked", checked);
        HelperUtils.appendAttr(buf, "id", id);
        HelperUtils.appendAttr(buf, "class", cls);
        buf.append('>');
        return new SafeString(buf);
    }

    public CharSequence radio(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(radioButton(options));
        buf.append(' ');
        HelperUtils.safeAppend(buf, options.hash("title", ""));
        buf.append("</label>");
        return new SafeString(buf);
    }

    CharSequence radio(CharSequence name, Object value, boolean checked, CharSequence id, CharSequence cls,
                       CharSequence title) {
        StringBuilder buf = new StringBuilder();
        if (!StringUtils.isEmpty(title)) {
            buf.append("<label>");
        }
        buf.append(radioButton(name, value, checked, id, cls));
        if (!StringUtils.isEmpty(title)) {
            buf.append(' ');
            HelperUtils.safeAppend(buf, title);
            buf.append("</label>");
        }
        return new SafeString(buf);
    }

    public CharSequence selectOption(Options options) {
        Object value = HelperUtils.mandatoryHash("value", options);
        Object selectedValue = options.hash("selectedValue");
        CharSequence title = options.hash("title", "");
        if (selectedValue == null) {
            boolean selected = HelperUtils.boolArg(options.hash("selected", false));
            return selectOption(value, selected, null, title);
        } else {
            if (options.hash("selected") != null) {
                throw new AmbiguousArgumentsException("selected", "selectedValue");
            }
            return selectOption(value, false, selectedValue, title);
        }
    }

    CharSequence selectOption(Object value, boolean selected, Object selectedValue, CharSequence title) {
        StringBuilder buf = new StringBuilder();
        buf.append("<option");
        HelperUtils.appendAttr(buf, "value", value);
        if (selectedValue != null) {
            if (selectedValue instanceof Number && value instanceof Number) {
                selected = ((Number) selectedValue).longValue() == ((Number) value).longValue();
            } else {
                selected = selectedValue.equals(value);
            }
        }
        HelperUtils.appendAttr(buf, "selected", selected);
        buf.append('>');
        HelperUtils.safeAppend(buf, title);
        buf.append("</option>");
        return new SafeString(buf);
    }

    public CharSequence formTable(Options options) throws IOException {
        CharSequence title = options.hash("title", "");
        boolean mini = HelperUtils.boolArg(options.hash("mini", false));
        boolean miniTitle = HelperUtils.boolArg(options.hash("miniTitle", false));
        String width = mini ? "50%" : "95%";
        if (miniTitle) {
            options.data("formStar", false);
            options.data("formTitleWidth", "10%");
            options.data("formValueWidth", "90%");
        } else {
            options.data("formStar", true);
            options.data("formTitleWidth", "25%");
            options.data("formValueWidth", "75%");
        }
        StringBuilder buf = new StringBuilder();
        buf.append("<center><table class=\"form\"");
        HelperUtils.appendAttr(buf, "width", width);
        buf.append('>');
        if (title.length() > 0) {
            buf.append("<tr><td class=\"form-title\">");
            HelperUtils.safeAppend(buf, title);
            buf.append("</td></tr>");
        }
        buf.append("<tr><td><table class=\"form-layer\" width=\"100%\">");
        buf.append(options.apply(options.fn));
        buf.append("</table></td></tr></table></center>");
        return new SafeString(buf);
    }

    public CharSequence star(Options options) {
        return star(options.hash("mandatory", false));
    }

    private CharSequence star(boolean mandatory) {
        if (mandatory) {
            return new SafeString("<span class=\"form-star\">* </span>");
        } else {
            return new SafeString("<span class=\"form-star\" style=\"visibility: hidden\">* </span>");
        }
    }

    public CharSequence starInfo(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<tr valign=\"top\"><td class=\"form-cell eightpt\" colspan=\"2\">");
        buf.append("Поля, отмеченные ");
        buf.append(star(true));
        buf.append(", заполнять обязательно");
        buf.append("</td></tr>");
        return new SafeString(buf);
    }

    public CharSequence formLine(Options options) throws IOException {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        String name = HelperUtils.mandatoryHash("name", options);
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence comment = options.hash("comment", "");
        CharSequence id = options.hash("id", "");

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, name, mandatory, comment, id, options));
        buf.append(options.apply(options.fn));
        buf.append(formLineEnd());
        return new SafeString(buf);
    }

    private String fieldName(String name) {
        int pos = name.lastIndexOf('.');
        return pos >= 0 ? name.substring(pos + 1) : name;
    }

    CharSequence formLineBegin(CharSequence title, String name, boolean mandatory, CharSequence comment,
                               CharSequence id, Options options) {
        StringBuilder buf = new StringBuilder();
        if (id != null && id.length() > 0) {
            buf.append("<tr");
            HelperUtils.appendAttr(buf, "id", id);
            buf.append(" valign=\"top\">");
        } else {
            buf.append("<tr valign=\"top\">");
        }
        buf.append("<th class=\"form-cell\"");
        HelperUtils.appendAttr(buf, "width", options.get("formTitleWidth"));
        buf.append('>');
        if (options.get("formStar")) {
            buf.append(star(mandatory));
        }
        HelperUtils.safeAppend(buf, title);
        if (!StringUtils.isEmpty(comment)) {
            buf.append("<p class=\"form-comment\">");
            HelperUtils.safeAppend(buf, comment);
            buf.append("</p>");
        }
        buf.append("</th>");
        buf.append("<td class=\"form-cell ninept");
        if (options.get("errors") instanceof Errors) {
            Errors errors = (Errors) options.get("errors");
            if (errors.hasFieldErrors(fieldName(name))) {
                buf.append(" has-error");
            }
        }
        buf.append('"');
        HelperUtils.appendAttr(buf, "width", options.get("formValueWidth"));
        buf.append('>');
        return new SafeString(buf);
    }

    CharSequence formLineEnd() {
        return new SafeString("</td></tr>");
    }

    public CharSequence formComment(Options options) throws IOException {
        StringBuilder buf = new StringBuilder();
        buf.append("<tr><td class=\"form-cell\" colspan=\"2\">");
        buf.append(options.apply(options.fn));
        buf.append("</td></tr>");
        return new SafeString(buf);
    }

    public CharSequence formSection(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);

        StringBuilder buf = new StringBuilder();
        buf.append("<tr valign=\"top\">");
        buf.append("<th class=\"form-section\" colspan=\"2\">&nbsp;&nbsp;");
        HelperUtils.safeAppend(buf, title);
        buf.append("</th>");
        buf.append("</tr>");
        return new SafeString(buf);
    }

    public CharSequence edit(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"text\"");
        HelperUtils.appendMandatoryArgAttr(buf, "name", options);
        HelperUtils.appendMandatoryArgAttr(buf, "value", options);
        HelperUtils.appendArgAttr(buf, "size", "40", options);
        HelperUtils.appendArgAttr(buf, "maxlength", "250", options);
        HelperUtils.appendOptionalArgAttr(buf, "onkeypress", options);
        HelperUtils.appendOptionalArgAttr(buf, "id", options);
        HelperUtils.appendOptionalArgAttr(buf, "class", options);
        buf.append('>');
        return new SafeString(buf);
    }

    private CharSequence edit(CharSequence name, CharSequence value, CharSequence size, CharSequence maxlength,
                              CharSequence onkeypress, CharSequence id) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"text\"");
        HelperUtils.appendAttr(buf, "name", name);
        HelperUtils.appendAttr(buf, "value", value);
        HelperUtils.appendAttr(buf, "size", size);
        HelperUtils.appendAttr(buf, "maxlength", maxlength);
        HelperUtils.appendAttr(buf, "onkeypress", onkeypress);
        HelperUtils.appendAttr(buf, "id", id);
        buf.append('>');
        return new SafeString(buf);
    }

    CharSequence edit(CharSequence name, CharSequence value, CharSequence size, CharSequence maxlength,
                      CharSequence id) {
        return edit(name, value, size, maxlength, null, id);
    }

    public CharSequence formEdit(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        CharSequence value = HelperUtils.mandatoryHash("value", options);
        CharSequence size = options.hash("size", "40");
        CharSequence maxlength = options.hash("maxlength", "250");
        long xmlid = HelperUtils.intArg("xmlid", options.hash("xmlid"));

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, name, mandatory, comment, "", options));
        buf.append(edit(name, value, size, maxlength, null, null));
        if (xmlid != 0) {
            buf.append("<br>");
            buf.append(xmlText(xmlid, name));
        }
        buf.append(formLineEnd());
        return new SafeString(buf);
    }

    public CharSequence formPassword(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        CharSequence size = options.hash("size", "40");
        CharSequence maxlength = options.hash("maxlength", "250");

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, name, mandatory, comment, "", options));
        buf.append(String.format("<input type='password' name=\"%s\" size=\"%s\" maxlength=\"%s\">",
                                 HelperUtils.he(name), HelperUtils.he(size), HelperUtils.he(maxlength)));
        buf.append(formLineEnd());
        return new SafeString(buf);
    }

    public CharSequence formEditor(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        CharSequence body = HelperUtils.mandatoryHash("body", options);
        CharSequence rows = options.hash("rows", "10");
        long xmlid = HelperUtils.intArg("xmlid", options.hash("xmlid"));

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, name, mandatory, comment, "", options));
        buf.append(String.format("<textarea name=\"%s\" rows=\"%s\" wrap=\"virtual\" style=\"width: 100%%\">%s"
                        + "</textarea>", HelperUtils.he(name), HelperUtils.he(rows), HelperUtils.he(body)));
        if (xmlid != 0) {
            buf.append("<br>");
            buf.append(xmlText(xmlid, name));
        }
        buf.append(formLineEnd());
        return new SafeString(buf);
    }

    public CharSequence formCheckbox(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        Object value = options.hash("value", "1");
        boolean checked = HelperUtils.boolArg(options.hash("checked", true));
        CharSequence style = options.hash("style", "select");
        CharSequence idYes = options.hash("idYes", "");
        CharSequence idNo = options.hash("idNo", "");

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, name, mandatory, comment, "", options));
        if (style.equals("select")) {
            buf.append("<select");
            HelperUtils.appendAttr(buf, "name", name);
            buf.append('>');
            buf.append(selectOption(value, checked, null, "Да"));
            buf.append(selectOption("0", !checked, null, "Нет"));
            buf.append("</select>");
        } else if (style.equals("radio")) {
            buf.append(radio(name, value, checked, idYes, null, "Да"));
            buf.append(" &nbsp; ");
            buf.append(radio(name, "0", !checked, idNo, null, "Нет"));
        } else if (style.equals("box")) {
            buf.append(checkboxButton(name, value, checked, null, null));
        } else {
            buf.append(checkboxButton(name, value, checked, null, null));
        }
        buf.append(formLineEnd());
        return new SafeString(buf);
    }

    public CharSequence formButtons(Options options) {
        CharSequence title = options.hash("title", "Изменить");
        boolean clear = HelperUtils.boolArg(options.hash("clear", true));

        StringBuilder buf = new StringBuilder();
        buf.append("<tr><td colspan=\"2\" class=\"form-section\" style=\"text-align: center\">");
        buf.append("<input type=\"submit\"");
        HelperUtils.appendAttr(buf, "value", title);
        buf.append('>');
        if (clear) {
            buf.append("&nbsp;&nbsp;<input type=\"reset\" value=\"Очистить\">");
        }
        buf.append("</td></tr>");
        return new SafeString(buf);
    }

    public CharSequence formSelect(Options options) throws IOException {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        CharSequence style = options.hash("style", "list");
        boolean multiple = HelperUtils.boolArg(options.hash("multiple", false));
        CharSequence rows = options.hash("rows", "10");

        StringBuilder buf = new StringBuilder();
        buf.append(formSelectBegin(options, title, mandatory, comment, name, style, multiple, rows));
        buf.append(options.apply(options.fn));
        buf.append(formSelectEnd(style));
        return new SafeString(buf);
    }

    CharSequence formSelectBegin(Options options, CharSequence title, boolean mandatory, CharSequence comment,
                                 String name, CharSequence style, boolean multiple, CharSequence rows) {
        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, name, mandatory, comment, "", options));
        if (style.equals("list")) {
            buf.append("<select");
            HelperUtils.appendAttr(buf, "name", name);
            if (multiple) {
                HelperUtils.appendAttr(buf, "size", rows);
                HelperUtils.appendAttr(buf, "multiple", true);
            }
            buf.append('>');
        } else if (style.equals("box")) {
            buf.append("<table border=\"0\" cellspacing=\"0\" cellpadding=\"3\" width=\"100%\">");
        }
        options.data("FormSelectName", name);
        options.data("FormSelectStyle", style);
        options.data("FormSelectMultiple", multiple);
        return new SafeString(buf);
    }

    CharSequence formSelectEnd(CharSequence style) {
        StringBuilder buf = new StringBuilder();
        if (style.equals("list")) {
            buf.append("</select>");
        } else if (style.equals("box")) {
            buf.append("</table>");
        }
        buf.append(formLineEnd());
        return new SafeString(buf);
    }

    public CharSequence formOption(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        Object value = HelperUtils.mandatoryHash("value", options);
        Object selectedValue = options.hash("selectedValue");
        if (selectedValue == null) {
            boolean selected = HelperUtils.boolArg(options.hash("selected", false));
            return formOption(options, title, value, selected, selectedValue);
        } else {
            if (options.hash("selected") != null) {
                throw new AmbiguousArgumentsException("selected", "selectedValue");
            }
            return formOption(options, title, value, false, selectedValue);
        }

    }

    CharSequence formOption(Options options, CharSequence title, Object value, boolean selected) {
        return formOption(options, title, value, selected, null);
    }

    CharSequence formOption(Options options, CharSequence title, Object value, boolean selected, Object selectedValue) {
        StringBuilder buf = new StringBuilder();
        String style = options.get("FormSelectStyle");
        if (style.equals("list")) {
            buf.append("<option");
            HelperUtils.appendAttr(buf, "value", value);
            if (selectedValue != null) {
                HelperUtils.appendAttr(buf, "selected", selectedValue.equals(value));
            } else {
                HelperUtils.appendAttr(buf, "selected", selected);
            }
            buf.append('>');
            buf.append(HelperUtils.he(title));
        } else if (style.equals("box")) {
            CharSequence id = new SafeString((String) options.get("FormSelectName") + HelperUtils.he(value));
            String type = options.get("FormSelectMultiple") ? "checkbox" : "radio";
            buf.append("<tr>");
            buf.append("<td width=\"1\">");
            buf.append("<input");
            HelperUtils.appendAttr(buf, "id", id);
            HelperUtils.appendAttr(buf, "type", type);
            HelperUtils.appendAttr(buf, "name", options.get("FormSelectName"));
            HelperUtils.appendAttr(buf, "value", value);
            if (selectedValue != null) {
                HelperUtils.appendAttr(buf, "checked", selectedValue.equals(value));
            } else {
                HelperUtils.appendAttr(buf, "checked", selected);
            }
            buf.append('>');
            buf.append("</td>");
            buf.append("<td><label");
            HelperUtils.appendAttr(buf, "for", id);
            buf.append('>');
            HelperUtils.safeAppend(buf, title);
            buf.append("</label></td>");
            buf.append("</tr>");
        }
        return new SafeString(buf);
    }

    public CharSequence xmlText(Options options) {
        long id = HelperUtils.intArg("id", HelperUtils.mandatoryHash("id", options));
        CharSequence name = HelperUtils.mandatoryHash("name", options);

        return xmlText(id, name);
    }

    private CharSequence xmlText(long id, CharSequence name) {
        StringBuilder buf = new StringBuilder();
        buf.append(String.format("<a href=\"/xml/%d/%s/\">", id, HelperUtils.ue(name)));
        buf.append(imagesHelperSource.image("/pics/xml.gif"));
        buf.append("</a>");
        return new SafeString(buf);
    }

}