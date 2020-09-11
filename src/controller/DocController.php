<?php


namespace Faed\Doc\controller;

use Faed\Doc\models\Api;
use Faed\Doc\models\Param;
use Faed\Doc\models\Project;
use Illuminate\Http\Request;


class DocController
{
    /**
     * 保存apis
     * @param Request $request
     */
    public function save(Request $request)
    {
        $post = $request->input();
        $project = Project::updateOrcreate($post['project']);

        $this->delApiParams($project->id);
        foreach ($post['apis'] as $value){
            $api = Api::create([
                'project_id'=>$project->id,
                'name'=>@$value['name'],
                'path'=>@$value['path'],
                'method'=>@$value['method'],
            ]);
            $this->setParams($api->id,@$value['q']?:[],1);
            $this->setParams($api->id,@$value['u']?:[],2);
            $this->setParams($api->id,@$value['b']?:[],3);
        }
    }

    public function setParams($apiId,$params,$type)
    {
        foreach ($params as $param){
            Param::create([
                'api_id'=>$apiId,
                'type'=>$type,
                'name'=>$param['name'],
                'is_must'=>$param['is_must'],
                'desc'=>$param['desc'],
            ]);
        }
    }

    public function delApiParams($id)
    {
        $ids = Api::where('project_id',$id)->pluck('id');
        Param::whereIn('api_id',Api::where('project_id',$ids)->pluck('id'))->delete();
        Api::destroy($ids);
    }


    public function index($id)
    {
        $project = Project::find($id);
        $apis = Api::with(['params'])->where('project_id',$id)->get();
        return view('doc::index',compact('apis','project'));
    }
}
