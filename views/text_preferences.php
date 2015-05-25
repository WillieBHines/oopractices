<?php
	
echo "<div class='row'><div class='col-md-6'>
<h2>Your settings</h2>";

if ($u->logged_in()) {
	echo "<h3>Text Notifications</h3>
		<p>If you want notifications via text, check the box and set your phone info.</p>\n";			
	echo $u->get_text_preferences_form()->get_form();
} else {
	echo "<p>You are not logged in! Go back to the <a href='$sc'>front page</a> and enter your email. We'll email you a link so you can log in.</p>\n";
}
echo "<p>Just <a href='$sc'>go back to the main page</a>.</p>
</div></div>\n";
		
?>
		