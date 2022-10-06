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
                DATE_FORMAT(DATE(created_at), "%m/%d/%Y") as date,
                COUNT(id) as count
            FROM feedback
            GROUP BY date
            ORDER BY date ASC;'
        );
        return response()->json($query);
    }

    public function getFeedbackById($id)
    {
        return response()->json(Feedback::find($id));
    }

    public function getFeedbackByGame($gameId){
        return response()->json(Feedback::
            select('feedback.*',DB::raw('CONCAT(DATE_FORMAT(DATE(created_at), "%m/%d/%Y")," ",TIME_FORMAT(TIME(created_at), "%H:%i:%s")) as datetime'))
            ->where('game_id','=',$gameId)
            ->orderBy('datetime', 'desc')
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
        $feedback -> update($request->all());

        //Pulls updated data with properly formatted datetime
        $feedbackResponse = Feedback::
        select('feedback.*',DB::raw('CONCAT(DATE_FORMAT(DATE(created_at), "%m/%d/%Y")," ",TIME_FORMAT(TIME(created_at), "%H:%i:%s")) as datetime'))
        ->where('id','=',$id)
        ->limit(1)
        ->get();

        return response()->json($feedbackResponse[0], 200);
    }

    public function delete($id)
    {
        Game::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }

    public function createCsv(Request $request, $filename="export.csv"){
        
        

        $data = json_decode($request->getContent(),true);

        // output headers so that the file is downloaded rather than displayed
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ];
        
        //callback function to generate csv
        $callback = function() use ($data){

            // create a file pointer connected to the output stream
            $output = fopen('php://output', 'w');

            //Adds Byte Order Mark
            fwrite($output, "\xEF\xBB\xBF");

            if(!empty($data['header'])){
                fputcsv($output, $data['header']);
            }

            // loop over the rows, outputting them
            foreach($data['data'] as $row){
                if(is_string($row)){
                    $row=[$row];
                }
                fputcsv($output, $row);
            }

            fclose($output);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}