<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Plank\Mediable\Facades\MediaUploader;

class AdminVehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vehicle::query()->when($request->get('search'), function ($query, $search) {
            $search = strtolower(trim($search));

            return $query->whereRaw('LOWER(vehicles.name) LIKE ?', ["%$search%"]);
        })->when($request->get('sort'), function ($query, $sortBy) {
            return $query->orderBy($sortBy['key'], $sortBy['order']);
        });

        $data = $query->paginate($request->get('limit', 10));
        foreach ($data as $item) {
            $item['owner'] = $item->owner()->get()->select('id', 'name')->toArray()[0];
            $item['main_user'] = $item->main_user()->get()->select('id', 'name')->toArray()[0];
            $item['users'] = $item->users()->get()->select('id', 'name')->toArray();
        }

        return Inertia::render('Admin/Vehicles/Index', [
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Vehicles/Create', [
            'system_users' => User::all('id', 'name'),
            'mqtt' => [
                'hostname' => 'mqtt.host.name', //TODO add config
                'tls' => true,
                'port' => 8883,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:32'],
            'owner' => ['required', 'exists:users,id'],
            'main_user' => ['exists:users,id'],
            'users' => ['array', 'exists:users,id'],
            'module_id' => ['required', 'max:32', 'unique:vehicles,module_id'],
            'module_username' => ['required', 'max:32', 'unique:vehicles,module_username'],
            'module_pwd' => ['required', 'max:255'],
        ]);

        $vehicle = Vehicle::create([
            'name' => $validated['name'],
            'module_id' => $validated['module_id'],
            'module_username' => $validated['module_username'],
            'module_pwd' => Hash::make($validated['module_pwd']),
            'owner_id' => $validated['owner'],
            'main_user_id' => $validated['main_user'],
        ]);

        $vehicle->users()->attach($validated['users']);

        $picture = MediaUploader::fromSource($request->file('picture'))
            ->setAllowedAggregateTypes(['image'])
            ->toDestination('vehicle_images', $vehicle->id)
            ->useHashForFilename()
            ->upload();

        $vehicle->syncMedia($picture, 'picture');

        //TODO acls granularity in real life
        $topic_prefix = 'ovms/'.$validated['module_username'].'/'.$validated['module_id'];
        $topics = [
            1 => $topic_prefix.'/#',
            2 => $topic_prefix.'/#',
            4 => $topic_prefix.'/#',
        ];

        foreach ($topics as $key => $value) {
            DB::table('mqtt_acls')->insert([
                'username' => $validated['module_username'],
                'rw' => $key,
                'topic' => $value,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        $picture = $vehicle->getMedia('picture')->first();

        $vehicle['owner'] = $vehicle->owner()->get()->select('id', 'name')->toArray()[0];
        $vehicle['main_user'] = $vehicle->main_user()->get()->select('id', 'name')->toArray()[0];
        $vehicle['users'] = $vehicle->users()->get()->select('id', 'name')->toArray();
        $vehicle['picture'] = $picture != null ? $picture->getUrl() : '/car_default.png';

        return Inertia::render('Admin/Vehicles/Edit', [
            'system_users' => User::all('id', 'name'),
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:32'],
            'owner' => ['required', 'exists:users,id'],
            'main_user' => ['exists:users,id'],
            'users' => ['array', 'exists:users,id'],
        ]);

        $vehicle->name = $validated['name'];
        $vehicle->owner_id = $validated['owner'];
        $vehicle->main_user_id = $validated['main_user'];

        $vehicle->save();

        $vehicle->users()->sync($validated['users']);

        $vehicle->getMedia('picture')->first()->delete();

        $picture = MediaUploader::fromSource($request->file('picture'))
            ->setAllowedAggregateTypes(['image'])
            ->toDestination('vehicle_images', $vehicle->id)
            ->useHashForFilename()
            ->upload();

        $vehicle->syncMedia($picture, 'picture');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->getMedia('picture')->first()->delete();
        $vehicle->delete();
    }
}
