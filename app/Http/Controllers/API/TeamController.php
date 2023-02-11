<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit',10); // defaultnya jadi 10
        
        $teams = Team::query();

        //powerhuman.com/api/team?id={id}
        if($id)
        {
            $team = $teams->find($id);
             
            if($team)
            {
                return ResponseFormatter::success($team,'Team Found');
            }

            return ResponseFormatter::error('Team Not Found');
        }

        //powerhuman.com/api/team
        if($request->company_id){
            $teams = $teams->where('company_id',$request->company_id);    
        }
        
        //powerhuman.com/api/team?name={name}
        if($name)
        {
            $teams->where('name','like','%'.$name.'%');
        }
        
        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Team Found'
        );
    }

    public function create(CreateTeamRequest $request)
    {
        $path = null;
        try {
            if($request->hasFile('icon')){
                $path = $request->file('icon')->store('public/icon');
            }
    
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id,
            ]);

            if(!$team)
            {
                throw new Exception('Team no created');
            }
    
            return ResponseFormatter::success($team,'Team Created');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }

    }

    public function update(UpdateTeamRequest $request,$id)
    {
        
        $path = null;
        try {
            $team = Team::find($id);

            if(!$team){
                throw new Exception('Team not found');
            }

            if($request->hasFile('icon')){
                $path = $request->file('icon')->store('public/icon');
            }

            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'team_id' => $request->team_id,
            ]);

            return ResponseFormatter::success($team,'Team Updated');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }
    }

    public function destroy($id)
    {
        //otomatis jadi pake soft delete
        try {
            $team = Team::find($id);

            if(!$team){
                throw new Exception('Team not found');
            }
            $team->delete();
            return ResponseFormatter::success($team,'Team Deleted');
        } catch (Exception $er) {
            return ResponseFormatter::error($er->getMessage(),500);
        }
    }
}
