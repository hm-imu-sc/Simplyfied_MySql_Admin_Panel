$("form .input_group .status").click(function(){
    $(this).toggleClass("active");
    let _for = $(this).attr("for");
    if ($(this).hasClass("active")) {
        console.log("has class");
        $("#"+_for).attr("value", "1");
    }
    else {
        $("#"+_for).attr("value", "0");
    }
});