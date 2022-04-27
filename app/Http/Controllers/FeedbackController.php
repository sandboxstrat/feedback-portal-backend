<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Log;

class FeedbackController extends Controller
{
    
    public function getAllFeedback()
    {
        return response()->json(Feedback::all());
    }

    public function getLatestFeedback(){
        $query = DB::select(
            'SELECT 
                feedback.*,
                games.name as game_name,
                games.id as game_id,
                CONCAT(DATE_FORMAT(DATE(feedback.created_at), "%M %d, %Y")," ",TIME_FORMAT(TIME(feedback.created_at), "%H:%i:%s")) as datetime
            FROM feedback
            LEFT JOIN games ON feedback.game_id=games.id
            ORDER BY created_at DESC;'
        );
        return response()->json($query);
    }

    public function getFeedbackCountByDate(){
        $query = DB::select(
            'SELECT 
                DATE_FORMAT(DATE(created_at), "%M %d, %Y") as date,
                COUNT(id) as count
            FROM feedback
            GROUP BY date;'
        );
        return response()->json($query);
    }

    public function getFeedbackById($id)
    {
        return response()->json(Feedback::find($id));
    }

    public function getFeedbackByGame($gameId){
        return response()->json(Feedback::
            select('feedback.*',DB::raw('CONCAT(DATE_FORMAT(DATE(created_at), "%M %d, %Y")," ",TIME_FORMAT(TIME(created_at), "%H:%i:%s")) as datetime'))
            ->where('game_id','=',$gameId)
            ->get());
    }

    public function create(Request $request)
    {
        $this->validate($request,[
            'user_id'=>'required',
            'user_name'=>'required',
            'feedback'=>'required'
        ]);
        
        $feedback = Feedback::create($request->all()+['ip_address'=>$request->ip()]);

        return response()->json($feedback, 201);
    }

    public function uploadImage(Request $request){
        if($request->hasFile('photo')){
            
            $file = $request->file('photo');

            //check mime types to confirm image
            $allowedMimeTypes = ['png','jpeg','svg+xml'];
            $extention = $file->getClientOriginalExtension();
            $fullMimeType = $file->getMimeType();
            $mimetype = explode("/",$fullMimeType);
            $check = in_array($mimetype[1], $allowedMimeTypes);
            if($check){

                //adds timestamp to name to ensure no dupes
                $name = time()."-".$file->getClientOriginalName();

                //replace non-alphanumeric characters in name
                $name = pathinfo($name, PATHINFO_FILENAME);
                $name = preg_replace("/[^A-Za-z0-9 \-]/", '', $name);
                $name = str_replace(" ","-",$name);

                //move to public folder
                $file->move($_SERVER["DOCUMENT_ROOT"].'\..\..\public\images\uploads',$name.".".$extention);
                $response = ['name'=>$name.".".$extention];
                return response()->json($response,200);
            }else{
                $response = ['Error'=>'400 Bad Request'];
                return response()->json($response,400);
            }
        }
    }

    public function update($id, Request $request)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update($request->all());

        return response()->json($feedback, 200);
    }

    public function delete($id)
    {
        Game::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
}