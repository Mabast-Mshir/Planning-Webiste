<?php
require'../config/database.php';
require'../config/Helper.php';

class Logs
{
      public static function find()
      {
            $data=[];
            $user_id=isset($_GET['user_id']) ? $_GET['user_id'] : 0;
            if($user_id!==0){
                  $data=['u_id'=>$user_id];
            }
            $dbmanager = new DBManager();
            $res=$dbmanager->show('logs',$data);
            echo json_encode($res);
      }
}

$logs = new Logs();


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

      $token=isset($_GET['token']) ? $_GET['token'] : 0;
      if($token!=0){
        $logs::find(); 
      }else{
            echo Helper::json(['staus'=>'failed','message'=>'Unauthenticated']);
      }    
}
else{
        echo Helper::json(['staus'=>'failed','message'=>'Unsupported Method, only GET method is supported']);
}
?>