<?php require_once('Connections/members.php'); ?>
<?php
include("PHPMailerAutoload.php");

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

// *** Redirect if username exists
$MM_flag="MM_insert";
if (isset($_POST[$MM_flag])) {
  $MM_dupKeyRedirect="index.php";
  $loginUsername = $_POST['Username'];
  $LoginRS__query = sprintf("SELECT Username FROM members WHERE Username=%s", GetSQLValueString($loginUsername, "text"));
  mysql_select_db($database_members, $members);
  $LoginRS=mysql_query($LoginRS__query, $members) or die(mysql_error());
  $loginFoundUser = mysql_num_rows($LoginRS);

  //if there is a row in the database, the username was found - can not add the requested username
  if($loginFoundUser){
    $MM_qsChar = "?";
    //append the username to the redirect page
    if (substr_count($MM_dupKeyRedirect,"?") >=1) $MM_qsChar = "&";
    $MM_dupKeyRedirect = $MM_dupKeyRedirect . $MM_qsChar ."requsername=".$loginUsername;
    header ("Location: $MM_dupKeyRedirect");
    exit;
  }
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO members (Username, Password, Email, Authcode) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['Username'], "text"),
                       GetSQLValueString(md5($_POST['Password']), "text"),
                       GetSQLValueString($_POST['Email'], "text"),
                       GetSQLValueString($_POST['Authcode'], "text"));

  mysql_select_db($database_members, $members);
  $Result1 = mysql_query($insertSQL, $members) or die(mysql_error());

//寄出認證信
$Url="http://localhost/members/auth.php?Username=" . $_POST['Username']
       . "&Authcode=" . $_POST['Authcode'];

$mail= new PHPMailer(); //建立新物件
$mail->IsSMTP(); //設定使用SMTP方式寄信
$mail->SMTPAuth = true; //設定SMTP需要驗證
$mail->SMTPSecure = "ssl"; // Gmail的SMTP主機需要使用SSL連線
$mail->Host = "smtp.gmail.com"; //Gamil的SMTP主機
$mail->Port = 465;  //Gamil的SMTP主機的埠號(Gmail為465)。
$mail->CharSet = "utf-8"; //郵件編碼

$mail->Username = "XXX@gmail.com"; //Gamil帳號
$mail->Password = "password"; //Gmail密碼
$mail->From = "XXX@gmail.com"; //寄件者信箱
$mail->FromName = "XXX購物網客服"; //寄件者姓名
$mail->Subject = " XXX購物網歡迎您";  //郵件標題
$mail->Body =$_POST['Username'] . "你好!<br>"
        	."歡迎你在XXX購物網註冊<br>"
		."若你沒有註冊請忽略這封認證信件<br>"
		."<a href=" . $Url . ">"
		."點一下這裡認證你的帳號 </a>";

$mail->IsHTML(true); //郵件內容為html ( true || false)
$mail->AddAddress($_POST['Email']);
if(!$mail->Send()) {
	echo "發送錯誤: " . $mail->ErrorInfo;
}







  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>無標題文件</title>
</head>

<body>
<form name="form1" method="POST" action="<?php echo $editFormAction; ?>">
  <table width="50%" border="1" align="center">
    <tr>
      <td colspan="2" align="center" valign="middle">使用者註冊</td>
    </tr>
    <tr>
      <td valign="middle">使用者名稱:</td>
      <td valign="middle"><label for="Username"></label>
      <input type="text" name="Username" id="Username"></td>
    </tr>
    <tr>
      <td valign="middle">密碼:</td>
      <td valign="middle"><label for="Password"></label>
      <input type="password" name="Password" id="Password"></td>
    </tr>
    <tr>
      <td valign="middle">信箱:</td>
      <td valign="middle"><label for="Email"></label>
      <input type="text" name="Email" id="Email"></td>
    </tr>
    <tr>
      <td colspan="2" align="right" valign="middle"><input name="Authcode" type="hidden" id="Authcode" value="<?php echo $authcode=substr(md5(uniqid(rand())),0,8); ?>">
      <a href="index.php">登入</a>        <input type="submit" name="button" id="button" value="註冊"></td>
    </tr>
  </table>
  <input type="hidden" name="MM_insert" value="form1">
</form>
</body>
</html>