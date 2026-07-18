<?php

namespace App\Http\Controllers;

use App\Models\CompanyLocation;
use Illuminate\Http\Request;

class CompanyLocationController extends Controller
{
    /**
     * Show the company location settings page.
     */
    public function index()
    {
        $location = CompanyLocation::getActive();
        $settings = CompanyLocation::getSettings();

        return view('admin.location', compact('location', 'settings'));
    }

    /**
     * Update the company location settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:5000',
        ]);

        $location = CompanyLocation::getActive();

        if ($location) {
            $location->update($validated);
        } else {
            $validated['is_active'] = true;
            CompanyLocation::create($validated);
        }

        return redirect()->route('admin.location')
            ->with('success', 'Lokasi toko berhasil diperbarui!');
    }
}
