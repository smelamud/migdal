function sortableInit() {
    $("#sortable").sortable({
        placeholder: "list-group-item-info"
    });
    $("#sortable").disableSelection();
}

$(function() {
    sortableInit();
});
