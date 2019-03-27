<?php 
	session_start();
	$db = mysqli_connect('localhost', 'amavisd', '', 'amavisd');
        // Check connection
        if (!$db)
           {
           die("Connection error: " . mysqli_connect_error());
           }

/* добавить пользователя */
if (isset($_POST['save'])) {
	$domain = $_POST['domain'];
	$list = $_POST['list'];
	if($list=="W") $l = 'Whitelist';
	if($list=="B") $l = 'Blacklist';
	/* проверка, есть ли такой домен или email */
	$results = mysqli_query($db, "SELECT id FROM `mailaddr` WHERE email='$domain'");
	$row = mysqli_fetch_array($results);
	if (!empty($row['id'])) {
		$_SESSION['message_header'] = "Ошибка при добавлении";
		$_SESSION['message'] = "Домен / Email <strong>$domain</strong> уже есть в базе.";
		$_SESSION['message_footer'] = "";
	}
	else
	{
		mysqli_query($db, "INSERT INTO `mailaddr` (priority,email) VALUES ('5','$domain')");
		$results = mysqli_query($db, "SELECT id FROM `mailaddr` WHERE email='$domain'");
		$row = mysqli_fetch_array($results);
		mysqli_query($db, "INSERT INTO `wblist` (rid,sid,wb) VALUES ('1','$row[id]','$list')");
		$_SESSION['message_header'] = "Успешно";
		$_SESSION['message'] = "Домен/Email: <strong>$domain</strong><br>
		Лист: <strong>$l</strong><br>";
		$_SESSION['message_footer'] = "";
	}
		header('location: index.php');
}

/* вывод формы редактирования пользователя */
if (isset($_GET['edituser'])) {
	$id = $_GET['edituser'];
	$results = mysqli_query($db, "SELECT * FROM `mailaddr` LEFT JOIN `wblist` ON `mailaddr`.`id` = `wblist`.`sid` WHERE id='$id'");
	$row = mysqli_fetch_array($results);
	$domain = $row['email'];
	$list = $row['wb'];
	if($list=="W") $w = 'checked';
	if($list=="B") $b = 'checked';
	$_SESSION['message_header'] = "Редактировать домен <strong>$domain</strong>";
	$_SESSION['message'] = "<form method=\"post\" action=\"server.php\">
	<input type=\"hidden\" name=\"id\" value=\"$id\">
	<div class=\"form-group\">
	<label>Домен/Email:</label>
	<input class=\"form-control\" type=\"text\" name=\"domain\" value=\"$domain\" maxlength=\"99\" autofocus>
	</div>
	<div class=\"form-group\">
	<label>Лист:</label><br>
	<label class=\"hand radio-inline\">
	<input type=\"radio\" name=\"list\" value=\"W\" $w> Whitelist
	</label>
	&nbsp;&nbsp;
	<label class=\"hand radio-inline\">
	<input type=\"radio\" name=\"list\" value=\"B\" $b> Blacklist
	</label>
	</div>";
	$_SESSION['message_footer'] = "<button class=\"btn btn-success btn-sm\" type=\"submit\" name=\"update\"><i class=\"fas fa-sync\"></i> Обновить</button>
	</form>";
	header('location: index.php#'.$id.'');
}

/* Обновление */
if (isset($_POST['update'])) {
	$id = $_POST['id'];
	$domain = $_POST['domain'];
	$list = $_POST['list'];
	if($list=="W") $l = 'Whitelist';
	if($list=="B") $l = 'Blacklist';
	/* проверка, есть ли такой домен или email */
	$results = mysqli_query($db, "SELECT id FROM `mailaddr` WHERE email='$domain' AND id!='$id'");
	$row = mysqli_fetch_array($results);
	if (!empty($row['id'])) {
		$_SESSION['message_header'] = "Ошибка при обновлении";
		$_SESSION['message'] = "Домен / Email <strong>$domain</strong> уже есть в базе.";
		$_SESSION['message_footer'] = "";
	}
	else
	{
	mysqli_query($db, "UPDATE `mailaddr` SET email='$domain' WHERE id=$id");
	mysqli_query($db, "UPDATE `wblist` SET wb='$list' WHERE sid=$id");
	$_SESSION['message_header'] = "Успешено";
	$_SESSION['message'] = "Домен / Email изменен: <strong>$domain</strong><br>
	Лист: <strong>$l</strong><br>";
	$_SESSION['message_footer'] = "";
	}
	header('location: index.php#'.$id.'');
}

/* удаление */
if (isset($_GET['del'])) {
	$id = $_GET['del'];
	$results = mysqli_query($db, "SELECT * FROM `mailaddr` WHERE id=$id");
	$row = mysqli_fetch_array($results);
	$domain = $row['email'];
	mysqli_query($db, "DELETE FROM `mailaddr` WHERE id=$id");
	mysqli_query($db, "DELETE FROM `wblist` WHERE sid=$id");
	$_SESSION['message_header'] = "Успешно";
	$_SESSION['message'] = "Домен / Email удален: <strong>$domain</strong>";
	$_SESSION['message_footer'] = "";
	header('location: index.php');
}
?>
