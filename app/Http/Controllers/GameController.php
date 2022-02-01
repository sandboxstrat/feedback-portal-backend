<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class GameController extends Controller
{
    
    public function getAllGames()
    {
        $query = DB::select(
            'SELECT 
                games.*,
                SUM(case when feedback.viewed=0 then 1 else 0 end) as unviewed_feedback
            FROM games
            LEFT JOIN feedback ON feedback.game_id=games.id
            GROUP BY games.id;'
        );
        return response()->json($query);
    }

    public function getOneGame($id)
    {
        return response()->json(Game::find($id));
    }

    public function getOneGameByUrl($url)
    {
        return response()->json(Game::where('url','LIKE',$url)->first());
    }

    public function checkGameUrl($url,$id)
    {   
        return response()->json(Game::where('url','LIKE',$url)->where('id','<>',$id)->first());
    }


    public function getOneGameByUrlAndPublic($url)
    {
        return response()->json(Game::where('url','LIKE',$url)->where('feedback_page','=',1)->first());
    }

    public function create(Request $request)
    {
        $this->validate($request,[
            'name'=>'required',
        ]);
        
        $game = Game::create($request->all());

        return response()->json($game, 201);
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
        $game = Game::findOrFail($id);
        $game->update($request->all());

        return response()->json($game, 200);
    }

    public function delete($id)
    {
        Game::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
}