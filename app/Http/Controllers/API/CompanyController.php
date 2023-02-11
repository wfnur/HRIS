<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit',10); // defaultnya jadi 10
        
        $companyQuery = Company::whereHas('users',function($query){
            $query->where('user_id',Auth::id());
        })->with(['users']);

        //powerhuman.com/api/company?id={id}
        if($id)
        {
            $company = $companyQuery->find($id);
             
            if($company)
            {
                return ResponseFormatter::success($company,'Company Found');
            }

            return ResponseFormatter::error('Company Not Found');
        }

        //powerhuman.com/api/company
        $company = $companyQuery;

        //powerhuman.com/api/company?name={name}
        if($name)
        {
            $company->where('name','like','%'.$name.'%');
        }
        
        return ResponseFormatter::success(
            $company->paginate($limit),
            'Company Found'
        );
    }

    public function create(CreateCompanyRequest $request)
    {
        $path = null;
        try {
            if($request->hasFile('logo')){
                $path = $request->file('logo')->store('public/logos');
            }
    
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path
            ]);

            if(!$company)
            {
                throw new Exception('Company no created');
            }

            //attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            $company->load('users');
    
            return ResponseFormatter::success($company,'Company Created');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }

    }

    public function update(UpdateCompanyRequest $request,$id)
    {
        
        $path = null;
        try {
            $company = Company::find($id);

            if(!$company){
                throw new Exception('Company not found');
            }

            if($request->hasFile('logo')){
                $path = $request->file('logo')->store('public/logos');
            }

            $company->update([
                'name' => $request->name,
                'logo' => $path
            ]);

            return ResponseFormatter::success($company,'Company Updated');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }
    }
}
