<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class OptionController extends Controller
{

    public function getOption($id)
    {
        return response()->json(Option::find($id));
    }

    public function getOptionsByGameId($gameId)
    {
        $options = DB::select('select * from options where game_id LIKE ?', [$gameId]);
        $options = json_decode(json_encode($options), true);
        $optionTree = $this->buildTree($options);
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

    public function create(Request $request)
    {
        $this->validate($request,[
            'text'=>'required',
        ]);
        
        $option = Option::create($request->all());

        return response()->json($option, 201);
    }

    public function update($id, Request $request)
    {
        $option = Option::findOrFail($id);
        $option->update($request->all());

        return response()->json($option, 200);
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