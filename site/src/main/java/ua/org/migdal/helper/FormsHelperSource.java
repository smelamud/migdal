package ua.org.migdal.helper;

import java.io.IOException;

import org.springframework.beans.factory.annotation.Autowired;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;

@HelperSource
public class FormsHelperSource {

    @Autowired
    private ImagesHelperSource imagesHelperSource;

    public CharSequence hidden(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"hidden\"");
        HelperUtils.appendMandatoryArgAttr(buf, "name", options);
        HelperUtils.appendArgAttr(buf, "value", "", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    public CharSequence checkboxButton(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"checkbox\"");
        HelperUtils.appendMandatoryArgAttr(buf, "name", options);
        HelperUtils.appendArgAttr(buf, "value", "1", options);
        HelperUtils.appendArgAttr(buf, "checked", false, options);
        HelperUtils.appendOptionalArgAttr(buf,"id", options);
        HelperUtils.appendOptionalArgAttr(buf, "class", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    private CharSequence checkboxButton(String name, String value, boolean checked, String id, String cls) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"checkbox\"");
        HelperUtils.appendAttr(buf, "name", name);
        HelperUtils.appendAttr(buf, "value", value);
        HelperUtils.appendAttr(buf, "checked", checked);
        HelperUtils.appendAttr(buf,"id", id);
        HelperUtils.appendAttr(buf, "class", cls);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    public CharSequence checkbox(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(checkboxButton(options));
        buf.append(' ');
        buf.append(options.hash("title", "").toString());
        buf.append("</label>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence radioButton(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"radio\"");
        HelperUtils.appendMandatoryArgAttr(buf, "name", options);
        HelperUtils.appendMandatoryArgAttr(buf, "value", options);
        HelperUtils.appendArgAttr(buf, "checked", false, options);
        HelperUtils.appendOptionalArgAttr(buf,"id", options);
        HelperUtils.appendOptionalArgAttr(buf, "class", options);
        HelperUtils.appendOptionalArgAttr(buf, "onclick", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    private CharSequence radioButton(String name, String value, boolean checked, String id, String cls) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"radio\"");
        HelperUtils.appendAttr(buf, "name", name);
        HelperUtils.appendAttr(buf, "value", value);
        HelperUtils.appendAttr(buf, "checked", checked);
        HelperUtils.appendAttr(buf,"id", id);
        HelperUtils.appendAttr(buf, "class", cls);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    public CharSequence radio(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(radioButton(options));
        buf.append(' ');
        buf.append(options.hash("title", "").toString());
        buf.append("</label>");
        return new Handlebars.SafeString(buf);
    }

    private CharSequence radio(String name, String value, boolean checked, String id, String cls, String title) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(radioButton(name, value, checked, id, cls));
        buf.append(' ');
        buf.append(title);
        buf.append("</label>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence selectOption(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<option");
        HelperUtils.appendMandatoryArgAttr(buf, "value", options);
        HelperUtils.appendArgAttr(buf, "selected", false, options);
        buf.append(options.hash("title", "").toString());
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    private CharSequence selectOption(String value, boolean selected, String title) {
        StringBuilder buf = new StringBuilder();
        buf.append("<option");
        HelperUtils.appendAttr(buf, "value", value);
        HelperUtils.appendAttr(buf, "selected", selected);
        buf.append(title);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formTable(Options options) throws IOException {
        String title = options.hash("title", "");
        boolean mini = options.hash("mini", false);
        boolean miniTitle = options.hash("miniTitle", false);
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
        if (!title.isEmpty()) {
            buf.append("<tr><td class=\"form-title\">");
            buf.append(title);
            buf.append("</td></tr>");
        }
        buf.append("<tr><td><table class=\"form-layer\" width=\"100%\">");
        buf.append(options.apply(options.fn));
        buf.append("</table></td></tr></table></center>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence star(Options options) {
        return star(options.hash("mandatory", false));
    }

    private CharSequence star(boolean mandatory) {
        if (mandatory) {
            return new Handlebars.SafeString("<span class=\"form-star\">* </span>");
        } else {
            return new Handlebars.SafeString("<span class=\"form-star\" style=\"visibility: hidden\">* </span>");
        }
    }

    public CharSequence starInfo(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<tr valign=\"top\"><td class=\"form-cell eightpt\" colspan=\"2\">");
        buf.append("Поля, отмеченные ");
        buf.append(star(true));
        buf.append(", заполнять обязательно");
        buf.append("</td></tr>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formLine(Options options) throws IOException {
        String title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = options.hash("mandatory", false);
        String comment = options.hash("comment", "");
        String id = options.hash("id", "");

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, mandatory, comment, id, options));
        buf.append(options.apply(options.fn));
        buf.append(formLineEnd());
        return new Handlebars.SafeString(buf);
    }

    private CharSequence formLineBegin(String title, boolean mandatory, String comment, String id, Options options) {
        StringBuilder buf = new StringBuilder();
        if (id != null && !id.isEmpty()) {
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
        buf.append(title);
        if (!comment.isEmpty()) {
            buf.append("<p class=\"form-comment\">");
            buf.append(comment);
            buf.append("</p>");
        }
        buf.append("</th>");
        buf.append("<td class=\"form-cell ninept\"");
        HelperUtils.appendAttr(buf, "width", options.get("formValueWidth"));
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    private CharSequence formLineEnd() {
        return new Handlebars.SafeString("</td></tr>");
    }

    public CharSequence formComment(Options options) throws IOException {
        StringBuilder buf = new StringBuilder();
        buf.append("<tr><td class=\"form-cell\" colspan=\"2\">");
        buf.append(options.apply(options.fn));
        buf.append("</td></tr>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formSection(Options options) {
        String title = HelperUtils.mandatoryHash("title", options);

        StringBuilder buf = new StringBuilder();
        buf.append("<tr valign=\"top\">");
        buf.append("<th class=\"form-section\" colspan=\"2\">&nbsp;&nbsp;");
        buf.append(title);
        buf.append("</th>");
        buf.append("</tr>");
        return new Handlebars.SafeString(buf);
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
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    private CharSequence edit(String name, String value, String size, String maxlength, String onkeypress, String id) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"text\"");
        HelperUtils.appendAttr(buf, "name", name);
        HelperUtils.appendAttr(buf, "value", value);
        HelperUtils.appendAttr(buf, "size", size);
        HelperUtils.appendAttr(buf, "maxlength", maxlength);
        HelperUtils.appendAttr(buf, "onkeypress", onkeypress);
        HelperUtils.appendAttr(buf, "id", id);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formEdit(Options options) {
        String title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = options.hash("mandatory", false);
        String comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        String value = HelperUtils.mandatoryHash("value", options);
        String size = options.hash("size", "40");
        String maxlength = options.hash("maxlength", "250");
        long xmlid = HelperUtils.intArg("xmlid", options.hash("xmlid"));

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, mandatory, comment, "", options));
        buf.append(edit(name, value, size, maxlength, null, null));
        if (xmlid != 0) {
            buf.append("<br>");
            buf.append(xmlText(xmlid, name));
        }
        buf.append(formLineEnd());
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formPassword(Options options) {
        String title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = options.hash("mandatory", false);
        String comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        String size = options.hash("size", "40");
        String maxlength = options.hash("maxlength", "250");

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, mandatory, comment, "", options));
        buf.append(String.format("<input type='password' name=\"%s\" size=\"%s\" maxlength=\"%s\">",
                                 name, size, maxlength));
        buf.append(formLineEnd());
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formEditor(Options options) {
        String title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = options.hash("mandatory", false);
        String comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        String body = HelperUtils.mandatoryHash("body", options);
        String rows = options.hash("rows", "10");
        long xmlid = HelperUtils.intArg("xmlid", options.hash("xmlid"));

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, mandatory, comment, "", options));
        buf.append(String.format("<textarea name=\"%s\" rows=\"%s\" wrap=\"virtual\" style=\"width: 100%\">%s</textarea>",
                                 name, rows, body));
        if (xmlid != 0) {
            buf.append("<br>");
            buf.append(xmlText(xmlid, name));
        }
        buf.append(formLineEnd());
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formCheckbox(Options options) {
        String title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = options.hash("mandatory", false);
        String comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        String value = options.hash("value", "1");
        boolean checked = options.hash("checked", true);
        String style = options.hash("style", "select");
        String idYes = options.hash("idYes", "");
        String idNo = options.hash("idNo", "");

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, mandatory, comment, "", options));
        if (style.equals("select")) {
            buf.append("<select");
            HelperUtils.appendAttr(buf, "name", name);
            buf.append('>');
            buf.append(selectOption(value, checked, "Да"));
            buf.append(selectOption("0", !checked, "Нет"));
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
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formButtons(Options options) {
        String title = options.hash("title", "Изменить");
        boolean clear = options.hash("clear", true);

        StringBuilder buf = new StringBuilder();
        buf.append("<tr><td colspan=\"2\" class=\"form-section\" style=\"text-align: center\">");
        buf.append("<input type=\"submit\"");
        HelperUtils.appendAttr(buf, "value", title);
        buf.append('>');
        if (clear) {
            buf.append("&nbsp;&nbsp;<input type=\"reset\" value=\"Очистить\">");
        }
        buf.append("</td></tr>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formSelect(Options options) throws IOException {
        String title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = options.hash("mandatory", false);
        String comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        String style = options.hash("style", "list");
        boolean multiple = options.hash("multiple", false);
        String rows = options.hash("rows", "10");

        StringBuilder buf = new StringBuilder();
        buf.append(formLineBegin(title, mandatory, comment, "", options));
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
        buf.append(options.apply(options.fn));
        if (style.equals("list")) {
            buf.append("</select>");
        } else if (style.equals("box")) {
            buf.append("</table>");
        }
        buf.append(formLineEnd());
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formOption(Options options) {
        String title = HelperUtils.mandatoryHash("title", options);
        String value = HelperUtils.mandatoryHash("value", options);
        boolean selected = options.hash("selected", false);

        StringBuilder buf = new StringBuilder();
        String style = options.get("FormSelectStyle");
        if (style.equals("list")) {
            buf.append("<option");
            HelperUtils.appendAttr(buf, "value", value);
            HelperUtils.appendAttr(buf, "selected", selected);
            buf.append('>');
            buf.append(title);
        } else if (style.equals("box")) {
            String id = options.get("FormSelectName") + value;
            String type = options.get("FormSelectMultiple") ? "checkbox" : "radio";
            buf.append("<tr>");
            buf.append("<td width=\"1\">");
            buf.append("<input");
            HelperUtils.appendAttr(buf, "id", id);
            HelperUtils.appendAttr(buf, "type", type);
            HelperUtils.appendAttr(buf, "name", options.get("FormSelectName"));
            HelperUtils.appendAttr(buf, "value", value);
            HelperUtils.appendAttr(buf, "checked", selected);
            buf.append('>');
            buf.append("</td>");
            buf.append("<td><label");
            HelperUtils.appendAttr(buf, "for", id);
            buf.append('>');
            buf.append(title);
            buf.append("</label></td>");
            buf.append("</tr>");
        }
        return new Handlebars.SafeString(buf);
    }

    public CharSequence xmlText(Options options) {
        long id = HelperUtils.intArg("id", HelperUtils.mandatoryHash("id", options));
        String name = HelperUtils.mandatoryHash("name", options);

        return xmlText(id, name);
    }

    private CharSequence xmlText(long id, String name) {
        StringBuilder buf = new StringBuilder();
        buf.append(String.format("<a href=\"/xml/%d/%s/\">", id, name));
        buf.append(imagesHelperSource.image("/pics/xml.gif"));
        buf.append("</a>");
        return new Handlebars.SafeString(buf);
    }

}