<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

$require_help = TRUE;
$require_login = true;
$helpTopic = 'Profile';
include '../../include/baseTheme.php';
include "../auth/auth.inc.php";
$require_valid_uid = TRUE;
$tool_content = "";

check_uid();
$nameTools = $langModifProfile;
check_guest();
$allow_username_change = !get_config('block-username-change');
$pdodb = new PDO("mysql:host=$mysqlServer;dbname=$mysqlMainDb",$mysqlUser, $mysqlPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
if (isset($submit) && (!isset($ldap_submit)) && !isset($changePass)) {
        if (!$allow_username_change) {
                $username_form = $uname;
        }
	// check if username exists
	//$username_check=mysql_query("SELECT username FROM user WHERE username='".escapeSimple($username_form)."'");
	$sql= $pdodb->prepare("SELECT username FROM user WHERE username= ?");
			$username_form = escapeSimple($username_form);
			$sql->bindParam(1, $username_form);
			$sql->execute();
	while ($myusername = $sql->fetch(PDO::FETCH_ASSOC))
	{
		$user_exist=$myusername[0];
	}


	$username_form = htmlspecialchars($username_form, ENT_QUOTES);
	$prenom_form = htmlspecialchars($prenom_form, ENT_QUOTES);
	$nom_form = htmlspecialchars($nom_form, ENT_QUOTES);
	$am_form = htmlspecialchars($am_form, ENT_QUOTES);
	// check if there are empty fields
	if (empty($nom_form) OR empty($prenom_form) OR empty($username_form)) {
		header("location:". $_SERVER['PHP_SELF']."?msg=4");
		exit();
	}

	elseif (empty($email_form) and check_prof()) {
		header("location:". $_SERVER['PHP_SELF']."?msg=4");
		exit();
	}

	elseif (strstr($username_form, "'") or strstr($username_form, '"') or strstr($username_form, '\\')){
		header("location:". $_SERVER['PHP_SELF']."?msg=10");
		exit();
	}

	// check if username is free
	elseif(isset($user_exist) AND ($username_form==$user_exist) AND ($username_form!=$uname)) {
		header("location:". $_SERVER['PHP_SELF']."?msg=5");
		exit();
	}

	// check if email is valid
	elseif (!email_seems_valid($email_form) and check_prof()) {
		header("location:". $_SERVER['PHP_SELF']."?msg=6");
		exit();
	}

	// everything is ok
	else {
		##[BEGIN personalisation modification]############
		$_SESSION['langswitch'] = $language = langcode_to_name($_REQUEST['userLanguage']);
		$langcode = langname_to_code($language);

		if ($_SESSION['csrfToken'] === $_POST['csrfToken']){

			$username_form = escapeSimple($username_form);
			$nom_form = escapeSimple($nom_form);
			$prenom_form = escapeSimple($prenom_form);

			// mysql_query("UPDATE user
			//     SET nom='$nom_form', prenom='$prenom_form',
			//     username='$username_form', email='$email_form', am='$am_form',
			//         perso='$persoStatus', lang='$langcode'
			// 	WHERE user_id='".$_SESSION["uid"]."'")   
		

			$sql = $pdodb->prepare("UPDATE user
	        SET nom= ? , prenom= ?,
	        username= ? , email= ?, am=?,
	            perso=?, lang=?
			WHERE user_id=?");
			$user_id = intval($_SESSION["uid"]);
			$sql->bindParam(1, $nom_form);
			$sql->bindParam(2, $prenom_form);
			$sql->bindParam(3, $username_form);
			$sql->bindParam(4, $email_form);
			$sql->bindParam(5, $am_form);
			$sql->bindParam(6, $persoStatus);
			$sql->bindParam(7, $langcode);
			$sql->bindParam(8, $user_id);

			if($sql->execute()){
				if (isset($_SESSION['user_perso_active']) and $persoStatus == "no") {
                			unset($_SESSION['user_perso_active']);
				}
				header("location:". $_SERVER['PHP_SELF']."?msg=1");
				exit();
			}
		}
	}
}	// if submit

##[BEGIN personalisation modification - For LDAP users]############
if (isset($submit) && isset($ldap_submit) && ($ldap_submit == "ON")) {
	$_SESSION['langswitch'] = $language = langcode_to_name($_REQUEST['userLanguage']);
	$langcode = langname_to_code($language);

	if ($_SESSION['csrfToken'] === $_POST['csrfToken']){

		$sql = $pdodb -> prepare("UPDATE user SET perso = ?,
		lang = ? WHERE user_id= ?");
		$user_id = intval($_SESSION["uid"]);
		$sql->bindParam(1, $persoStatus);
		$sql->bindParam(2, $langcode);
		$sql->bindParam(3, $user_id);
		$sql ->execute();
		
		// mysql_query("UPDATE user SET perso = '$persoStatus',
		// 	lang = '$langcode' WHERE user_id='".$_SESSION["uid"]."' ");
		
		if (isset($_SESSION['user_perso_active']) and $persoStatus == "no") {
			unset($_SESSION['user_perso_active']);
		}

		header("location:". $_SERVER['PHP_SELF']."?msg=1");
		exit();
	}
}
##[END personalisation modification]############

//Show message if exists
if(isset($msg))
{
	switch ($msg){
		case 1: { //profile information changed successfully (not the password data!)
			$message = $langProfileReg;
			$urlText = $langHome;
			$type = "success_small";
			break;
		}
		case 3: { //pass too easy
			$message = $langPassTooEasy.": <strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong>";
			$urlText = "";
			$type = "caution_small";
			break;
		}
		case 4: { // empty fields check
			$message = $langFields;
			$urlText = "";
			$type = "caution_small";
			break;
		}
		case 5: {//username already exists
			$message = $langUserFree;
			$urlText = "";
			$type = "caution_small";
			break;
		}
		case 6: {//email not valid
			$message = $langEmailWrong;
			$urlText = "";
			$type = "caution_small";
			break;
		}
		case 10: { // invalid characters
			$message = $langInvalidCharsUsername;
			$urlText = "";
      $type = "caution_small";
      break;
		}
		default:die("invalid message id");
	}

	$tool_content .=  "<p class=\"$type\">$message<br><a href=\"../../index.php\">$urlText</a></p><br/>";

}

// $sqlGetInfoUser ="SELECT nom, prenom, username, password, email, am, perso, lang
// 	FROM user WHERE user_id='".$uid."'";

// $result=mysql_query($sqlGetInfoUser);
// $myrow = mysql_fetch_array($result);
$pdprof = new PDO("mysql:host=$mysqlServer;dbname=$mysqlMainDb",$mysqlUser, $mysqlPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$sqlGetInfoUser = $pdprof->prepare("SELECT nom, prenom, username, password, email, am, perso, lang
FROM user WHERE user_id= ?");

$sqlGetInfoUser->bindParam(1, $uid);
$sqlGetInfoUser->execute();
$myrow = $sqlGetInfoUser->fetch(PDO::FETCH_ASSOC);


$nom_form = $myrow['nom'];
$prenom_form = $myrow['prenom'];
$username_form = $myrow['username'];
$password_form = $myrow['password'];
$email_form = $myrow['email'];
$am_form = $myrow['am'];
##[BEGIN personalisation modification, added 'personalisation on SELECT]############
$persoStatus=	$myrow['perso'];
$userLang = $myrow['lang'];
if ($persoStatus == "yes")  {
	$checkedClassic = "checked";
	$checkedPerso = "";
} else {
	$checkedClassic  = "";
	$checkedPerso = "checked";
}

##[END personalisation modification]############

unset($_SESSION['uname']);
unset($_SESSION['pass']);
unset($_SESSION['nom']);
unset($_SESSION['prenom']);

$_SESSION['uname'] = $username_form;
$_SESSION['pass'] = $password_form;
$_SESSION['nom'] = $nom_form;
$_SESSION['prenom'] = $prenom_form;

##[BEGIN personalisation modification]############IT DOES NOT UPDATE THE DB!!!
if (isset($_SESSION['perso_is_active'])) {
	if ($persoStatus == "no") {
		$_SESSION['user_perso_active'] = TRUE;
	} else {
		unset($_SESSION['user_perso_active']);
	}
}

##[END personalisation modification]############

$sec = $urlSecure.'modules/profile/profile.php';
$passurl = $urlSecure.'modules/profile/password.php';
$authmethods = array("imap","pop3","ldap","db","shibboleth");

if ((!isset($changePass)) || isset($_POST['submit'])) {
	$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\">";
	if(!in_array($password_form,$authmethods)) {
		$tool_content .= "<li><a href=\"".$passurl."\">".$langChangePass."</a></li>";
	}
	$_SESSION['csrfToken'] = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 32);

	$tool_content .= " <li><a href='../unreguser/unreguser.php'>$langUnregUser</a></li>";
	$tool_content .= "</ul></div>";
	$tool_content .= "<form method=\"post\" action=\"$sec?submit=yes\"><br/>
    <table width=\"99%\">
    <tbody><tr>
       <th width=\"220\" class='left'>$langName</th>";

	if (isset($_SESSION['shib_user'])) {
                $auth_text = "Shibboleth user";
		$tool_content .= "<td class=\"caution_small\">&nbsp;&nbsp;&nbsp;&nbsp;<b>".$prenom_form."</b> [".$auth_text."]
	        <input type=\"hidden\" name=\"prenom_form\" value=\"$prenom_form\"></td>";
	} else {
		$tool_content .= "<td><input class='FormData_InputText' type=\"text\" size=\"40\" name=\"prenom_form\" value=\"$prenom_form\"></td>";
	}
	
	$tool_content .= "</tr>
    <tr>
       <th class='left'>$langSurname</th>";
	if (isset($_SESSION['shib_user'])) {
                $auth_text = "Shibboleth user";
		$tool_content .= "<td class=\"caution_small\">&nbsp;&nbsp;&nbsp;&nbsp;<b>".$nom_form."</b> [".$auth_text."]
                <input type=\"hidden\" name=\"nom_form\" value=\"$nom_form\"></td>";
	} else {
       		$tool_content .= "<td><input class='FormData_InputText' type=\"text\" size=\"40\" name=\"nom_form\" value=\"$nom_form\"></td>";
	}
    $tool_content .= "</tr>";

	if(!in_array($password_form,$authmethods) and $allow_username_change) {
		$tool_content .= "<tr>
       <th class='left'>$langUsername</th>
       <td><input class='FormData_InputText' type=\"text\" size=\"40\" name=\"username_form\" value=\"$username_form\"></td>
    </tr>";
	}
	else		// means that it is external auth method, so the user cannot change this password
	{
		switch($password_form)
		{
			case "pop3": $auth=2;break;
			case "imap": $auth=3;break;
			case "ldap": $auth=4;break;
			case "db": $auth=5;break;
			default: $auth=1;break;
		}
		if (isset($_SESSION['shib_user'])) {
			$auth_text = "Shibboleth user";
		} else {
			$auth_text = get_auth_info($auth);
		}
		$tool_content .= "
    <tr>
      <th class='left'>".$langUsername. "</th>
      <td class=\"caution_small\">&nbsp;&nbsp;&nbsp;&nbsp;<b>".$username_form."</b> [".$auth_text."]
        <input type=\"hidden\" name=\"username_form\" value=\"$username_form\">
      </td>
    </tr>";
	}

	$tool_content .= "<tr><th class='left'>$langEmail</th>";

	if (isset($_SESSION['shib_user'])) {
        	$tool_content .= "<td class=\"caution_small\">&nbsp;&nbsp;&nbsp;&nbsp;<b>".$email_form."</b> [".$auth_text."]
                <input type=\"hidden\" name=\"email_form\" value=\"$email_form\"></td>";
	} else {
		$tool_content .= "<td><input class='FormData_InputText' type=\"text\" size=\"40\" name=\"email_form\" value=\"$email_form\"></td>";
	}
    $tool_content .= "</tr><tr>
        <th class='left'>$langAm</th>
        <td><input class='FormData_InputText' type=\"text\" size=\"40\" name=\"am_form\" value=\"$am_form\"></td>
    </tr>";
	##[BEGIN personalisation modification]############
	if (isset($_SESSION['perso_is_active'])) {
		$tool_content .= "<tr><th class='left'>$langPerso</th><td>
		<input class='FormData_InputText' type=radio name='persoStatus' value='no' $checkedPerso>$langModern&nbsp;
		<input class='FormData_InputText' type=radio name='persoStatus' value='yes' $checkedClassic>$langClassic
		</td>
    </tr>";
	}
	##[END personalisation modification]############
	$tool_content .= "
    <tr>
      <th class='left'>$langLanguage</th>
      <td>
        " . lang_select_options('userLanguage') . "
      </td>
    </tr>
	<tr>
	  <th>&nbsp;</th>
	  <input type='hidden' name='csrfToken' value='".@$_SESSION['csrfToken']."'/>
      <td><input type=\"Submit\" name=\"submit\" value=\"$langModify\"></td>
    </tr>
    </tbody>
    </table>

</form>
   ";
}

draw($tool_content, 1);
