<?php

    router::set('register/register_general',function(){
        require_once('./controllers/register.php');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = json_decode(file_get_contents("php://input",true));
            $id = $data->id;              /* id card */
            
            if (!empty($id)) {
                
                register_general($id);
                
            }else{
                
                echo json_encode(array('message' => 'ID ห้ามเป็นค่าว่าง','status' => FALSE)); 
                
            }

        }else{
            echo json_encode(array('message' => 'Invalid Method'.' '.$_SERVER['REQUEST_METHOD'],'status' => FALSE)); 
        }
    });

    router::set('register/register_appointment',function(){
        require_once('./controllers/register.php');
        require_once('./controllers/misu.php');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = json_decode(file_get_contents("php://input",true));
            $ser_fu = $data->ser;              /* ser fu */
            
            if (!empty($ser_fu)) {
                
                register_appointment($ser_fu);
                
            }else{
                
                echo json_encode(array('message' => 'ser ห้ามเป็นค่าว่าง','status' => FALSE)); 
                
            }
          
        }else{
            echo json_encode(array('message' => 'Invalid Method'.' '.$_SERVER['REQUEST_METHOD'],'status' => FALSE)); 
        }
    });

?>