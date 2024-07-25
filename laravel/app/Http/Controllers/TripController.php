<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Rules\IsUserOfVehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::orWhereRelation('users', 'user_id', '=', auth()->user()->id)
            ->orWhere('owner_id', auth()->user()->id)
            ->orWhere('main_user_id', auth()->user()->id)
            ->with('users:name')
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'user_id' => ['required', 'exists:users,id', new IsUserOfVehicle],
        ]);

        $trip->category_id = $validated['category_id'];
        $trip->user_id = $validated['user_id'];
        $trip->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip)
    {
        //
    }
}
