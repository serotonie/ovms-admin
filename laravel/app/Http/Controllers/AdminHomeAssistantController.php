<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminHomeAssistantController extends Controller
{
    /**
     * Display the Home Assistant page.
     */
    public function index(Request $request)
    {
        $vehicle = Vehicle::where('name', 'home-assistant')->first();

        return Inertia::render('Admin/HomeAssistant/Index', [
            'existingVehicle' => $vehicle ? [
                'module_username' => $vehicle->module_username,
                'module_pwd' => null,
            ] : null,
        ]);
    }

    /**
     * Store a new vehicle for Home Assistant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'max:32'],
            'module_username' => ['required', 'max:32'],
            'module_pwd' => ['required', 'max:255'],
        ]);

        $user = $request->user();

        if (! $user) {
            return redirect(route('login'))->withErrors(['auth' => 'Authentication required.']);
        }

        $vehicle = Vehicle::where('module_username', $validated['module_username'])->first();

        if ($vehicle) {
            $vehicle->update([
                'name' => $validated['name'] ?? 'home-assistant',
                'module_pwd' => Hash::make($validated['module_pwd']),
                'mqtt_superuser' => 1,
                'owner_id' => $vehicle->owner_id ?? $user->id,
                'main_user_id' => $vehicle->main_user_id ?? $user->id,
            ]);

            $vehicle->users()->syncWithoutDetaching([$user->id]);

            return redirect(route('admin.home-assistant.index'))->with('success', 'Vehicle updated successfully.');
        }

        $moduleId = 'ha-'.strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));

        $vehicle = Vehicle::create([
            'name' => $validated['name'] ?? 'home-assistant',
            'module_id' => $moduleId,
            'module_username' => $validated['module_username'],
            'module_pwd' => Hash::make($validated['module_pwd']),
            'mqtt_superuser' => 1,
            'owner_id' => $user->id,
            'main_user_id' => $user->id,
        ]);

        $vehicle->users()->sync([$user->id]);

        return redirect(route('admin.home-assistant.index'))->with('success', 'Vehicle created successfully.');
    }
}
