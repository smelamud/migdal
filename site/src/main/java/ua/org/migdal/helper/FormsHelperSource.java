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
        HelperUtils.appendHashParam(buf, "name", "name", null, options);
        HelperUtils.appendHashParam(buf, "value", "value", "", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    public CharSequence checkboxButton(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"checkbox\"");
        HelperUtils.appendHashParam(buf, "name", "name", null, options);
        HelperUtils.appendHashParam(buf, "value", "value", "1", options);
        boolean checked = options.<Boolean> hash("checked", false);
        if (checked) {
            buf.append(" checked");
        }
        HelperUtils.appendHashParam(buf,"id", options);
        HelperUtils.appendHashParam(buf, "class", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    public CharSequence checkbox(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(checkboxButton(options));
        String title = options.hash("title", "");
        buf.append(' ');
        buf.append(title);
        buf.append("</label>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence radioButton(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"radio\"");
        HelperUtils.appendHashParam(buf, "name", "name", null, options);
        HelperUtils.appendHashParam(buf, "value", "value", null, options);
        boolean checked = options.<Boolean> hash("checked", false);
        if (checked) {
            buf.append(" checked");
        }
        HelperUtils.appendHashParam(buf,"id", options);
        HelperUtils.appendHashParam(buf, "class", options);
        HelperUtils.appendHashParam(buf, "onclick", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    public CharSequence radio(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(radioButton(options));
        String title = options.hash("title", "");
        buf.append(' ');
        buf.append(title);
        buf.append("</label>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence selectOption(Options options) {
        boolean selected = options.<Boolean> hash("selected", false);
        String value = HelperUtils.mandatoryHash("value", options);
        String title = options.hash("title", "");
        if (selected) {
            return new Handlebars.SafeString(String.format("<option value=\"%s\" selected>%s", value, title));
        } else {
            return new Handlebars.SafeString(String.format("<option value=\"%s\">%s", value, title));
        }
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
        buf.append("<center><table class=\"form\" width=\"");
        buf.append(width);
        buf.append("\" cellspacing=\"0\" cellpadding=\"1\">");
        if (!title.isEmpty()) {
            buf.append("<tr><td class=\"form-title\">");
            buf.append(title);
            buf.append("</td></tr>");
        }
        buf.append("<tr><td><table class=\"form-layer\" width=\"100%\" cellspacing=\"1\" cellpadding=\"5\">");
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
        if (!id.isEmpty()) {
            buf.append("<tr id=\"");
            buf.append(id);
            buf.append("\" valign=\"top\">");
        } else {
            buf.append("<tr valign=\"top\">");
        }
        buf.append("<th class=\"form-cell\" width=\"");
        buf.append(options.get("formTitleWidth").toString());
        buf.append("\">");
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
        buf.append("<td class=\"form-cell ninept\" width=\"");
        buf.append(options.get("formValueWidth").toString());
        buf.append("\">");
        buf.append(options.apply(options.fn));
        buf.append("</td>");
        buf.append("</tr>");
        return new Handlebars.SafeString(buf);
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
        HelperUtils.appendHashParam(buf, "name", "name", null, options);
        HelperUtils.appendHashParam(buf, "value", "value", null, options);
        HelperUtils.appendHashParam(buf, "size", "size", "40", options);
        HelperUtils.appendHashParam(buf, "maxlength", "maxlength", "250", options);
        HelperUtils.appendHashParam(buf, "onkeypress", options);
        HelperUtils.appendHashParam(buf, "id", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }
/*
<element name='form_edit'>
<form_line title='$title' mandatory='$mandatory' comment='$comment'>
 <edit name='$name' value='$value' size='$size' maxlength='$maxlength'>
 <ifnzero what='$xmlid'>
  <br><xml_text id='$xmlid' name='$name'>
 </ifnzero>
</form_line>
</element>

<element name='form_password'>
<form_line title='$title' mandatory='$mandatory' comment='$comment'>
 <input type='password' name='$name' size='$size' maxlength='$maxlength'>
</form_line>
</element>

<element name='form_editor'>
<form_line title='$title' mandatory='$mandatory' comment='$comment'>
 <textarea name='$name' rows='$rows' wrap='virtual'
    style='width: 100%'>$body</textarea>
 <ifnzero what='$xmlid'>
  <br><xml_text id='$xmlid' name='$name'>
 </ifnzero>
</form_line>
</element>

<element name='form_checkbox'>
<form_line title='$title' mandatory='$mandatory' comment='$comment'>
 <if what="$style=='select'">
  <select name='$name'>
   <select_option value='$value' selected='$checked' title='Да'>
   <select_option value='0' selected!='!$checked' title='Нет'>
  </select>
 <elif what="$style=='radio'">
  <radio name='$name' value='$value' checked='$checked' title='Да' id='$id_yes'>
            &nbsp;
  <radio name='$name' value='0' checked!='!$checked' title='Нет' id='$id_no'>
 <elif what="$style=='box'">
  <checkbox_button name='$name' value='$value' checked='$checked'>
 <else>
  <checkbox_button name='$name' value='$value' checked='$checked'>
 </if>
</form_line>
</element>

<element name='form_buttons'>
<tr><td colspan='2' class='form-section' style='text-align: center'>
 <input type=submit value='$title'>
 <if what='$clear'>
            &nbsp;&nbsp;<input type=reset value='Очистить'>
 </if>
</td></tr>
</element>

<element name='form_select'>
<form_line title='$title' mandatory='$mandatory' comment='$comment'>
 <if what="$style=='list'">
  <if what='$multiple'>
   <select name='$name' size='$rows' multiple>
  <else>
   <select name='$name'>
  </if>
 <elif what="$style=='box'">
  <table border='0' cellspacing='0' cellpadding='3' width='100%'>
 </if>
 <assign_global name='FormSelectName' value='$name'>
 <assign_global name='FormSelectStyle' value='$style'>
 <assign_global name='FormSelectMultiple' value='$multiple'>
</element>

<element name='/form_select'>
 <if what="$$FormSelectStyle=='list'">
  </select>
 <elif what="$$FormSelectStyle=='box'">
  </table>
 </if>
</form_line>
</element>

<element name='form_option'>
<if what="$$FormSelectStyle=='list'">
 <if what='$selected'>
  <option value='$value' selected>$title
            <else>
  <option value='$value'>$title
            </if>
<elif what="$$FormSelectStyle=='box'">
 <assign name='type' value!='$$FormSelectMultiple ? "checkbox" : "radio"'>
 <tr>
  <td width='1'>
   <if what='$selected'>
    <input id='$$FormSelectName$value' type='$type' name='$$FormSelectName'
    value='$value' checked>
   <else>
    <input id='$$FormSelectName$value' type='$type' name='$$FormSelectName'
    value='$value'>
   </if>
  </td>
  <td><label for='$$FormSelectName$value'>$title</label></td>
 </tr>
</if>
</element>

<element name='form_date'>
<form_line title='$title' mandatory='$mandatory' comment='$comment'>
 <input_datetime name='$name' value='$value' base_year='$base_year'>
</form_line>
</element>
*/

    public CharSequence xmlText(Options options) {
        String id = HelperUtils.mandatoryHash("id", options);
        String name = HelperUtils.mandatoryHash("name", options);

        StringBuilder buf = new StringBuilder();
        buf.append(String.format("<a href=\"/xml/%s/%s/\">", id, name));
        buf.append(imagesHelperSource.image("/pics/xml.gif"));
        buf.append("</a>");
        return new Handlebars.SafeString(buf);
    }

}