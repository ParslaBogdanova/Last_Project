<?php

namespace App\Http\Controllers;

use App\Models\Calender;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('calendar.index');
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Calender $calender)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Calender $calender)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Calender $calender)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Calender $calender)
    {
        //
    }
}
