$("input[is_image=\"1\"]").change(function(e){
    let field_name = $(this).attr("name");
    let url = URL.createObjectURL(e.target.files[0]);

    $("#img_" + field_name).attr("src", url);
});