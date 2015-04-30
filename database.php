<?php

function getDbConnection() {
  $db = new PDO(DB_DRIVER . ":dbname=" . DB_DATABASE . ";host=" . DB_SERVER, DB_USER, DB_PASSWORD);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
  return $db;
}

function searchForKeyword($keyword, $id) {
    $db = getDbConnection();
    $results = array();
    $db->prepare("DROP TABLE temp;")->execute();
    
    $stmt = $db->prepare("CREATE TABLE temp AS SELECT Query,Count FROM `newq` WHERE Query LIKE ? ORDER BY count desc limit 50");

    $keyword1 = $keyword . '%';
    $stmt->bindParam(1, $keyword1, PDO::PARAM_STR, 100);
    $stmt->execute();
    
    $stmt = $db ->prepare("INSERT INTO `temp`(Query) SELECT userlike FROM `likes` WHERE userlike LIKE ? AND id LIKE ?");
    $keyword1 = $keyword . '%';
    $stmt->bindParam(1, $keyword1, PDO::PARAM_STR, 100);
    $stmt->bindParam(2, $id, PDO::PARAM_STR, 100);
    
    $stmt->execute();
    
    
    $stmt = $db->prepare("SELECT Query FROM `pastq` WHERE time > DATE_SUB(now(), INTERVAL 1 HOUR)");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach($results as $pastquery) {
        $stmt = $db->prepare("Update temp t cross join ( select avg(count) acnt from temp ) u SET t.count=t.count+acnt*4 WHERE t.Query LIKE ?");
        $pastquery = '%'.$pastquery;
        $stmt->bindParam(1, $pastquery, PDO::PARAM_STR, 100);
        $stmt->execute();
        
        $stmt = $db->prepare("Update temp t cross join ( select avg(count) acnt from temp ) u SET t.count=t.count+acnt*2 WHERE t.Query LIKE ?");
        $pastquery = '%'. $pastquery .' %';
        $stmt->bindParam(1, $pastquery, PDO::PARAM_STR, 100);
        $stmt->execute();
        
        $stmt = $db->prepare("Update temp t cross join ( select avg(count) acnt from temp ) u SET t.count=t.count+acnt WHERE t.Query LIKE ?");
        $pastquery = '%'. $pastquery .'%';
        $stmt->bindParam(1, $pastquery, PDO::PARAM_STR, 100);
        $stmt->execute();
    }
    
    $stmt = $db->prepare("SELECT Query FROM `trending` ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach($results as $trend) {
        
        if(0 === stripos($trend, $keyword))
        {
            $stmt = $db->prepare("insert into temp values ( ? , 5000)");
            $stmt->bindParam(1, $trend, PDO::PARAM_STR, 100);
            $stmt->execute();
        }
    }
    
    $stmt = $db->prepare("SELECT Query FROM `temp` ORDER BY count desc limit 10");
    $isQueryOk = $stmt->execute();
    if ($isQueryOk) {
      $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
      trigger_error('Error executing statement.', E_USER_ERROR);
    }

    $db = null; 

    return $results;
}
function insertQuery($keyword, $id) {
    $db = getDbConnection();
    $stmt = $db->prepare("INSERT INTO `test`.`pastq` (`UserID`, `Query`, `Time`) VALUES (?, ?, now()) ON DUPLICATE KEY UPDATE Time=now();");
    $stmt->bindParam(1, $id, PDO::PARAM_STR, 100);    
    $stmt->bindParam(2, $keyword, PDO::PARAM_STR, 100);
    $stmt->execute();
    
    $stmt = $db->prepare("INSERT INTO `newq` VALUES (?, 100) ON DUPLICATE KEY UPDATE Count=Count+50;");
    $stmt->bindParam(1, $keyword, PDO::PARAM_STR, 100);
    $stmt->execute();
    
    $db = null; 
}


function fetchTrends() {
    $db = getDbConnection();
    $db->prepare("TRUNCATE TABLE trending;")->execute();
    
    $json = file_get_contents("http://hawttrends.appspot.com/api/terms/");
    $obj = json_decode($json,true);
    $array = $obj['3'];
    
    foreach($array as $trend){
        $stmt = $db->prepare("INSERT INTO `trending` VALUES ( ? ) ON DUPLICATE KEY UPDATE Query = Query");
        $stmt->bindParam(1, $trend, PDO::PARAM_STR, 100);
        $stmt->execute();
    }
    
    $db = null; 

    return $array;}

function saveLikes($likes, $id) {
    $db = getDbConnection();
    foreach($likes as $like) {
    $stmt = $db->prepare("INSERT INTO `likes` VALUES ( ?, ?) ON DUPLICATE KEY UPDATE id = id");
    $stmt->bindParam(1, $id, PDO::PARAM_STR, 100);
    $stmt->bindParam(2, $like, PDO::PARAM_STR, 100);
    $stmt->execute();
    }
}
?>