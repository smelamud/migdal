package ua.org.migdal.grp;

import java.util.Collections;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;

import org.springframework.expression.EvaluationContext;
import org.springframework.expression.Expression;
import org.springframework.expression.ExpressionParser;
import org.springframework.expression.ParserContext;

import org.springframework.util.StringUtils;
import ua.org.migdal.manager.IdentManager;

public class GrpDescriptor {

    private String name;
    private short bit;
    private List<String> groups;

    private String rootIdent;

    /**
     * Grp used to publish this postings as collection (for example,
     * publishing albums of pictures
     */
    private String publishGrp;

    private String heading = "${subject}";
    private Expression headingExpression;

    /**
     * Page to show the posting's topic ("general view")
     */
    private List<SubtreeHref> generalHref = Collections.emptyList();

    /**
     * Page to show the posting's details ("best view")
     */
    private List<SubtreeHref> detailsHref = Collections.emptyList();

    /**
     * Template to show the posting's details
     */
    private String detailsTemplate = "posting";

    /**
     * Helper to show the posting in a feed
     */
    private String feedHelper = "postingGeneral";

    /**
     * Допустимые сообщения: {}
     */
    private String title = "Сообщения";

    /**
     * Добавить {}
     * Изменить {}
     */
    private String what = "сообщение";

    /**
     * Добавление {}
     * Редактирование {}
     */
    private String whatA = "сообщения";

    private List<GrpEditor> editors = Collections.emptyList();
    private Map<String, GrpEditor> fieldEditors;
    private List<GrpEditor> hiddenEditors;

    private Expression trackExpression;

    public GrpDescriptor() {
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public short getBit() {
        return bit;
    }

    public void setBit(short bit) {
        this.bit = bit;
    }

    public long getValue() {
        return 1L << getBit();
    }

    public List<String> getGroups() {
        return groups;
    }

    public void setGroups(List<String> groups) {
        this.groups = groups;
    }

    public String getRootIdent() {
        return rootIdent;
    }

    public void setRootIdent(String rootIdent) {
        this.rootIdent = rootIdent;
    }

    public String getPublishGrp() {
        return publishGrp;
    }

    public void setPublishGrp(String publishGrp) {
        this.publishGrp = publishGrp;
    }

    public String getHeading() {
        return heading;
    }

    public void setHeading(String heading) {
        this.heading = heading;
    }

    public String getHeading(EvaluationContext evaluationContext) {
        return headingExpression.getValue(evaluationContext, String.class);
    }

    public List<SubtreeHref> getGeneralHref() {
        return generalHref;
    }

    public void setGeneralHref(List<SubtreeHref> generalHref) {
        this.generalHref = generalHref;
    }

    public String getGeneralHref(EvaluationContext evaluationContext) {
        return evaluateSubtreeExpression(generalHref, evaluationContext);
    }

    public List<SubtreeHref> getDetailsHref() {
        return detailsHref;
    }

    public void setDetailsHref(List<SubtreeHref> detailsHref) {
        this.detailsHref = detailsHref;
    }

    public String getDetailsHref(EvaluationContext evaluationContext) {
        return evaluateSubtreeExpression(detailsHref, evaluationContext);
    }

    public String getDetailsTemplate() {
        return detailsTemplate;
    }

    public void setDetailsTemplate(String detailsTemplate) {
        this.detailsTemplate = detailsTemplate;
    }

    public String getFeedHelper() {
        return feedHelper;
    }

    public void setFeedHelper(String feedHelper) {
        this.feedHelper = feedHelper;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getWhat() {
        return what;
    }

    public void setWhat(String what) {
        this.what = what;
    }

    public String getWhatA() {
        return whatA;
    }

    public void setWhatA(String whatA) {
        this.whatA = whatA;
    }

    public List<GrpEditor> getEditors() {
        return editors;
    }

    public void setEditors(List<GrpEditor> editors) {
        this.editors = editors;
        fieldEditors = editors.stream()
                .filter(editor -> !StringUtils.isEmpty(editor.getField()))
                .collect(Collectors.toMap(GrpEditor::getField, Function.identity()));
    }

    public boolean isMandatory(String field) {
        GrpEditor editor = fieldEditors.get(field);
        return editor != null && editor.isMandatory();
    }

    public List<GrpEditor> getHiddenEditors() {
        return hiddenEditors;
    }

    private String evaluateSubtreeExpression(List<SubtreeHref> subtreeHrefs, EvaluationContext evaluationContext) {
        String track = trackExpression.getValue(evaluationContext, String.class);
        for (SubtreeHref subtreeHref : subtreeHrefs) {
            if (subtreeHref.isUnder(track)) {
                return subtreeHref.getHref(evaluationContext);
            }
        }
        return "";
    }

    void parseExpressions(IdentManager identManager, ExpressionParser parser) {
        ParserContext parserContext = new TemplateParserContext();
        headingExpression = parser.parseExpression(heading, parserContext);
        generalHref.forEach(element -> element.parseExpressions(identManager, parser, parserContext));
        detailsHref.forEach(element -> element.parseExpressions(identManager, parser, parserContext));
        trackExpression = parser.parseExpression("${track}", parserContext);
    }

    void fillHiddenEditors(GrpDescriptor grpNone) {
        Set<String> visible = new HashSet<>();
        editors.stream()
                .filter(editor -> !editor.isSection())
                .forEach(editor -> visible.add(editor.getField()));
        hiddenEditors = grpNone.editors.stream()
                            .filter(editor -> !editor.isSection() && !visible.contains(editor.getField()))
                            .collect(Collectors.toList());
    }

}