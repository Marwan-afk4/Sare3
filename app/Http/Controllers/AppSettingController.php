<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function index()
    {
        // Ensure admin profit percentage setting exists
        $this->ensureDefaultSettings();
        
        $settings = AppSetting::orderBy('key')->get()->map(function ($setting) {
            // Cast the value properly for display
            $setting->cast_value = AppSetting::get($setting->key);
            return $setting;
        });
        return view('settings.index', compact('settings'));
    }

    /**
     * Ensure default settings exist
     */
    private function ensureDefaultSettings()
    {
        // Ensure admin profit percentage setting exists
        if (!AppSetting::where('key', 'admin_profit_percentage')->exists()) {
            AppSetting::set('admin_profit_percentage', 10, 'string', 'Admin profit percentage from rides (0-100%)');
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'array',
            'settings.admin_profit_percentage' => 'nullable|numeric|min:0|max:100',
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