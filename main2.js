var myVar = setInterval(LoadData, 2000);

function LoadData(){
    $.ajax({
        url: 'view.php',
        type: "POST",
        dataType: 'json',
        success: function(data) {
            $('#MyTable tbody').empty();  // Clear the existing table rows
            for (var i = 0; i < data.length; i++) {
                var commentId = data[i].id;
                if (data[i].parent_comment == 0) {  // Top-level comments
                    var row = $('<tr><td><b><img src="avatar.jpg" width="30px" height="30px" />' + data[i].student + ' :<i> ' + data[i].date + ':</i></b></br><p style="padding-left:80px">' + data[i].post + '</br><a data-toggle="modal" data-id="'+ commentId +'" title="Add this item" class="open-ReplyModal" href="#ReplyModal">Reply</a>'+'</p></td></tr>');
                    $('#record').append(row);
                    // Loop through replies to the top-level comment
                    for (var r = 0; r < data.length; r++) {
                        if (data[r].parent_comment == commentId) {  // Replies to the comment
                            var comments = $('<tr><td style="padding-left:80px"><b><img src="avatar.jpg" width="30px" height="30px" />' + data[r].student + ' :<i> ' + data[r].date + ':</i></b></br><p style="padding-left:40px">'+ data[r].post +'</p></td></tr>');
                            $('#record').append(comments);
                        }
                    }
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Error: ' + textStatus + ' - ' + errorThrown);
        }
    });
}

// Handle the modal opening when the "Reply" button is clicked
$(document).on("click", ".open-ReplyModal", function () {
    var commentid = $(this).data('id');
    $(".modal-body #commentid").val(commentid);
});

// Post data to the server when the save button is clicked for a new discussion/reply
$(document).ready(function() {
    $('#butsave').on('click', function() {
        $("#butsave").attr("disabled", "disabled");
        var id = document.forms["frm"]["Pcommentid"].value;
        var name = document.forms["frm"]["name"].value;
        var msg = document.forms["frm"]["msg"].value;
        if (name != "" && msg != "") {
            $.ajax({
                url: "save.php",
                type: "POST",
                data: {
                    id: id,
                    name: name,
                    msg: msg,
                },
                cache: false,
                success: function(dataResult) {
                    var dataResult = JSON.parse(dataResult);
                    if (dataResult.statusCode == 200) {
                        $("#butsave").removeAttr("disabled");
                        document.forms["frm"]["Pcommentid"].value = "";
                        document.forms["frm"]["name"].value = "";
                        document.forms["frm"]["msg"].value = "";
                        LoadData();  // Reload the data after saving
                    } else if (dataResult.statusCode == 201) {
                        alert("Error occurred!");
                    }
                }
            });
        } else {
            alert('Please fill all the fields!');
        }
    });
});

// Handle the modal opening when the "Reply" button is clicked for a reply
$(document).ready(function() {
    $('#btnreply').on('click', function() {
        $("#btnreply").attr("disabled", "disabled");
        var id = document.forms["frm1"]["Rcommentid"].value;
        var name = document.forms["frm1"]["Rname"].value;
        var msg = document.forms["frm1"]["Rmsg"].value;
        if (name != "" && msg != "") {
            $.ajax({
                url: "save.php",
                type: "POST",
                data: {
                    id: id,
                    name: name,
                    msg: msg,
                },
                cache: false,
                success: function(dataResult) {
                    var dataResult = JSON.parse(dataResult);
                    if (dataResult.statusCode == 200) {
                        $("#btnreply").removeAttr("disabled");
                        document.forms["frm1"]["Rcommentid"].value = "";
                        document.forms["frm1"]["Rname"].value = "";
                        document.forms["frm1"]["Rmsg"].value = "";
                        LoadData();  // Reload the data after saving the reply
                        $("#ReplyModal").modal("hide");  // Close the modal
                    } else if (dataResult.statusCode == 201) {
                        alert("Error occurred!");
                    }
                }
            });
        } else {
            alert('Please fill all the fields!');
        }
    });
});
