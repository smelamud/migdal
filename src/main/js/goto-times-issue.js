function gotoTimesIssueInit() {
    $("#select-issue").change(function() {
        window.location = "/times/" + $(this).val();
    });
}

$(gotoTimesIssueInit);
