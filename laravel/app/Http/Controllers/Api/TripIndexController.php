<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiTripIndexRequest;
use App\Models\Trip;
use App\Models\Vehicle;

class TripIndexController extends Controller
{
    public function __invoke(ApiTripIndexRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $accessibleVehicleIds = Vehicle::query()
            ->where(function ($query) use ($user) {
                $query->whereRelation('users', 'user_id', '=', $user->id)
                    ->orWhere('owner_id', $user->id)
                    ->orWhere('main_user_id', $user->id);
            })
            ->pluck('id');

        $trips = Trip::query()
            ->with([
                'vehicle:id,name,module_id',
                'user:id,name,email',
            ])
            ->whereIn('vehicle_id', $accessibleVehicleIds)
            ->when(isset($validated['vehicle_id']), function ($query) use ($validated) {
                $query->where('vehicle_id', $validated['vehicle_id']);
            })
            ->when(isset($validated['user_id']), function ($query) use ($validated) {
                $query->where('user_id', $validated['user_id']);
            })
            ->where('start_time', '<=', $validated['end_at'])
            ->where(function ($query) use ($validated) {
                $query->whereNull('stop_time')
                    ->orWhere('stop_time', '>=', $validated['start_at']);
            })
            ->orderBy('start_time')
            ->get();

        return response()->json($trips);
    }
}
