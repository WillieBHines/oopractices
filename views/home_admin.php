<p>
<a class='btn btn-default' href='#add'>add a workshop</a> 
<a class='btn btn-default' href='$sc?v=gemail'>get emails</a> 
<a class='btn btn-default' href='$sc?v=search'>find students</a>
<a class='btn btn-default' href='$sc?v=allchange'>change log</a>
</p>
<h2>All Practices</h2>
<?php include "{$path}workshop_list.php"; ?>
<a name='add'></a><div class='row'><div class='col-md-5'><form action='$sc' method='post'>
<fieldset name=\"session_add\"><legend>Add Session</legend>
<?php 
echo $wk->get_workshop_form(true)->get_form(); 
?>		
</fieldset></form></div></div>