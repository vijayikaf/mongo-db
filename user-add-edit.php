<?php
include('config.php');
$error = array();
$id = $_REQUEST['id'];
$mongo = new MongoDbClass('user');
if(isset($_POST['save'])){
	extract($_POST);
	if(empty($name)){
		$error['name'] = 'Name is required'; 
	}
	if(empty($email)){
		$error['email'] = 'Email is required'; 
	}
	if(empty($error)){
		$document = array(
			'name' => $name,
			'email' => $email,
		);
		if(!empty($id)){
			$condition = array('email'=>$id);			
			$mongo->update($condition, $document);
		}else{
			$mongo->insert($document);	
		}
		header('Location:user-list.php');		
	}
}
$name = '';
$email = '';
if($id){
	$res = $mongo->getRow(array('email'=>$id));
	$name = $res['name'];
	$email = $res['email'];
}

?>
<a href="user-list.php">Back</a>
<form method="post" action="">
	<table>
		<tr>
			<td>Name:</td>
			<td>
				<input type="text" name="name" value="<?php echo($name); ?>"><br>
				<?php echo(isset($error['name']) ? $error['name'] : '') ?>
			</td>
		</tr>
		<tr>
			<td>Email:</td>
			<td>
				<input type="text" name="email" value="<?php echo($email); ?>"><br>
				<?php echo(isset($error['email']) ? $error['email'] : '') ?>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="save" value="Save"></td>
		</tr>
	</table>
</form>	