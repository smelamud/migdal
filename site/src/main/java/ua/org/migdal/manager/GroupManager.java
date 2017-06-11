package ua.org.migdal.manager;

import java.util.ArrayList;
import java.util.List;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import ua.org.migdal.data.GroupUsersProjection;
import ua.org.migdal.data.IdLoginProjection;
import ua.org.migdal.data.UserRepository;

@Service
public class GroupManager {

    @Autowired
    private UserRepository userRepository;

    public List<GroupUsersProjection> getAll() {
        List<Object[]> data = userRepository.findGroupsAndUsersOrderByLogin();
        List<GroupUsersProjection> groups = new ArrayList<>();
        GroupUsersProjection group = null;
        for (Object[] row : data) {
            if (group == null || group.getGroupId() != (Long) row[0]) {
                if (group != null) {
                    groups.add(group);
                }
                group = new GroupUsersProjection((Long) row[0], (String) row[1]);
            }
            group.getUsers().add(new IdLoginProjection((Long) row[2], (String) row[3]));
        }
        if (group != null) {
            groups.add(group);
        }
        return groups;
    }

}
