<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/x-icon" href="img/favicon.ico">
<title>Homework To-Do Discussion</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="main2.js"></script>
</head>
<body>

<div class="container" style="margin-top: 20px;">
    <!-- Back to Dashboard Button -->
    <a href="dashboardpage.php" class="btn btn-secondary" style="margin-bottom: 20px;">
        <span class="glyphicon glyphicon-arrow-left"></span> Back to Dashboard
    </a>

    <!-- Modal -->
    <div id="ReplyModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Reply Question</h4>
                </div>
                <div class="modal-body">
                    <form name="frm1" method="post">
                        <input type="hidden" id="commentid" name="Rcommentid">
                        <div class="form-group">
                            <label for="usr">Write your name:</label>
                            <input type="text" class="form-control" name="Rname" required>
                        </div>
                        <div class="form-group">
                            <label for="comment">Write your reply:</label>
                            <textarea class="form-control" rows="5" name="Rmsg" required></textarea>
                        </div>
                        <input type="button" id="btnreply" name="btnreply" class="btn btn-primary" value="Reply">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default" style="margin-top:50px">
        <div class="panel-body">
            <h3>Live Discussion</h3>
            <p>Please use your name! Remember!</p>
            <hr>
            <form name="frm" method="post">
                <input type="hidden" id="commentid" name="Pcommentid" value="0">
                <div class="form-group">
                    <label for="usr">Write your name:</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="form-group">
                    <label for="comment">Write your question:</label>
                    <textarea class="form-control" rows="5" name="msg" required></textarea>
                </div>
                <input type="button" id="butsave" name="save" class="btn btn-primary" value="Send">
            </form>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-body">
            <h4>Recent Questions</h4>
            <table class="table" id="MyTable" style="background-color: #edfafa; border:0px; border-radius:10px">
                <tbody id="record">
                    <!-- Comments will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
