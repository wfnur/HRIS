<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Http\Requests\UpdateResponsibilityRequest;
use App\Models\Responsibility;
use Exception;
use Illuminate\Http\Request;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit',10); // defaultnya jadi 10
        
        $Responsibilities = Responsibility::query();

        //powerhuman.com/api/Responsibility?id={id}
        if($id)
        {
            $Responsibility = $Responsibilities->find($id);
             
            if($Responsibility)
            {
                return ResponseFormatter::success($Responsibility,'Responsibility Found');
            }

            return ResponseFormatter::error('Responsibility Not Found');
        }

        //powerhuman.com/api/role
        if($request->role_id){
            $Responsibilities = $Responsibilities->where('role_id',$request->role_id);
        }
        

        //powerhuman.com/api/role?name={name}
        if($name)
        {
            $Responsibilities->where('name','like','%'.$name.'%');
        }
        
        return ResponseFormatter::success(
            $Responsibilities->paginate($limit),
            'Responsibility Found'
        );
    }

    public function create(CreateResponsibilityRequest $request)
    {
        try {
            $Responsibilities = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            if(!$Responsibilities)
            {
                throw new Exception('Responsibility no created');
            }
    
            return ResponseFormatter::success($Responsibilities,'Responsibility Created');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }

    }

    public function update(UpdateResponsibilityRequest $request,$id)
    {
        
        try {
            $Responsibilities = Responsibility::find($id);

            if(!$Responsibilities){
                throw new Exception('Responsibility not found');
            }

            $Responsibilities->update([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);
            
            return ResponseFormatter::success($Responsibilities,'Responsibility Updated');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }
    }

    public function destroy($id)
    {
        //otomatis jadi pake soft delete
        try {
            $Responsibilities = Responsibility::find($id);

            if(!$Responsibilities){
                throw new Exception('Responsibility not found');
            }
            $Responsibilities->delete();
            return ResponseFormatter::success($Responsibilities,'Responsibility Deleted');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }
    }
}
