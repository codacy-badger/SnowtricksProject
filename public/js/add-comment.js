$('#form-data').submit(function (e) {
    e.preventDefault();
    let url = $(this).attr('action');
    let formdata = new FormData(document.getElementById("form-data"));
    $.ajax({
        url: url,
        method: "POST",
        data: formdata,
        processData: false,  // indique à jQuery de ne pas traiter les données
        contentType: false,  // indique à jQuery de ne pas configurer le contentType
        success: function (data) {
            $('.add-comment').last().clone().insertBefore($('.add-comment:first')).html(data);
            $('#comment_content').val("");
        },
    });
});
