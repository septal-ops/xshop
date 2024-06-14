<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\XController;
use App\Http\Requests\GroupSaveRequest;
use App\Models\Access;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Helper;
use function App\Helpers\hasCreateRoute;

class GroupController extends XController
{

    // protected  $_MODEL_ = Group::class;
    // protected  $SAVE_REQUEST = GroupSaveRequest::class;

    protected $cols = ['name','subtitle','parent_id'];
    protected $extra_cols = ['id','slug','image'];

    protected $searchable = ['name','subtitle','description'];

    protected $listView = 'admin.groups.group-list';
    protected $formView = 'admin.groups.group-form';


    protected $buttons = [
        'edit' =>
            ['title' => "Edit", 'class' => 'btn-outline-primary', 'icon' => 'ri-edit-2-line'],
        'show' =>
            ['title' => "Detail", 'class' => 'btn-outline-light', 'icon' => 'ri-eye-line'],
        'destroy' =>
            ['title' => "Remove", 'class' => 'btn-outline-danger delete-confirm', 'icon' => 'ri-close-line'],
    ];


    public function __construct()
    {
        parent::__construct(Group::class, GroupSaveRequest::class);
    }

    /**
     * @param $group Group
     * @param $request  GroupSaveRequest
     * @return Group
     */
    public function save($group, $request)
    {

        $group->name = $request->input('name');
        $group->subtitle = $request->input('subtitle');
        $group->description = $request->input('description');
        $group->parent_id = $request->input('parent_id');
        $group->slug = $this->getSlug($group);
        if ($request->has('image')){
            $group->image = $this->storeFile('image',$group, 'groups');
        }
        if ($request->has('bg')){
            $group->bg = $this->storeFile('bg',$group, 'groups');
        }
        $group->save();
        return $group;

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $cats = Group::all();
        return view($this->formView,compact('cats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $item)
    {
        //
        $cats = Group::all();
        return view($this->formView, compact('item','cats'));
    }

    public function bulk(Request $request)
    {

//        dd($request->all());
        $data = explode('.', $request->input('action'));
        $action = $data[0];
        $ids = $request->input('id');
        switch ($action) {
            case 'delete':
                $msg = __(':COUNT items deleted successfully', ['COUNT' => count($ids)]);
                $this->_MODEL_::destroy($ids);
                break;
            /**restore*/
            case 'restore':
                $msg = __(':COUNT items restored successfully', ['COUNT' => count($ids)]);
                foreach ($ids as $id) {
                    $this->_MODEL_::withTrashed()->find($id)->restore();
                }
                break;
            /*restore**/
            default:
                $msg = __('Unknown bulk action : :ACTION', ["ACTION" => $action]);
        }

        return $this->do_bulk($msg, $action, $ids);
    }

    public function destroy(Group $item)
    {
        return parent::delete($item);
    }


    public function update(Request $request, Group $item)
    {
        return $this->bringUp($request, $item);
    }

    /**restore*/
    public function restore($item)
    {
        return parent::restoreing(Group::withTrashed()->where('id', $item)->first());
    }
    /*restore**/
}
