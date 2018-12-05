function datepickerInit() {
    $(".datepicker").each(function () {
        var val = $(this).val();
        $(this).datepicker($.datepicker.regional["ru"]);
        $(this).datepicker("option", "dateFormat", "dd-mm-yy");
        $(this).datepicker("setDate", val);
    });
}

$(datepickerInit);
