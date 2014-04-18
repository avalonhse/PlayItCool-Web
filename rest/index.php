<?php

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

require('rb.php'); 
R::setup('mysql:host=localhost;dbname=playitcool','root','12345678');

$app = new \Slim\Slim();

$app->post('/hello/:name', function ($name) {
    echo "Hello, $name";
});

$app->get('/sensors', 'visualizeSensors');
$app->get('/sensors/:uid',  'getSensor');
$app->get('/teams','getTeamsJSON');
$app->post('/sensors', 'addSensor');
$app->post('/add','addPoint');

//$app->put('/sensors/:uid', 'updateSensor');
//$app->put('/sensors/:uid/:team', 'visitCheckpoint');

//$app->delete('/sensors/:uid',   'deleteSensor');


function getSensors() {  
  $sensors = R::find('sensor'); 
  
  // send response header for JSON content type
  //pp->response()->header('Content-Type', 'application/json');
  
  // return JSON-encoded response body with query results
  //ho json_encode(R::exportAll($sensors));
}
/*
function getSensors() {
	$sql = "select * FROM sensor";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$sensors = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"sensors": ' . json_encode($sensors) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
*/

function visualizeSensors() {
        $sql = "select * FROM sensor";
        try {
                $db = getConnection();
                $stmt = $db->query($sql);  
                $sensors = $stmt->fetchAll(PDO::FETCH_OBJ);
                $db = null;
                echo '{"sensors": ' . json_encode($sensors) . '}';
        } catch(PDOException $e) {
                echo '{"error":{"text":'. $e->getMessage() .'}}'; 
        }
}

function getTeamsJSON(){
    $teams = getTeams();
    echo '{"teams":'.json_encode($teams).'}';
}

function getTeams(){
    $db = getConnection();
    $sql = 'select * from team';
    $stmt = $db->query($sql);
    $teams = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    return $teams;
}




function getSensor($uid) {
/*    $sensors = R::load('sensor',$uid);

    // send response header for JSON content type
    //$app->response()->header('Content-Type', 'application/json');

    // return JSON-encoded response body with query results
    //echo json_encode(R::exportAll($sensors));  

*/
    $sql = "SELECT * FROM sensor WHERE uid=:uid";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("uid", $uid);
        $stmt->execute();
        $sensor = $stmt->fetchObject();
        $db = null;
        echo json_encode($sensor);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }


}

function addPoint() {
    error_log('addPoint\n', 3, '/var/tmp/php.log');
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    file_put_contents("/tmp/post.log",$request->getBody());
    $team= null;
    $search = 'select * from sensor where longtitude=:longtitude and latitude=:latitude';
    
    try{
	$db = getConnection();
	$stmt = $db->prepare($search);  
	$stmt->bindParam("longtitude", $data->checkpoint_lon);
	$stmt->bindParam("latitude", $data->checkpoint_lat);
	$stmt->execute();
	$sensor = $stmt->fetchObject();
	
	$searchTeam = 'select * from team where teamname=:teamname';
	$stmt = $db->prepare($searchTeam);
        $stmt->bindParam("teamname", $data->team);
	$stmt->execute();
        $team = $stmt->fetchObject();
	
	$length = count($data->humidity);
	$score = $length;
	$visit = 0;
	if ($data->team=='Resilients'){ $visit=1; }
	if ($data->team=='Engagers'){ $visit=-1; }
	
	$team->score = $team->score+$score;	
	$sensorScore = $sensor->currentScore+$visit;

	if ($sensorScore>4){
		if ($sensor->owner!='Resilients'){
			$team->checkpoints=$team->checkpoints+1;}
		$sensor->owner='Resilients';
		$sensorScore=5;
	}	

        if ($sensorScore<-4){
		if ($sensor->owner!='Engagers'){
                        $team->checkpoints=$team->checkpoints+1;}
                $sensor->owner='Engagers';                
		$sensorScore=-5;
        }
	if ($sensorScore==0){
		$sql = "UPDATE team SET checkpoints=checkPoints-1 WHERE uid!=:uid";
       		$stmt = $db->prepare($sql);
        	$stmt->bindParam("uid", $team->uid);
		$stmt->execute();
		$sensor->owner='none';
	}

	if ($data->team==$sensor->owner){ $score = $score*2;  }

	$sensor->currentScore = $sensorScore;
	$sensor->humidity=$data->humidity[$length-1];
        $sensor->temperature=$data->temperature[$length-1];
	date_default_timezone_set('Asia/Saigon');
        $sensor->latestCheck = date('Y-m-d h:i:s', time());	

	$sql = "UPDATE sensor SET owner=:owner, currentScore=:score,humidity=:humidity, temperature=:temperature, latestCheck=:latestCheck WHERE uid=:uid";
        $stmt = $db->prepare($sql);   
        $stmt->bindParam("uid", $sensor->uid);
        $stmt->bindParam("score", $sensor->currentScore);
	$stmt->bindParam("humidity", $sensor->humidity);
	$stmt->bindParam("temperature", $sensor->temperature);
	$stmt->bindParam("latestCheck", $sensor->latestCheck);
	$stmt->bindParam("owner", $sensor->owner);
        $stmt->execute();

	$sql = "UPDATE team SET score=:score,checkpoints=:checkpoints WHERE teamname=:teamname";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("teamname", $data->team);
        $stmt->bindParam("score", $team->score);
	$stmt->bindParam("checkpoints", $team->checkpoints);

        $stmt->execute();
        
        //echo json_encode($sensor);
    } 
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    } 
    $teams = getTeams();
    $data = array();    
    $data['team']=$team->teamname;
    $data['teams']=$teams;
    $data['point_scored']=$score;
    $data['checkpoint_owner']=$sensor->owner;
    echo json_encode($data);	    
    $db = null;          
}

