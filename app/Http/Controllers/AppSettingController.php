<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::orderBy('key')->get();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = AppSetting::where('key', $key)->first();
            
            if ($setting) {
                $processedValue = $this->processValue($value, $setting->type);
                AppSetting::set($key, $processedValue, $setting->type, $setting->description);
            }
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    private function processValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return $value === '1' || $value === 'on' || $value === true;
            case 'integer':
                return (int) $value;
            case 'json':
                return is_array($value) ? $value : json_decode($value, true);
            default:
                return $value;
        }
    }
}