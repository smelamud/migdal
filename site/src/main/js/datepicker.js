function datepickerInit() {
    var val = $(".datepicker").val(); // FIXME allows only one DatePicker on a page
    $(".datepicker").datepicker($.datepicker.regional["ru"]);
    $(".datepicker").datepicker("option", "dateFormat", "dd-mm-yy");
    $(".datepicker").datepicker("setDate", val);
}

$(datepickerInit);
