<?php
class DBManager
{

    private $host = "localhost";
    private $port = 3306;
    private $user = 'root';
    private $pass = '';
    private $db = 'task_ms';
    private $dbh;

    public function __construct()
    {
        try {
            $this->dbh = new PDO("mysql:host={$this->host};dbname={$this->db}", $this->user, $this->pass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"));
        } catch (PDOException $e) {
            // die("DB-F");
        }
    }

    public function save($table_name, $fields)
    {
        if ($this->dbh) 
        {
            //build the query
            $f="";$v="";$i=0;
            $query="INSERT INTO ".$table_name."(";
            foreach($fields as $key=>$field){
                $i++;
                $seperator=",";
                if($i==count($fields)){
                    $seperator="";
                }

                if(gettype($field)=="string")
                {
                    $v .= '"'.$field.'"'.$seperator;
                }else{
                    $v .= $field.$seperator;
                }

                $f .=$key.$seperator;

            }
            $query .=$f.") values(".$v.")";
            //execute query
            $prepare = $this->dbh->prepare($query);
            $prepare->execute();
            if ($prepare->rowCount() > 0) {
                return true;
            }
            return false;
        } else {
            error_log("logging->insert into LOG table failed Database not connected");
            return false;
        }
    }

    public function update($table_name, $fields,$id)
    {
        if ($this->dbh) 
        {
            if( ! $this->is_exist($table_name,['id'=>$id])){
                return false;
            }else{
                //build the query
                $f="";$v="";$i=0;
                $query="update ".$table_name." set ";
                foreach($fields as $key=>$field){
                    $i++;
                    $seperator=",";
                    if($i==count($fields)){
                        $seperator="";
                    }

                    if(gettype($field)=="string")
                    {
                        $query .=$key.'="'.$field.'"'.$seperator;
                    }else{
                        $query .= $key."=".$field.$seperator;
                    }

                }
                $query .=" where id=".$id;
                //execute query

                $prepare = $this->dbh->prepare($query);
                
                if ($prepare->execute()) {
                    return true;
                }
                return false;
            }
        } else {
            error_log("logging->update table failed Database not connected");
            return false;
        }
        
    }

    public function delete($table_name,$id)
    {
        if ($this->dbh) 
        {
            if( ! $this->is_exist($table_name,['id'=>$id])){
                return ['status'=>'failed','message'=>"data not exist"];
            }else{
                $sth = $this->dbh->prepare("SELECT * FROM ".$table_name." where id=:id");
                $sth->bindParam(":id", $id);
                if($sth->execute()) 
                {
                    if($sth->rowCount() > 0) 
                    {
                        $query="delete from ".$table_name.' where id='.$id;
                        $prepare = $this->dbh->prepare($query);
                            if($prepare->execute()){
                                return ['status'=>'success','message'=>"data deleted"];
                            }
                            return ['status'=>'failed','message'=>"data not deleted"]; 
                    } 
                    else 
                    {
                        return ['status'=>'failed','message'=>"data not exist"];
                    }

                }
            }
        }else{
            error_log("logging->update table failed Database not connected");
            return false;
        }
          
    }

    public function show($table_name,$fields=[])
    {   
        $i=0;$operator=" and ";
        $query="select * from ".$table_name;
        if(count($fields)>0){
            $query .= ' where ';
            foreach($fields as $key=>$field){
                $i++;
                if($i==count($fields)){
                    $operator="";
                }
                if(gettype($field)=="string")
                {
                    $query .=$key."=\"".$field."\"".$operator;
                }else{
                    $query .=$key."=".$field.$operator;
                }
            }
        }
        
        $sth = $this->dbh->prepare($query);
        if($sth->execute()) 
        {
            if($sth->rowCount() > 0) 
            {
                 $result = $sth->fetchAll(PDO::FETCH_ASSOC);  
            } 
            else 
            {
                return [];
            }
            
            return $result;

        } else 
        {
            echo 'there is an error in the query';
        }
    }

    public function tasks_search($fields=[])
    {   
        $i=0;$operator=" and ";
        $query="select t.id,s.title as status,t.title,t.description,t.image,t.due_date,u.username,t.created_at from tasks t
        join status s on s.id=status_id
        join users u on u.id=t.user_id
        ";
        //filters
        $data=[];
        if(isset($fields['task_id'])){
            $data['t.id']=$fields['task_id'];
        }

        if(isset($fields['status_id'])){
            $data['t.status_id']=$fields['status_id'];
        }

        if(count($data)>0){
            $query .= ' where ';
            foreach($data as $key=>$field){
                $i++;
                if($i==count($data)){
                    $operator="";
                }
                if(gettype($field)=="string")
                {
                    $query .=$key."=\"".$field."\"".$operator;
                }else{
                    $query .=$key."=".$field.$operator;
                }
            }
        }
        
        //order by        
        $order_by="title";$order_type="asc";
        if(isset($fields['order_by']) && $fields['order_by']!==""){
            
            if($fields['order_by']==="created_at" || $fields['order_by']==="title"){
                $order_by=$fields['order_by'];
            }
        }

        if(isset($fields['order_type']) && $fields['order_type']!==""){
            
            if($fields['order_type']==="desc"){
                $order_type=$fields['order_type'];
            }
        }

        $query.="order by ".$order_by." ".$order_type;
        
        $sth = $this->dbh->prepare($query);
        if($sth->execute()) 
        {
            if($sth->rowCount() > 0) 
            {
                 $result = $sth->fetchAll(PDO::FETCH_ASSOC);  
            } 
            else 
            {
                return [];
            }
            
            return $result;

        } else 
        {
            echo 'there is an error in the query';
        }
    }

    public function is_exist($table_name,$fields=[])
    {   
        $i=0;$operator=" and ";
        $query="select * from ".$table_name;
        if(count($fields)>0){
            $query .= ' where ';
            foreach($fields as $key=>$field){
                $i++;
                if($i==count($fields)){
                    $operator="";
                }
                if(gettype($field)=="string")
                {
                    $query .=$key."=\"".$field."\"".$operator;
                }else{
                    $query .=$key."=".$field.$operator;
                }
            }
        }
        
        $sth = $this->dbh->prepare($query);
        if($sth->execute()) 
        {
            if($sth->rowCount() > 0) 
            {
                return true;
            } 
            else 
            {
                return false;
            }

        } else 
        {
            echo 'there is an error in the query';
            return false;
        }
    }

    public function Authenticate($username)
    {   
        $sth = $this->dbh->prepare("SELECT * FROM users where username=:username and password=:password;");
        $sth->bindParam(":username", $username);
        $sth->bindParam(":password", $password);
        if($sth->execute()) 
        {
            $result=[];
            if($sth->rowCount() > 0) 
            {
                $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            } 
            else 
            {
                return $result;
            }

            return $result;

        } else 
        {
            echo 'there is an error in the query';
        }
    }

    public function get_user_type($user_id)
    {   
        if($this->is_exist('users',['id'=>$user_id])===true)
        {
            $sth = $this->dbh->prepare("SELECT user_type FROM users where id=:id;");
            $sth->bindParam(":id", $user_id);
            if($sth->execute()) 
            {
                $result=[];
                if($sth->rowCount() > 0) 
                {
                    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
                } 

                return ['status'=>'success','user_type'=>$result[0]['user_type']];

            } else 
            {
                echo 'there is an error in the query';
            }
        }else{
            return ['status'=>'failed','message'=>'User not exist!'];
        }
        
    }

    public function get_current_status($selector)
    {   
        $query="";$id=0;
        if(isset($selector['task_id'])){
            $id=$selector['task_id'];
            $query="SELECT title FROM status where id=(select status_id from tasks where id=:id)";
        }else{
            $id=$selector['status_id'];
            $query="SELECT title FROM status where id=:id";
        }

        $sth = $this->dbh->prepare($query);
        
        $sth->bindParam(":id", $id);
        if($sth->execute()) 
        {
            $result=[];
            if($sth->rowCount() > 0) 
            {
                $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            } 

            return ['status'=>'success','status_title'=>$result[0]['title']];

        } else 
        {
            echo 'there is an error in the query';
        }
    }

    public function __destruct()
    {
        error_log("Closeing database");
        $this->dbh = null;
    }
}

?>
