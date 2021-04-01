export function extractLocationFromTag(match, groupId, group, checkDublicatesBuffer, tags) {
    match = match !== null ? match[0].trim() : '';
    if (match) {
        if (!checkDublicatesBuffer.includes(match)) {
            checkDublicatesBuffer.push(match);
            tags.push({
                group_name: group.groupTitle.innerHTML,
                group_id: groupId,
                name: match
            });
        }
    }
}
