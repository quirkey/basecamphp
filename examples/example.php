<?php
require 'customcamp.php';

$session = new Customcamp('USER','PASS','http://BASECAMPURL');
$all_uncomplete = $session->find_all_uncomplete_todos();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Basecamp PHP Example</title>
</head>
<style type="text/css" media="screen">
/* <![CDATA[ */
body {
	font-family: Arial, "MS Trebuchet", sans-serif;
	font-size: 12px;
}
.rank, .project_name, .list_name {
	float:left;
	width:100px;
}
.item_content {
	margin-left:200px;
}
.todo {
	clear:both;
}	
	
/* ]]> */
</style>
<script type="text/javascript" language="javascript" charset="utf-8">
// <![CDATA[
	
// ]]>
</script>
<body>
<h1>Basecamp</h1>
<h2>New Todo</h2>
<input id="project_name" >
<h2>All todos!</h2>
<?php foreach ($all_uncomplete as $todo_item ) { ?>
	<div class="todo">
		<!-- <div class="rank"><?= round($todo_item->rank) ?></div> -->
		<div class="project_name"><?= $todo_item->in_project->name ?></div>
		<div class="list_name"><?= $todo_item->in_list->name ?></div>
	 	<div class="item_content"><?= $todo_item->content ?></div>
	</div>
<?php } ?>
</body>
</html>