<?php
class MongoApi{
	
	private $connection = null;
	private $db = null;
	private $collection = null;

	function __construct($collection){
		$this->connection = new MongoClient('mongodb://'.MONGO_SERVER.':'.MONGO_PORT); // connect to a remote host at a given port
		if(!$this->connection){
			return "Mongo Error";
		}else{			
			$this->db = $this->connection->selectDB("trackActivityDb"); //select DB
			$this->collection = $this->db->selectCollection($collection);//select collection
		}
	}
	
	//just for testing..
	function testEntry($document){
		$collection = $this->collection;
		$ret = $collection->insert($document);
		return $ret;
	}
	
	
	// save/update video activity data in mongodb
	function saveVideoActivityData($document){
		$collection = $this->collection;
		$activityDate = date('Y-m-d').'T'.date('H:i:s');		
		$userId = $document['User ID'];
		$bookId = $document['Book Details']['Book ID'];	
			
		$saveData = $this->getVideoActivityDataFromMongoDb($userId,$bookId);
		if(!empty($saveData['Activity Date'])){
			$activityDate = $saveData['Activity Date'];
			$collection->remove(array("User ID"=>$userId,"Book Details.Book ID"=>$bookId));
		}
		$document["Activity Date"] = $activityDate;
		$ret = $collection->insert($document);
		return $ret;
	}
	
	
	
	//save/update ass activity data in mongodb
	function saveAssActivityData($document){
		$collection = $this->collection;
		$document["Activity Date"] = '';
		$activityDate = date('Y-m-d').'T'.date('H:i:s');
		//$attemptId = $document['AttemptID'];
		/*
		$saveData = $this->getAssActivityDataFromMongoDb($attemptId);
		if(!empty($saveData['Activity Date'])){
			$activityDate = $saveData['Activity Date'];
			$collection->remove(array("AttemptID"=>$attemptId));
		}*/
		$document["Activity Date"] = $activityDate;
		$ret = $collection->insert($document);
		return $ret;
	}
	
	
	
	
	//get video activity data from mongodb
	function getVideoActivityDataFromMongoDb($userId,$bookId){
		$collection = $this->collection;
		$ret = array();
		$condition = array("User ID"=>$userId,"Book Details.Book ID"=>$bookId);
		/*
		$cursor = $collection->findOne($condition);
		$ret = iterator_to_array($cursor);
		*/
		
		$ret = $collection->findOne($condition);
		return $ret;
	}
	
	//get ass activity data from mongodb
	function getAssActivityDataFromMongoDb($AttemptID){
		$collection = $this->collection;
		$ret = array();
		$condition = array("AttemptID"=>$AttemptID);
		$ret = $collection->findOne($condition);
		//$cursor = iterator_to_array($cursor);
		return $ret;
	}
	
	//get all ass activity data for session (attempt id) from mongodb
	function getAllAssActivityDataFromMongoDbByAttemptId($AttemptID){
		$collection = $this->collection;
		$ret = array();
		$condition = array("AttemptID"=>$AttemptID);
		$cursor = $collection->find($condition);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	
	//get All ass activity data from mongodb
	function getAllAssActivityDataFromMongoDb(){
		$collection = $this->collection;
		$ret = array();
		$condition = array("AttemptID" => array('$ne' => null));
		$cursor = $collection->find($condition);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	//get All ass activity data from mongodb
	function getAllAssQuesActivityDataFromMongoDb(){
		$collection = $this->collection;
		$ret = array();
		$condition = array();
		//$condition = array("AttemptID" => array('$ne' => null));
		$cursor = $collection->find($condition);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	//get video activity data from mongodb
	function getVideoActivityDataByUserIdFromMongoDb($userId){
		$collection = $this->collection;
		$ret = array();
		$condition = array("User ID"=>$userId);
		$cursor = $collection->find($condition);
		$ret = iterator_to_array($cursor);
		return $ret;
	}

	//get video activity data from mongodb without condition
	/*
	function getVideoActivityDataFromMongoDb(){
		$ret = array();
		$condition = array();
		$cursor = $collection->find($condition);
		$ret = iterator_to_array($cursor);
		return $ret;
	}*/	
	
	
	//get activity VIDEO data from mongodb
	function filterVideoActivityDataFromMongoDb($filterBy,$catObj=null,$mysqli=null){
		$collection = $this->collection;
		$ret = array();
	    $userId = intval($filterBy['userId']);
		
		
		
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];
		
		if(!empty($filterBy['catId'])){
			$catId = array();
			$subId = intval($filterBy['catId']);
			$catId = $catObj->getTopicIdFromSubjectId($subId,$mysqli);
		}
		
		if(!empty($catId) && !empty($gtDateTime) && !empty($ltDateTime)){
			
			$filterCond = array("User ID"=>$userId,"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Book Details.Cat.Cat ID"=>array('$in' => $catId));
		}else if(!empty($gtDateTime) && !empty($ltDateTime) && empty($catId)){			
			//echo 123;die;
			$filterCond = array("User ID"=>$userId,"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")));
			
		}else if(empty($gtDateTime) && empty($ltDateTime) && !empty($catId)){
			
			$filterCond = array("User ID"=>$userId,"Book Details.Cat.Cat ID"=>array('$in' => $catId));
		}
		$cursor = $collection->find($filterCond);		
		$ret = iterator_to_array($cursor);	
		//echo $userId;		
		return $ret;
	}
	//$filterCond = array("Activity Date"=>array('$gt'=>Date("2015-08-28T00:00:00"),'$lt'=>Date("2015-08-30T23:59:59")));
	
	
	
