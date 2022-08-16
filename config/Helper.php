<?php

class Helper
{
      public static function validate($request,$fields)
      {
           foreach($fields as $field){
             if((!isset($request->$field) || $request->$field=="")){  
                  return $field.' is mendatory';
             }
           }
           return true;
      }

      public static function get_params($request){
            $fields=[];
            foreach($request as $key=>$field){
                  $fields[$key]=$field;
            }
            return $fields;
      }

      public static function json($array){
            return json_encode($array,JSON_PRETTY_PRINT);
      }

}

?>