<?php
	
echo "<div class='row'><div class='col-md-12'>
<h2>Your settings</h2>\n";

if ($u->logged_in()) {
echo "<h3>New Email</h3>
<p>If you have a new email, enter it below. We will send a link to your new email. Click that link and we'll reset your account to use that email.</p>\n
<div class='row'><div class='col-md-4'>";
echo $u->get_change_email_form()->get_form();
echo "</div></div> <!-- end of col and row -->

<h3>Reset Your Link</h3>
<p>For the paranoid: This will log you out, generate a new key, and a send a link to your email. If you don't even understand this then don't worry about it. <a class='btn btn-primary' href='$sc?ac=reset'>Reset My Login Link</a></p>

<h3>Never Mind</h3>
<p>Just <a href='$sc'>go back to the main page</a>.</p>
</div></div>\n";


} else {
	echo "<p>You are not logged in! Go back to the <a href='$sc'>front page</a> and enter your email. We'll email you a link so you can log in.</p>\n";
}

?>

