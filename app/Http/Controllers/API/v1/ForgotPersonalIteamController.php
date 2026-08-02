<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\v1\GcmController;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use DB;
class ForgotPersonalIteamController extends Controller
{

    public function __construct()
    {
        $this->limit=20;   
    }
  
    public function register(Request $request)
    {
   
        $id_requete = $request->get('id_ride');
        $id_driver = $request->get('id_driver');
        $user_name = $request->get('user_name');
        $from_id = $request->get('from_id');
        $description_text = $request->get('description_text');
       
        $title=str_replace("'","\'","Customer Forgot item");
        $msg=str_replace("'","\'",$user_name." forgot personal items like ".$description_text.". Please contact ".$user_name." ASAP.");
        
        $tab[] = array();
        $tab = explode("\\",$msg);
        $msg_ = "";
        for($i=0; $i<count($tab); $i++){
            $msg_ = $msg_."".$tab[$i];
        }

        $message=array("body"=>$msg_,"title"=>$title,"sound"=>"mySound","tag"=>"forgotitem");
        $fcm_token = DB::table('tj_conducteur')->where('fcm_id','!=','')->where('id','=',$id_driver)->value('fcm_id');
        if (!empty($fcm_token)) {
            GcmController::sendNotification($fcm_token, $message);

            $response['msg']['etat'] = 1;
            $date_heure = date('Y-m-d H:i:s');
            $from_id=$request->get('from_id');
            $to_id=$request->get('id_driver');

            $query = DB::insert("insert into tj_notification(titre,message,statut,creer,to_id,from_id,type,modifier)
            values('".$title."','".$msg."','yes','".$date_heure."','".$to_id."','".$from_id."','forgotitem','".$date_heure."')");
        }else{
            $response['msg']['etat'] = 2; 
        }
    
        return response()->json($response);
    }
}
