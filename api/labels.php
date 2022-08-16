<?php
require'../config/database.php';
require'../config/Helper.php';

class Labels
{
      
      public static function store($request)
      {
            $dbmanager = new DBManager();

            $required_fields=['label'];
            $validated=Helper::validate($request,$required_fields);
            if($validated===true){

                  $fields=Helper::get_params($request);
                  if($dbmanager->save("labels",$fields)===true){
                        echo Helper::json(['staus'=>'success','message'=>"label inserted successfully"]);
                  }else{
                        echo Helper::json(['staus'=>'failed','message'=>"label not inserted"]);
                  }
            }else{
                  echo Helper::json(['staus'=>'failed','message'=>$validated]);
            }
      }

      public function find($id=0)
      {
            $data=[];
            if($id!==0){
                  $data=['id'=>$id];
            }
            $dbmanager = new DBManager();
            $res=$dbmanager->show('boards',$data);
            echo json_encode($res);
      }

      public function destroy($id)
      {
            $dbmanager = new DBManager();
            echo Helper::json($dbmanager->delete("boards",$id));    
      }

      public function update($request,$id)
      {
            $dbmanager = new DBManager();
            $fields=Helper::get_params($request);
            if($dbmanager->update("boards",$fields,$id)===true){
                  echo Helper::json(['staus'=>'success','message'=>"board updated successfully"]);
            }else{
              echo Helper::json(['staus'=>'failed','message'=>"board not updated"]);

            }
            
      }
}

$Label = new Labels();

//save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $input=file_get_contents('php://input');
      $Label::store(json_decode($input));

}
//search
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

      $token=isset($_GET['token']) ? $_GET['token'] : 0;
      $id=isset($_GET['id']) ? $_GET['id'] : 0;
      if($token!=0){
            if($id==0){
                  $Label::find(); 
            }else{
                  $Label::find($_GET['id']); 
            }
      }else{
            echo Helper::json(['staus'=>'failed','message'=>'Unauthenticated']);
      }
      
      
      
}
//update
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
      $id=isset($_GET['id']) ? $_GET['id'] : 0;
      $token=isset($_GET['token']) ? $_GET['token'] : 0;

      $input=file_get_contents('php://input');
      if($token!=0){
            $Label->update(json_decode($input),$id);
      }else{
            echo Helper::json(['staus'=>'failed','message'=>'Unauthenticated']);
      }
      // $User::update(json_decode($input));
}
//delete
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

      $id=isset(json_decode(file_get_contents('php://input'))->id) ? json_decode(file_get_contents('php://input'))->id : 0;
      $token=isset(json_decode(file_get_contents('php://input'))->token) ? json_decode(file_get_contents('php://input'))->token : 0;

      if($id==0){
            echo Helper::json(['staus'=>'failed','message'=>'id is missed']);
      }

      $dbmanager=new DBManager();
      if($dbmanager->is_exist('user_sessions',['token'=>$token])===true && $id!=0){
            $Label::destroy($id);  
      }else{
            echo Helper::json(['staus'=>'failed','message'=>'Unauthenticated']);
      }
	
}
?>