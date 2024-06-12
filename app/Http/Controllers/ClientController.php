<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $client = Client::where('reseller_id',auth()->user()->id)->get();
        $data=[
            'message' => 'Fetched Succsessfully',
            'data' => $client
        ];
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'reseller_id' => auth()->user()->id
        ]);
        $client = Client::create($request->all());
        $data=[
            'message' => 'Created Succsessfully',
            'data' => $client
        ];
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        $data=[
            'message' => 'Fetched Succsessfully',
            'data' => $client
        ];
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $request->merge([
            'reseller_id' => auth()->user()->id
        ]);
        $client->update($request->all());
        $data=[
            'message' => 'Updated Succsessfully',
            'data' => $client
        ];
        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();
        $data=[
            'message' => 'Deleted Succsessfully',
        ];
        return response()->json($data);
    }
}
