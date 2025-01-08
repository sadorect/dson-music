<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'maintenance_mode' => 'boolean',
            'max_upload_size' => 'required|integer'
        ]);

        foreach ($validated as $key => $value) {
            setting([$key => $value]);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully');
    }
}
