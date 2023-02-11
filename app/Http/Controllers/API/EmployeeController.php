<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $email = $request->input('email');
        $gander = $request->input('gander');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');
        $limit = $request->input('limit',10); // defaultnya jadi 10
        
        $employees = Employee::query()->with(['role','team']);

        //powerhuman.com/api/employee?id={id}
        if($id)
        {
            $employee = $employees->with(['team','role'])->find($id);
             
            if($employee)
            {
                return ResponseFormatter::success($employee,'Employee Found');
            }

            return ResponseFormatter::error('Employee Not Found');
        }

        //powerhuman.com/api/employee?name={name}
        if($name)
        {
            $employees->where('name','like','%'.$name.'%');
        }
        if($email)
        {
            $employees->where('email',$email);
        }
        if($age)
        {
            $employees->where('age',$age);
        }
        if($phone)
        {
            $employees->where('phone','like','%'.$phone.'%');
        }
        if($team_id)
        {
            $employees->where('team_id',$team_id);
        }
        if($role_id)
        {
            $employees->where('role_id',$role_id);
        }
        
        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Employee Found'
        );
    }

    public function create(CreateEmployeeRequest $request)
    {
        try {
            if($request->hasFile('photo')){
                $path = $request->file('photo')->store('public/photos');
            }
    
            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => $path,
                'role_id' => $request->role_id,
                'team_id' => $request->team_id,
            ]);

            if(!$employee)
            {
                throw new Exception('Employee no created');
            }
    
            return ResponseFormatter::success($employee,'Employee Created');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }

    }

    public function update(UpdateEmployeeRequest $request,$id)
    {
        try {
            $employee = Employee::find($id);

            if(!$employee){
                throw new Exception('Employee not found');
            }

            if($request->hasFile('photo')){
                $path = $request->file('photo')->store('public/photos');
            }

            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => isset($path) ? $path : $employee->photo,
                'role_id' => $request->role_id,
                'team_id' => $request->team_id,
            ]);

            return ResponseFormatter::success($employee,'Employee Updated');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }
    }

    public function destroy($id)
    {
        //otomatis jadi pake soft delete
        try {
            $employee = Employee::find($id);

            if(!$employee){
                throw new Exception('Employee not found');
            }
            $employee->delete();
            return ResponseFormatter::success($employee,'Employee Deleted');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }
    }
}
