package ua.org.migdal.data.util;

public class CommentsPage {

    private int page;
    private int offset;
    private boolean skip;

    public CommentsPage(int page, int offset) {
        this.page = page;
        this.offset = offset;
    }

    public CommentsPage(boolean skip) {
        this.skip = skip;
    }

    public int getPage() {
        return page;
    }

    public void setPage(int page) {
        this.page = page;
    }

    public int getOffset() {
        return offset;
    }

    public void setOffset(int offset) {
        this.offset = offset;
    }

    public boolean isSkip() {
        return skip;
    }

    public void setSkip(boolean skip) {
        this.skip = skip;
    }

}