	//get activity VIDEO data from mongodb  (4sept)
	function filterVideoActivityDataByCatgsFromMongoDb($filterBy,$catObj=null,$mysqli=null){
		$collection = $this->collection;		
		$ret = array();
	    $catgs = array();
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];
		
		if(!empty($filterBy['catIds']))	{	
			$catgs = $filterBy['catIds'];
		}
		
		$filterCond = array("Book Details.Cat.Cat ID" => array('$in' => $catgs),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")));
		$cursor = $collection->find($filterCond);
		
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	
	//get activity ASS data from mongodb
	function filterAssActivityDataFromMongoDb($filterBy,$catObj=null,$mysqli=null){
		$collection = $this->collection;
		$ret = array();
		$filterCond = array();
		$AttemptIdIn = $filterBy['AttemptID'];
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];
		if(!empty($filterBy['catId'])){		
			$catId = array();
			$subId = intval($filterBy['catId']);
			$catId = $catObj->getTopicIdFromSubjectId($subId,$mysqli);
		}
		
		if(!empty($gtDateTime) && !empty($ltDateTime) && !empty($catId)){				
				$filterCond = array("AttemptID" => array('$in' => $AttemptIdIn),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Question Details.Cat.Cat ID"=>array('$in' => $catId));
				
		}else if(!empty($gtDateTime) && !empty($ltDateTime) && empty($catId)){
			$filterCond = array("AttemptID" => array('$in' => $AttemptIdIn),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")));
		}
		
		//echo '<pre>';print_r($filterCond);
		
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	
	//get activity ASS data from mongodb for review Concept Pass
	function filterAssActivityDataFromMongoDbByTopic($filterBy,$catObj=null,$mysqli=null){
		$collection = $this->collection;
		$ret = array();
		$filterCond = array();
		$AttemptIdIn = $filterBy['AttemptID'];
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];
		if(!empty($filterBy['catId'])){
			$catId = intval($filterBy['catId']);
		}
		
		if(!empty($gtDateTime) && !empty($ltDateTime) && !empty($catId)){				
				$filterCond = array("AttemptID" => array('$in' => $AttemptIdIn),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Question Details.Cat.Cat ID"=>$catId);
				
		}else if(!empty($gtDateTime) && !empty($ltDateTime) && empty($catId)){
			$filterCond = array("AttemptID" => array('$in' => $AttemptIdIn),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")));
		}
		
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	//get activity ASS data from mongodb
	function filterAssActivityDataWithRightAnsFromMongoDb($filterBy,$catObj=null,$mysqli=null){
		$collection = $this->collection;
		$ret = array();
		$filterCond = array();
		$AttemptIdIn = $filterBy['AttemptID'];
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];		
		if(!empty($filterBy['catId']))	{	
			$catId = array();
			$subId = intval($filterBy['catId']);
			$catId = $catObj->getTopicIdFromSubjectId($subId,$mysqli);
		}
		if(!empty($gtDateTime) && !empty($ltDateTime) && !empty($catId)){				
				$filterCond = array("UserResponse.ResultSuccess"=>1,"AttemptID" => array('$in' => $AttemptIdIn),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Question Details.Cat.Cat ID"=>array('$in' => $catId));
				
		}else if(!empty($gtDateTime) && !empty($ltDateTime) && empty($catId)){
			$filterCond = array("UserResponse.ResultSuccess"=>1,"AttemptID" => array('$in' => $AttemptIdIn),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime"))); 
		}
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	
	//get activity ASS data from mongodb By Topic
	function filterAssActivityDataWithRightAnsFromMongoDbByTopic($filterBy,$catObj=null,$mysqli=null){
		$collection = $this->collection;
		$ret = array();
		$filterCond = array();
		$AttemptIdIn = $filterBy['AttemptID'];
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];		
		
		if(!empty($filterBy['catId'])){
			$catId = intval($filterBy['catId']);
		}
			
		
		if(!empty($gtDateTime) && !empty($ltDateTime) && !empty($catId)){				
				$filterCond = array("UserResponse.ResultSuccess"=>1,"AttemptID" => array('$in' => $AttemptIdIn),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Question Details.Cat.Cat ID"=>$catId);
				
		}else if(!empty($gtDateTime) && !empty($ltDateTime) && empty($catId)){
			$filterCond = array("UserResponse.ResultSuccess"=>1,"AttemptID" => array('$in' => $AttemptIdIn),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime"))); 
		}
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	
	
	
	//get activity ASS data by date and cat id from mongodb  (4sept)
	function getAssActivityByCatgFromMongoDb($filterBy){
		$collection = $this->collection;
		$ret = array();
		$catgs = array();
		$filterCond = array();
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];	
		if(!empty($filterBy['catIds']))		
			$catgs = $filterBy['catIds'];
		
		$filterCond = array("AttemptID" => array('$ne' => null),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Question Details.Cat.Cat ID"=>array('$in' => $catgs));
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	//4sept for position on dashboard
	function getRightAnsByCatgAndDates($filterBy){
		$collection = $this->collection;
		$ret = array();
		$catgs = array();
		$filterCond = array();
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];	
		if(!empty($filterBy['catIds']))		
			$catgs = $filterBy['catIds'];
		
		$filterCond = array("UserResponse.ResultSuccess"=>1,"AttemptID" => array('$ne' => null),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Question Details.Cat.Cat ID"=>array('$in' => $catgs));
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	

	//get activity ASS data by attempt id and cat id from mongodb (not use)  (4sept)
	function getAssActivityByAttemptIdFromMongoDb($filterBy){
		$collection = $this->collection;
		$ret = array();
		$filterCond = array();
		$catgs = array();
		if(!empty($filterBy['catIds']))		
			$catgs = $filterBy['catIds'];
		
		$filterCond = array("AttemptID" => array('$in' => $AttemptIdIn),"Question Details.Cat.Cat ID"=>array('$in' => $catgs));
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	
	
	
	
	
	//22 sept for red topics start
	function getAllAssActivityForRedScoreByTopicId($filterBy){
		$collection = $this->collection;
		$ret = array();
		$AttemptIdIn = $filterBy['AttemptID'];
		
		if(!empty($filterBy['topicId'])){
			$topicId = intval($filterBy['topicId']);
		}
		$filterCond = array();
		$filterCond = array("AttemptID" => array('$in' => $AttemptIdIn),"Question Details.Cat.Cat ID"=>$topicId);
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	function getRightAssActivityForRedScoreByTopicId($filterBy){
		$collection = $this->collection;
		$ret = array();
		$AttemptIdIn = $filterBy['AttemptID'];
		
		if(!empty($filterBy['topicId'])){
			$topicId = intval($filterBy['topicId']);
		}	
		$filterCond = array();
		$filterCond = array("UserResponse.ResultSuccess"=>1,"AttemptID" => array('$in' => $AttemptIdIn),"Question Details.Cat.Cat ID"=>$topicId);
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	//22 sept for red topics end
	
	
	function __destruct() {	
		$connections = $this->connection->getConnections();
		foreach ($connections as $con )
		{
			// Loop over all the connections, and when the type is "SECONDARY"
			// we close the connection
			if ( $con['connection']['connection_type_desc'] == "SECONDARY" )
			{
				//echo "Closing '{$con['hash']}': ";
				$closed = $a->close( $con['hash'] );
				//echo $closed ? "ok" : "failed", "\n";
			}
		}
	}
	
	
	/*********************  START ZIP/DOC TYPE ACTIVITY ***************/
	// save/update zip/doc activity data in mongodb
	function saveZipDocActivityData($document){
		$collection = $this->collection;
		$activityDate = date('Y-m-d').'T'.date('H:i:s');		
		$userId = $document['User ID'];
		$bookId = $document['Book Details']['Book ID'];	
			
		$saveData = $this->getZipDocActivityDataFromMongoDb($userId,$bookId);
		/*
		if(!empty($saveData['Activity Date']) && $saveData['Completed']=="NO"){
			//$activityDate = $saveData['Activity Date'];
			$collection->remove(array("User ID"=>$userId,"Book Details.Book ID"=>$bookId));
		}*/
		$collection->remove(array("User ID"=>$userId,"Book Details.Book ID"=>$bookId));
		$document["Activity Date"] = $activityDate;
		$ret = $collection->insert($document);
		return $ret;
	}
	
	
	//get zip/doc activity data from mongodb
	function getZipDocActivityDataFromMongoDb($userId,$bookId){
		$collection = $this->collection;
		$ret = array();
		$condition = array("User ID"=>$userId,"Book Details.Book ID"=>$bookId);
		/*
		$cursor = $collection->findOne($condition);
		$ret = iterator_to_array($cursor);
		*/		
		$ret = $collection->findOne($condition);
		return $ret;
	}
	
	
	//get activity ZIP/DOC data from mongodb
	function filterZipDocActivityDataFromMongoDb($filterBy,$catObj=null,$mysqli=null){
		$collection = $this->collection;
		$ret = array();
	    $userId = intval($filterBy['userId']);
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];
		
		if(!empty($filterBy['catId'])){		
			$catId = array();
			$subId = intval($filterBy['catId']);
			$catId = $catObj->getTopicIdFromSubjectId($subId,$mysqli);
		}
		
		if(!empty($catId) && !empty($gtDateTime) && !empty($ltDateTime)){
			$filterCond = array("User ID"=>$userId,"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Book Details.Cat.Cat ID"=>array('$in' => $catId));
		}else if(!empty($gtDateTime) && !empty($ltDateTime) && empty($catId)){
			$filterCond = array("User ID"=>$userId,"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")));
		}else if(empty($gtDateTime) && empty($ltDateTime) && !empty($catId)){
			$filterCond = array("User ID"=>$userId,"Book Details.Cat.Cat ID"=>array('$in' => $catId));
		}		
		$cursor = $collection->find($filterCond);		
		$ret = iterator_to_array($cursor);
		
		return $ret;
	}
	
	
	//get activity zip/doc data from mongodb by catgs
	function filterZipDocActivityDataByCatgsFromMongoDb($filterBy){
		$collection = $this->collection;		
		$ret = array();
	    $catgs = array();
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];		
		if(!empty($filterBy['catIds']))		
			$catgs = $filterBy['catIds'];
		
		$filterCond = array("Book Details.Cat.Cat ID" => array('$in' => $catgs),"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")));
		$cursor = $collection->find($filterCond);		
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	
	/************ FOR QUES START **************/
	
	//save/update ques activity data in mongodb
	function saveQuesActivityData($document){
		$collection = $this->collection;
		$document["Activity Date"] = '';
		$activityDate = date('Y-m-d').'T'.date('H:i:s');
		$userId = intval($document['User ID']);
		$bookId = intval($document['Book ID']);
		$quesId = intval($document['Question Details']['Question ID']);
		$saveData = $this->getSingleQuesActivityDataFromMongoDb($userId,$bookId);
		if(!empty($saveData['Activity Date'])){
			//$activityDate = $saveData['Activity Date'];
			$collection->remove(array("User ID"=>$userId,"Book ID"=>$bookId,"Question Details.Question ID"=>$quesId));
		}
		$document["Activity Date"] = $activityDate;
		$ret = $collection->insert($document);
		return $ret;
	}
	
	
	
	//get ques activity data from mongodb
	function getQuesActivityDataFromMongoDb($userId,$bookId){
		$collection = $this->collection;
		$ret = array();
		$condition = array("User ID"=>intval($userId),"Book ID"=>intval($bookId));
		//$condition = array("User ID"=>intval($userId),"Book ID"=>"$bookId");//review with sangram for bookid 
		//$condition = array("Book ID"=>"$bookId");
		/*
		$cursor = $collection->findOne($condition);
		$ret = iterator_to_array($cursor);
		*/		
		$ret = $collection->find($condition);
		
		return $ret;
	}
	
	//get video activity data from mongodb
	function getSingleQuesActivityDataFromMongoDb($userId,$bookId){
		$collection = $this->collection;
		$ret = array();
		$condition = array("User ID"=>intval($userId),"Book ID"=>intval($bookId));
		
		/*
		$cursor = $collection->findOne($condition);
		$ret = iterator_to_array($cursor);
		*/		
		$ret = $collection->findOne($condition);
		return $ret;
	}
	
	
	//get activity QUES data from mongodb
	function filterQuesActivityDataFromMongoDb($filterBy,$catObj=null,$mysqli=null){
		$collection = $this->collection;
		$ret = array();
		$filterCond = array();
		$userId = intval($filterBy['userId']);
		$gtDateTime = $filterBy['gt'];
		$ltDateTime = $filterBy['lt'];
		if(!empty($filterBy['catId'])){		
			$catId = array();
			$subId = intval($filterBy['catId']);
			$catId = $catObj->getTopicIdFromSubjectId($subId,$mysqli);
		}
		
		if(!empty($gtDateTime) && !empty($ltDateTime) && !empty($catId)){				
				$filterCond = array("User ID" => $userId,"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Question Details.Cat.Cat ID"=>array('$in' => $catId));
				
		}else if(!empty($gtDateTime) && !empty($ltDateTime) && empty($catId)){
			$filterCond = array("User ID" => $userId,"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")));
		}
		
		$cursor = $collection->find($filterCond);
		$ret = iterator_to_array($cursor);
		return $ret;
	}
	
	/************ FOR QUES END**************/
	
	
	
	
	
	/*********  Cource Book Start ********/
	
		//save cource book activity data in mongodb
		function saveCourceBookActivityData($document){
			$collection = $this->collection;
			$activityDate = date('Y-m-d').'T'.date('H:i:s');		
			$userId = $document['User ID'];
			$bookId = $document['Book Details']['Book ID'];	
				
			$saveData = $this->getCourceBookActivityDataFromMongoDb($userId,$bookId);
			/*
			if(!empty($saveData['Activity Date']) && $saveData['Completed']=="NO"){
				//$activityDate = $saveData['Activity Date'];
				$collection->remove(array("User ID"=>$userId,"Book Details.Book ID"=>$bookId));
			}*/
			$collection->remove(array("User ID"=>$userId,"Book Details.Book ID"=>$bookId));
			$document["Activity Date"] = $activityDate;
			$ret = $collection->insert($document);
			return $ret;
		}
		
		
		//get single cource book activity data from mongodb
		function getCourceBookActivityDataFromMongoDb($userId,$bookId){
			$collection = $this->collection;
			$ret = array();
			$condition = array("User ID"=>$userId,"Book Details.Book ID"=>$bookId);
			/*
			$cursor = $collection->findOne($condition);
			$ret = iterator_to_array($cursor);
			*/		
			$ret = $collection->findOne($condition);
			return $ret;
		}
		
		
		//get activity Cource Book data from mongodb
		function filterCourceBookActivityDataFromMongoDb($filterBy){
			$collection = $this->collection;
			$ret = array();
			$filterCond = array();
			$userId = intval($filterBy['userId']);
			$gtDateTime = $filterBy['gt'];
			$ltDateTime = $filterBy['lt'];
			if(!empty($filterBy['catId']))		
				$catId = intval($filterBy['catId']); //subject Id
			
			if(!empty($gtDateTime) && !empty($ltDateTime) && !empty($catId)){				
					$filterCond = array("User ID" => $userId,"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")),"Book Details.Cat.Cat ID"=>$catId);
					
			}else if(!empty($gtDateTime) && !empty($ltDateTime) && empty($catId)){
				$filterCond = array("User ID" => $userId,"Activity Date"=>array('$gte'=>Date("$gtDateTime"),'$lte'=>Date("$ltDateTime")));
			}
			
			$cursor = $collection->find($filterCond);
			$ret = iterator_to_array($cursor);
			return $ret;
		}
		
		
		
	/*********  Cource Book End ********/
	
	function getActualDates($filterBy){
	
		//$reqDataMongo['gt'] = date('Y-m-d',strtotime($_POST['startDate']));
		$filterBy['gt'] = date('Y-m-d',strtotime('-1 day',strtotime($filterBy['gt'])));
		$filterBy['gt'] = $filterBy['gt'].'T00:00:00';
		
		//$reqDataMongo['lt'] = date('Y-m-d',strtotime('+1 day',strtotime($_POST['endDate'])));
		$filterBy['lt'] = date('Y-m-d',strtotime($filterBy['lt']));
		$filterBy['lt'] = $filterBy['lt'].'T23:59:59';
		
		return $filterBy;
	}
	
	
	/**********Notes On Mongo**********
		$cursor = $collection->find(array("someField" => array('$ne' => null)));
	*****/
	
}
?>