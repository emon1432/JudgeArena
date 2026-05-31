<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Setting;
use App\Services\ApplicationLogger;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::get();
        return view('admin.pages.settings.index', compact('settings'));
    }

    public function update(Request $request, string $key)
    {
        try {
            $setting = Setting::where('key', $key)->firstOrFail();
            $data = $request->except(['_token', '_method']);

            if (isset($data['logo']) && $data['logo']) {
                $image = $data['logo'];
                $slug = slugify($data['company_name']);
                $path = 'settings';
                if ($setting->value) {
                    $oldValue = json_decode($setting->value, true);
                    if (isset($oldValue['logo']) && $oldValue['logo']) {
                        $oldLogo = $oldValue['logo'];
                    } else {
                        $oldLogo = null;
                    }
                }
                $data['logo'] = imageUpdateManager($image, $slug, $path, $oldLogo);
            }
            if (isset($data['favicon']) && $data['favicon']) {
                $image = $data['favicon'];
                $slug = slugify($data['company_name']);
                $path = 'settings';
                if ($setting->value) {
                    $oldValue = json_decode($setting->value, true);
                    if (isset($oldValue['favicon']) && $oldValue['favicon']) {
                        $oldFavicon = $oldValue['favicon'];
                    } else {
                        $oldFavicon = null;
                    }
                }
                $data['favicon'] = imageUpdateManager($image, $slug, $path, $oldFavicon);
            }

            $setting->value = json_encode($data);
            $setting->save();

            return response()->json([
                'status' => 200,
                'message' => __('Setting updated successfully'),
                'redirect' => null,
            ]);
        } catch (\Exception $e) {
            app(ApplicationLogger::class)->error('Setting update failed', [
                'category' => 'admin',
                'source' => self::class,
                'setting_key' => $key,
            ], $e);

            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }
}
