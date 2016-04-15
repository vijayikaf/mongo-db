<?php
include('config.php');
$mongo = new MongoDbClass('user');
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete'){
	$id = $_REQUEST['id'];
	$condition = array('email'=>$id);
	$mongo->delete($condition);
	header('Location:user-list.php');
}


$results = $mongo->getAll();
//echo '<pre>';print_r($results);
?>
<a href="user-add-edit.php">Add New</a>
<table width="100%">
	<tr>
		<th align="left">ID</th>
		<th align="left">Name</th>
		<th align="left">Email</th>
		<th align="left">Action</th>
	</tr>
	<?php  
	if($results){
		foreach ($results as $result) {
			
	?>
			<tr>
				<td><?php echo($result['_id']) ?></td>
				<td><?php echo($result['name']) ?></td>
				<td><?php echo($result['email']) ?></td>
				<td><a href="user-add-edit.php?id=<?php echo($result['email']) ?>">Edit</a> / <a href="?action=delete&id=<?php echo($result['email']) ?>">Delete</a></td>
			</tr>
	<?php
		}
	}
	?>
</table>