function imageUploaderInit() {
    $(".image-uploader-delete").click(function () {
        var top = $(this).parentsUntil(".image-uploader").parent();
        top.find(".image-uploader-loaded").hide();
        top.find(".image-uploader-file").show();
        top.find(".image-uploader-uuid").val("");
    });
}

$(imageUploaderInit);
