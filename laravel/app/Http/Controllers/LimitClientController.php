<?php

namespace App\Http\Controllers;

use App\Models\Limitables;
use Illuminate\Http\Request;

class LimitClientController extends Controller
{
    //
    public function index()
    {
        //
        $getLimitClient = Limitables::all();
        return response()->json($getLimitClient);
    }

    public function store(Request $request){
    $validatedData = $request->validate([
        'limittoday' => 'required',
        'start' => 'required|date',
        'end' => 'required|date',
        'email' => 'required',
    ]);
        
        // return response()->json($limitClient, 201);
        $event = Limitables::create($validatedData);
        return response()->json($event, 201);
    }
    public function update(Request $request, Limitables $id)
    {
        $validatedData = $request->validate([
            'limittoday' => 'required',
            // 'start' => 'required|date',
            // 'end' => 'required|date',
        ]);

        $id->update($validatedData);
        return response()->json($id, 200);
    }

    public function destroy(Limitables $id)
    {
        $id->delete();
        return response()->json(null, 204); 
    }

}
