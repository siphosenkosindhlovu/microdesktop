<?php
# Don't put anything above the previous line, not even blank space

# Copyright 2007, Thomas Boutell and Boutell.Com, Inc. You
# MAY use this code in your own projects. You MAY NOT
# represent this code as your own work. If you wish to share
# this code with others, please do so by sharing the
# following URL:
#
#
# See: http://www.boutell.com/newfaq/creating/accounts.html

#require "login.php";

require '/home/boutell/html/tools/accountable/login.php';

require '/home/boutell/html/tools/captcha/captcha.php';

$recipient = 'sipho.ndhlovu@outlook.com';


$serverName = 'www.boutell.com';

if ($_POST['send']) {
	sendMail();
} elseif (($_POST['cancel']) || ($_POST['continue'])) {
	redirect();
} else {
	displayForm(false);
}

function displayForm($messages)
{
	global $login;

	$escapedEmail = htmlspecialchars($_POST['email']);
	$escapedRealName = htmlspecialchars($_POST['realname']);
	$escapedSubject = htmlspecialchars($_POST['subject']);
	$escapedBody = htmlspecialchars($_POST['body']);
	$returnUrl = $_POST['returnurl'];
	if (!strlen($returnUrl)) {
		$returnUrl = $_SERVER['HTTP_REFERER'];
		if (!strlen($returnUrl)) {
			$returnUrl = '/';
		}
	}
	$escapedReturnUrl = htmlspecialchars($returnUrl);
?>
<html>
<head>
<?php
	if ($login) {
?>
<link href="/accountable/chrome/login.css" rel="stylesheet" type="text/css">
<?php
	}
?>
<title>Contact Us</title>
</head>
<body>
<?php
	if ($login) {
		$login->prompt();
		if (!strlen($escapedEmail)) {
			$escapedEmail = htmlspecialchars($_SESSION['email']);
		}
		if (!strlen($escapedRealName)) {
			$escapedRealName = htmlspecialchars($_SESSION['realname']);
		}
	}
?>
<h1>Contact Us</h1>
<?php
	if (count($messages) > 0) {
		$message = implode("<br>\n", $messages);
		echo("<h3>$message</h3>\n");
	}
?>
<form method="POST" action="<?php echo $_SERVER['DOCUMENT_URL']?>">
<p>
<input
	name="email"
	size="64"
	maxlength="64"
	value="<?php echo $escapedEmail?>"/>
	<b>Your</b> Email Address
</p>
<p>
<input
	name="realname"
	size="64"
	maxlength="64"
	value="<?php echo $escapedRealName?>"/>
	Your Real Name (<i>so our reply won't get stuck in your spam folder</i>)
</p>
<p>
<input
	name="subject"
	size="64"
	maxlength="64"
	value="<?php echo $escapedSubject?>"/>
	Subject Of Your Message
</p>
<p>
<i>Please enter the text of your message in the field that follows.</i>
</p>
<textarea
	name="body"
	rows="10"
	cols="60"><?php echo $escapedBody?></textarea>
<?php
	if ((!$_SESSION['id']) && (function_exists('captchaImgUrl'))) {
?>
<p>
<b>Please help us prevent fraud</b> by entering the code displayed in the
image in the text field. Alternatively,
you may click <b>Listen To This</b> to hear the code spoken aloud.
</p>
<p>
<img style="vertical-align: middle"
	src="<?php echo captchaImgUrl()?>"/>
	<input name="captcha" size="8"/>
	<a href="<?php echo captchaWavUrl()?>">Listen To This</a>
</p>
<?php
	}
?>
<p>
<input type="submit" name="send" value="Send Your Message"/>
<input type="submit" name="cancel" value="Cancel - Never Mind"/>
</p>
<input
	type="hidden"
	name="returnurl"
	value="<?php echo $escapedReturnUrl?>"/>
</form>
</body>
</html>
<?php
}

function redirect()
{
	global $serverName;
	$returnUrl = $_POST['returnurl'];
	$prefix = "http://$serverName/";
	if (!beginsWith($returnUrl, $prefix)) {
		$returnUrl = "http://$serverName/";
	}
	header("Location: $returnUrl");
}

function beginsWith($s, $prefix)
{
	return (substr($s, 0, strlen($prefix)) === $prefix);
}

function sendMail()
{
	global $recipient;
	$messages = array();
	$email = $_POST['email'];
	if (!preg_match("/^[\w\+\-\.\~]+\@[\-\w\.\!]+$/", $email)) {
		$messages[] = "That is not a valid email address. Perhaps you left out the @something.com part?";
	}
	$realName = $_POST['realname'];
	if (!preg_match("/^[\w\ \+\-\'\"]+$/", $realName)) {
		$messages[] = "The real name field must contain only alphabetical characters, numbers, spaces, and the + and - signs. We apologize for any inconvenience.";
	}
	$subject = $_POST['subject'];
	$subject = preg_replace('/\s+/', ' ', $subject);
	if (preg_match('/^\s*$/', $subject)) {
		$messages[] = "Please specify a subject for your message.";
	}

	$body = $_POST['body'];
        if (preg_match('/^\s*$/', $body)) {
		$messages[] = "Your message was blank. Did you mean to say something? Click the Cancel button if you do not wish to send a message.";
	}
	if ((!$_SESSION['id']) && function_exists('captchaImgUrl')) {
		if ($_POST['captcha'] != $_SESSION['captchacode']) {
			$messages[] = "You did not enter the security code, or what you entered did not match the code. Please try again.";
		}
	}
	if (count($messages)) {
		displayForm($messages);
		return;
	}
	mail($recipient,
		$subject,
		$body,
		"From: $realName <$email>\r\n" .
		"Reply-To: $realName <$email>\r\n");
	$escapedReturnUrl = htmlspecialchars($_POST['returnurl']);
?>
<html>
<head>
<title>Thank You</title>
</head>
<body>
<h1>Thank You</h1>
<p>
Thank you for contacting us! Your message has been sent.
</p>
<form method="POST" action="<?php echo $_SERVER['DOCUMENT_URL']?>">
<input type="submit" name="continue" value="Click Here To Continue"/>
<input
	type="hidden"
	name="returnurl"
	value="<?php echo $escapedReturnUrl?>"/>
</form>
</body>
</html>
<?php
}
?>
