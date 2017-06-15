package ua.org.migdal.util;

import ua.org.migdal.session.RequestContext;

public class PermUtils {

    private static final String TEMPLATE = "rwaprwaprwaprwap";

    public static class AndOrMask {

        private long andMask;
        private long orMask;

        public AndOrMask(long andMask, long orMask) {
            this.andMask = andMask;
            this.orMask = orMask;
        }

        public long getAndMask() {
            return andMask;
        }

        public long getOrMask() {
            return orMask;
        }

    }

    /**
     * Check whether the permissions allow the given action 
     */
    public static boolean perm(long userId, long groupId, long perms, long right, RequestContext rc) {
        return rc.getUserId() == userId
                       && (perms & right << Perm.USER) != 0
               ||
               (rc.getUserId() == groupId || rc.getUserGroups().contains(groupId))
                       && (perms & right << Perm.GROUP) != 0
               ||
               rc.getUserId() > 0
                       && (perms & right << Perm.OTHER) != 0
               ||
                          (perms & right << Perm.GUEST) != 0;
    }

    /**
     * Convert the permission string to a mask. Question marks are replaced by
     * corresponding characters from {@param def}.
     */
    public static long parse(String s, String def) {
        if (s.length() != TEMPLATE.length()) {
            return -1;
        }

        s = s.toLowerCase();
        long perm = 0;
        long right = 1;
        for (int i = 0; i < TEMPLATE.length(); i++, right *= 2) {
            char c = s.charAt(i) == '?' ? def.charAt(i) : s.charAt(i);
            if (c == TEMPLATE.charAt(i)) {
                perm |= right;
            } else {
                if (c != '-') {
                    return -1;
                }
            }
        }
        return perm;
    }

    public static long parse(String s) {
        return parse(s, "----------------");
    }

    /**
     * Parse permission mask string to AND and OR masks
     */
    public static AndOrMask parseMask(String s) {
        if (s.length() != TEMPLATE.length()) {
            return null;
        }

        s = s.toLowerCase();
        long andMask = Perm.ALL;
        long orMask = Perm.NONE;
        long right = 1;
        for (int i = 0; i < TEMPLATE.length(); i++, right *= 2) {
            if (s.charAt(i) == TEMPLATE.charAt(i)) {
                orMask |= right;
            } else if (s.charAt(i) =='-') {
                andMask &= ~right;
            } else if (s.charAt(i) != '?') {
                return null;
            }
        }
        return new AndOrMask(andMask, orMask);
    }

    /**
     * Convert a mask to string
     */
    public static String toString(long perms) {
        StringBuilder buf = new StringBuilder();
        long right = 1;
        for (int i = 0; i < TEMPLATE.length(); i++, right *= 2) {
            if ((perms & right) != 0) {
                buf.append(TEMPLATE.charAt(i));
            } else {
                buf.append('-');
            }
        }
        return buf.toString();
    }

}