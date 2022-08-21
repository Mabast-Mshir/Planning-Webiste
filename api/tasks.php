<?php
require'../config/database.php';
require'../config/Helper.php';

class Tasks
{
      
      public static function store($request)
      {
            $dbmanager = new DBManager();

            $required_fields=['status_id','title','description','image','due_date','user_id'];
            
            $validated=Helper::validate($request,$required_fields);
            if($validated===true){
                  $user_type=$dbmanager->get_user_type($request->user_id);
                  if($user_type['status']==="success"){
                        if($user_type['user_type']==="product_owner")
                        {
                              $fields=Helper::get_params($request);

                              $labels=[];
                              if(isset($request->label_id))
                              {
                                    $labels=$fields['label_id'];
                              }

                              $unique_id=uniqid();
                              $fields['unique_id']=$unique_id;

                              unset($fields['label_id']);
                              
                              if($dbmanager->save("tasks",$fields)===true)
                              {
                                    if(count($labels)>0)
                                    {
                                          foreach($labels as $label_id)
                                          {
                                                $dbmanager->save("tasks_labels",['label_id'=>$label_id,'task_id'=>$unique_id]);
                                          }
                                    }

                                    $log_data['u_id']=$request->user_id;
                                    $log_data['log']="Created a new task";
                                    $dbmanager->save('logs',$log_data);

                                    echo Helper::json(['staus'=>'success','message'=>"task inserted successfully"]);
                              }else
                              {
                                    echo Helper::json(['staus'=>'failed','message'=>"task not inserted"]);
                              }
                        }else{
                              echo Helper::json(['staus'=>'failed','message'=>"Only product owner can create a task"]);
                        }
                  }else{
                        echo Helper::json(['staus'=>'failed','message'=>$dbmanager->get_user_type($request->user_id)['message']]);
                  }
            }else{
                  echo Helper::json(['staus'=>'failed','message'=>$validated]);
            }
      }

      public static function find()
      {
            $data=[];
            foreach($_GET as $name=>$val){
                  $data[$name]=$val;
            }
            
            $dbmanager = new DBManager();
            $res=$dbmanager->tasks_search($data);
            echo json_encode($res);
      }

      public static function destroy($id,$user_id)
      {
            $dbmanager = new DBManager();
            $delete=$dbmanager->delete("tasks",$id); 
            if($delete['status']==="success"){
                  echo Helper::json($delete);
                  $log_data['u_id']=$user_id;
                  $log_data['log']="task id : ".$id." has been deleted";
                  $dbmanager->save('logs',$log_data); 
            }else{
                  echo Helper::json($delete);
            }
      }

      public static function update($request,$id)
      {
            $required_fields=['status_id','user_id'];
            $validated=Helper::validate($request,$required_fields);
            if($validated===true){
                  $dbmanager = new DBManager();
                  $fields=Helper::get_params($request);
                  $new_status_id=$fields['status_id'];
                  unset($fields['status_id']);

                  if(count($fields)>0){
                        if($dbmanager->update("tasks",$fields,$id)===true){
                              self::update_status($id,$new_status_id,$fields['user_id']);
                        }else
                        {
                              echo Helper::json(['staus'=>'failed','message'=>"task not updated"]);
                        } 
                  }else{
                        self::update_status($id,$new_status_id,$fields['user_id']);
                  }
                  
            }else{
                  echo Helper::json(['staus'=>'failed','message'=>$validated]);
            }
            
      }

      public static function update_status($task_id,$new_status_id,$user_id){
            
            $dbmanager=new DBManager();
            $user=$dbmanager->get_user_type($user_id);
            if($user['status']==="success"){
                  $current_status=$dbmanager->get_current_status(['task_id'=>$task_id]);
                  $new_status=$dbmanager->get_current_status(['status_id'=>$new_status_id]);

                  if($current_status['status']==="success" && $new_status['status']==="success"){
                        $isUpdated=false;
                        $current_status=$current_status['status_title'];
                        $new_status=$new_status['status_title'];

                        //update status
                        $user_type=$user['user_type'];
                        if($user_type==="developer"){
                              if(($current_status==="to-do" && $new_status==="in-progress") || ($current_status==="in-progress" && $new_status==="testing")){
                                    if($dbmanager->update("tasks",['status_id'=>$new_status_id],$task_id)===true){
                                          $isUpdated=true;
                                    }
                              }else{
                                    $isUpdated=false;
                              }                 
                        }elseif($user_type==="tester"){
                              if($current_status==="testing" && $new_status==="dev-review"){
                                    if($dbmanager->update("tasks",['status_id'=>$new_status_id],$task_id)===true){
                                          $isUpdated=true;
                                    }
                              }else{
                                    $isUpdated=false;
                              }
                        }
                        elseif($user_type==="product_owner"){
                              if($dbmanager->update("tasks",['status_id'=>$new_status_id],$task_id)===true){
                                    $isUpdated=true;
                              }
                        }else{
                              echo Helper::json(['staus'=>'failed','message'=>"Unknown user type"]);
                        }

                        if($isUpdated){
                              //saving log
                              $log_data['u_id']=$user_id;
                              $log_data['log']="Status of ask id : ".$task_id." has been updated from ".$current_status." to ".$new_status;
                              $dbmanager->save('logs',$log_data);
                              echo Helper::json(['staus'=>'success','message'=>"task updated successfully"]);
                        }else{
                              echo Helper::json(['staus'=>'failed','message'=>$user_type." can't perform this action."]);
                        }
                  }else{
                        echo Helper::json(['staus'=>'failed','message'=>'Unknow Error']);
                  }
            }else{
                  echo Helper::json(['staus'=>'failed','message'=>$user['message']]);
            }
      }
}

$Task = new Tasks();

//save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      $input=file_get_contents('php://input');
      $Task::store(json_decode($input));

}
//search
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

      $token=isset($_GET['token']) ? $_GET['token'] : 0;
      if($token!=0){
            $Task::find(); 
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
            $Task->update(json_decode($input),$id);
      }else{
            echo Helper::json(['staus'=>'failed','message'=>'Unauthenticated']);
      }
      // $User::update(json_decode($input));
}
//delete
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

      $id=isset(json_decode(file_get_contents('php://input'))->id) ? json_decode(file_get_contents('php://input'))->id : 0;
      $user_id=isset(json_decode(file_get_contents('php://input'))->user_id) ? json_decode(file_get_contents('php://input'))->user_id : 0;
      $token=isset(json_decode(file_get_contents('php://input'))->token) ? json_decode(file_get_contents('php://input'))->token : 0;

      if($id==0){
            echo Helper::json(['staus'=>'failed','message'=>'id is missed']);
      }

      if($user_id==0){
            echo Helper::json(['staus'=>'failed','message'=>'user_id is missed']);
      }

      if($id!=0 && $user_id!=0){
            $dbmanager=new DBManager();
            if($dbmanager->is_exist('user_sessions',['token'=>$token])===true){
                  $Task::destroy($id,$user_id);  
            }else{
                  echo Helper::json(['staus'=>'failed','message'=>'Unauthenticated']);
            }
      }
      
	
}
?>