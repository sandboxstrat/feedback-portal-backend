<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class OptionController extends Controller
{

    public function getOption($id){
        return response()->json(Option::find($id));
    }

    public function getOptionsByGameId($gameId){
        $options = DB::select('select * from options where game_id LIKE ?', [$gameId]);
        $options = json_decode(json_encode($options), true);
        $optionTree = $this->buildTree($options);
        return json_encode($optionTree);
    }

    public function getOptionsByGameIdPublic($gameId){
        $options = DB::select('select * from options where game_id LIKE ?', [$gameId]);
        $options = json_decode(json_encode($options), true);
        $optionTree = $this->buildPublicTree($options);
        return json_encode($optionTree);
    }

    protected function buildTree(array $elements, $parentId = 0) {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
    
        return $branch;
    }

    protected function buildPublicTree(array $elements){

        $elementsById = [];
        $elementsByText = [];
        foreach($elements as $element){
            $elementsById[$element['id']]=$element;
        }

        foreach($elementsById as $element){
            if($element['parent']!=null){
                $elementsById[$element['parent']]['children'][]=$element['uri'];
            }else{
                $elementsByText['topLevelOptions']['children'][]=$element['uri'];
            }
        }

        foreach($elementsById as $element){
            $elementsByText[$element['uri']]=$element;
        }

        return $elementsByText;
    }

    public function create(Request $request)
    {
        $this->validate($request,[
            'text'=>'required',
        ]);
        
        $uri = preg_replace("/[^A-Z-a-z0-9 ]/", '', $request['text']);
        $uri = strtolower(str_replace(" ","-",$uri));
        $request->replace(['uri'=>$uri]);

        $optionCheck = $options = DB::select('select * from options where game_id LIKE ? AND uri LIKE ?', [$request['game_id'],$request['uri']]);
        if(empty($optionCheck)){

            $option = Option::create($request->all());
            return response()->json($option, 201);
        }else{
            $error=[
                'error'=>"400 Bad Request",
                'error_message'=>"There's another option for this game with a similar name. Please change and try again."
            ];

            return response()->json($error, 400);

        }
     
        
    }

    public function update($id, Request $request){

        //add uri
        $uri = preg_replace("/[^A-Z-a-z0-9 ]/", '', $request['text']);
        $uri = strtolower(str_replace(" ","-",$uri));
        $request->replace(['uri'=>$uri]);

        //check for dupe text
        $optionCheck = $options = DB::select('select * from options where game_id LIKE ? AND uri LIKE ? AND id NOT LIKE ?', [$request['game_id'],$request['uri'],$id]);
        
        if(empty($optionCheck)){

            //update option
            $option = Option::findOrFail($id);
            $option->update($request->all());
            return response()->json($option, 200);
        }else{
            $error=[
                'error'=>"400 Bad Request",
                'error_message'=>"There's another option for this game with a similar name. Please change and try again."
            ];

            return response()->json($error, 400);

        }

        
    }

    public function delete($id)
    {
        $options = DB::select('select * from options where parent LIKE ?', [$id]);
        $options = json_decode(json_encode($options), true);
        if(!empty($options)){
            foreach($options as $option){
                $this->delete($option['id']);
            }
        }
        Option::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
}