function addSensor() {
    $sensor = R::dispense('sensor');
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $sensor->status = 0;
    date_default_timezone_set('Asia/Saigon');
    $time = date('Y-m-d h:i:s', time());
    $sensor->latestCheck = $time;
    $sensor->currentScore = 0;
    echo data;    
    //R::store($sensor);	
}

/*

function updateSensor($uid) {
	$request = Slim::getInstance()->request();
	$body = $request->getBody();
	$sensor = json_decode($body);
	$sql = "UPDATE sensors SET longtitude=:longtitude,latitude=:latitude, currentScore=:currentScore, controlScore=:controlScore, baseScore=:baseScore, status=:status, latestCheck=:latestCheck WHERE uid=:uid";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql); 
		$stmt->bindParam("uid", $uid);
        	$stmt->bindParam("longtitude", $sensor->longtitude);
        	$stmt->bindParam("latitude", $sensor->latitude);
        	$stmt->bindParam("currentScore", $sensor->currentScore);
        	$stmt->bindParam("controlScore", $sensor->controlScore);
        	$stmt->bindParam("baseScore", $sensor->baseScore);
        	$stmt->bindParam("status",sensor->status);
        	date_default_timezone_set('Asia/Saigon');
        	$time = date('Y-m-d h:i:s', time());
        	$stmt->bindParam("latestCheck", $time);

		$stmt->execute();
		$db = null;
		echo json_encode($sensor); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
/*
function visitCheckpoint($uid,$team) {
        $request = Slim::getInstance()->request();
        $body = $request->getBody();
        $sensor = json_decode($body);
        $sql = "UPDATE sensors SET currentScore=:currentScore, status=:status, latestCheck=:latestCheck WHERE uid=:uid";
        try {
                $db = getConnection();
                $stmt = $db->prepare($sql);
                $stmt->bindParam("uid", $uid);
                $stmt->bindParam("currentScore", $sensor->currentScore);
                $stmt->bindParam("status",$sensor->status);
                date_default_timezone_set('Asia/Saigon');
                $time = date('Y-m-d h:i:s', time());
                $stmt->bindParam("latestCheck", $time);

                $stmt->execute();
                $db = null;
                echo json_encode($sensor);
        } catch(PDOException $e) {
                echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
}
*/



function getConnection() {
	$dbhost="localhost";
	$dbuser="root";
	$dbpass="12345678";
	$dbname="playitcool";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

$app->run();
?>
