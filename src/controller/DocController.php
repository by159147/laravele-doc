<?php


namespace Faed\Doc\controller;

use Faed\Doc\models\Api;
use Faed\Doc\models\Group;
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


        if (!empty($post['apis'])){
            collect($post['apis'])->groupBy('group')->map(function ($apis,$group) use ($project){
                $group = Group::create(['name'=>$group,'project_id'=>$project->id]);
                $apis->map(function ($api) use ($group){
                   $apiModel = Api::create([
                        'group_id'=>$group->id,
                        'name'=>@$api['name'],
                        'path'=>@$api['path'],
                        'method'=>@$api['method'],
                    ]);
                    $this->setParams($apiModel->id,@$api['u']?:[],1);
                    $this->setParams($apiModel->id,@$api['q']?:[],2);
                    $this->setParams($apiModel->id,@$api['b']?:[],3);
                });
            });
        }
    }

    public function setParams($apiId,$params,$type)
    {

        foreach ($params as $param){
            Param::create([
                'api_id'=>$apiId,
                'type'=>@$type,
                'name'=>@$param['name'],
                'is_must'=>@$param['is_must'],
                'desc'=>@$param['desc'],
                'example'=>@$param['example'],
            ]);
        }
    }

    public function delApiParams($id)
    {
        $groupIds=Group::where('project_id',$id)->pluck('id');
        $apiIds = Api::whereIn('group_id',$groupIds)->pluck('id');
        Param::whereIn('api_id',$apiIds)->delete();
        Api::destroy($apiIds);
        Group::destroy($groupIds);
    }


    public function index($id,$groupId = 0)
    {
        $project = Project::find($id);
        $groups = Group::where('project_id',$project->id)->get();
        $groupId = $groupId?:Group::where('project_id',$project->id)->min('id');
        $apis = Api::with(['params'])->where('group_id',$groupId)->get();

        return view('doc::index',compact('apis','project','groups'));
    }


    public function nav()
    {
        $config = Project::get();

        return view('doc::nav',compact('config'));
    }
}
