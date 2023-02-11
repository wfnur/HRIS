<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit',10); // defaultnya jadi 10
        $with_responsibilities = $request->input('with_responsibilities',false);
        
        $roles = Role::query();

        if($with_responsibilities)
        {
            $roles->with('responsibilities');
        }

        //powerhuman.com/api/role?id={id}
        if($id)
        {
            $role = $roles->with('responsibilities')->find($id);
             
            if($role)
            {
                return ResponseFormatter::success($role,'Role Found');
            }

            return ResponseFormatter::error('Role Not Found');
        }

        //powerhuman.com/api/role
        if($request->company_id){
            $roles = $roles->where('company_id',$request->company_id);
        }
        

        //powerhuman.com/api/role?name={name}
        if($name)
        {
            $roles->where('name','like','%'.$name.'%');
        }

        
        
        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Role Found'
        );
    }

    public function create(CreateRoleRequest $request)
    {
        try {
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            if(!$role)
            {
                throw new Exception('Role no created');
            }
    
            return ResponseFormatter::success($role,'Role Created');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }

    }

    public function update(UpdateRoleRequest $request,$id)
    {
        
        $path = null;
        try {
            $role = Role::find($id);

            if(!$role){
                throw new Exception('Role not found');
            }

            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);
            
            return ResponseFormatter::success($role,'Role Updated');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }
    }

    public function destroy($id)
    {
        //otomatis jadi pake soft delete
        try {
            $role = Role::find($id);

            if(!$role){
                throw new Exception('Role not found');
            }
            $role->delete();
            return ResponseFormatter::success($role,'Role Deleted');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }
    }
}
