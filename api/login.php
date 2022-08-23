<?php
require'../config/database.php';
require'../config/Helper.php';

class Login
{
      
      public static function login()
      {
            $username=isset($_GET['username']) ? $_GET['username'] : "";
            $password=isset($_GET['password']) ? $_GET['password'] : "";

            if($username==""){
                  echo "username is required";
            }

            if($password==""){
                  echo "password is required";
            }
            
            if($username !="" && $password!=""){
                  $dbmanager = new DBManager();
                  $user=$dbmanager->authenticate($username,$password);
                  $token=bin2hex(random_bytes(16));
                 
                  if(count($user)>0){
                        $data['u_id']=$user[0]['id'];
                        $data['token']=$token;
                        if ($dbmanager->is_exist('user_sessions',['u_id'=>$user[0]['id']])){
                              $u=$dbmanager->show('user_sessions',['u_id'=>$user[0]['id']]);
                              echo Helper::json(['staus'=>'success','message'=>'user already logged in','token'=>$u[0]['token']]);
                        }
                        else{
                              if ($dbmanager->save('user_sessions',$data)===true){
                              echo Helper::json(['staus'=>'success','token'=>$token]);
                              }
                        }
                        }
                        else{
                              echo Helper::json(['staus'=>'failed','message'=>'Invalid credentials']);
                  }     
            
            }
      }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $login = new Login();
      $login->login();
}else{
      echo Helper::json(['staus'=>'failed','message'=>"Route method not supported"]);
}


?>
