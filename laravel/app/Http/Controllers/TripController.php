<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::whereRelation('users', 'user_id', '=', auth()->user()->id)
            ->orWhere('owner_id', auth()->user()->id)
            ->orWhere('main_user_id', auth()->user()->id)
            ->get();

        $trips = Trip::whereIn('vehicle_id', $vehicles->pluck('id'))
            ->with('waypoints:trip_id,position_lat,position_long')
            ->orderBy('id', 'desc')
            ->cursorPaginate(12);

        $categories = Category::all();

        if (request()->wantsJson()) {
            return $trips;
        } else {
            return Inertia::render('Trips/Index', [
                'vehicles' => $vehicles,
                'trips' => $trips,
                'categories' => $categories,
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Trip $trip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Trip $trip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip)
    {
        //
    }
}
