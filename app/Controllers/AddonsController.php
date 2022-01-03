<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AddonsController extends Controller
{

    private $url = 'https://extensions.awebooking.org/extensions.php';

    public function _addons(Request $request)
    {
        $request = file_get_contents(add_query_arg(['addons' => 'all'], $this->url));
        $content = json_decode($request);
        return view('dashboard.screens.administrator.addons', ['addons' => $content, 'bodyClass' => 'hh-dashboard']);
    }

    public function _actionExtension(Request $request)
    {
        $action = $request->get('action', 'delete');
        $update = $request->get('update', '0');
        $addon = $request->get('extension');

        if ($update == 1) {
            $folder_path = app_path('Addons/' . trim($addon));
            if (is_dir($folder_path)) {
                File::deleteDirectory($folder_path);
            }
            $result = $this->curl([
                'download' => $addon
            ]);
            if ($result) {
                $file_name = app_path('Addons/' . trim($addon) . '.zip');
                $saved = file_put_contents($file_name, $result);
                if ($saved) {
                    $zip = new \ZipArchive();
                    $zip->open($file_name, \ZipArchive::CREATE);
                    $extracted = $zip->extractTo(app_path('Addons/'));
                    $zip->close();
                    File::delete($file_name);
                    if ($extracted) {
                        return $this->sendJson([
                            'status' => 1,
                            'title' => __('System Alert'),
                            'message' => __('The addon has been updated successfully'),
                            'reload' => true
                        ]);
                    } else {
                        return $this->sendJson([
                            'status' => 0,
                            'title' => __('System Alert'),
                            'message' => __('Can not extract the addon'),
                            'reload' => true
                        ]);
                    }
                } else {
                    return $this->sendJson([
                        'status' => 0,
                        'title' => __('System Alert'),
                        'message' => __('Can not update this Addon. Try again!'),
                        'reload' => true
                    ]);
                }
            } else {
                return $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not install this Addon. Try install again!'),
                    'reload' => true
                ]);
            }
        }
        if ($action === 'delete') {
            global $hh_extensions;
            if (empty($hh_extensions) || !isset($hh_extensions[$addon])) {
                return $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('This addon is not installed')
                ]);
            }
            $path = app_path('Addons/' . $addon);
            $deleted = File::deleteDirectory($path);
            if (!$deleted) {
                return $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not remove this Addon. Try again!')
                ]);
            } else {
                return $this->sendJson([
                    'status' => 1,
                    'title' => __('System Alert'),
                    'message' => __('This Addon is deleted'),
                    'reload' => true
                ]);
            }
        }
        if ($action === 'install') {
            $result = $this->curl([
                'download' => $addon
            ]);
            if ($result) {
                $file_name = app_path('Addons/' . trim($addon) . '.zip');
                $saved = file_put_contents($file_name, $result);
                if ($saved) {
                    $zip = new \ZipArchive();
                    $zip->open($file_name, \ZipArchive::CREATE);
                    $extracted = $zip->extractTo(app_path('Addons/'));
                    $zip->close();
                    File::delete($file_name);
                    if ($extracted) {
                        return $this->sendJson([
                            'status' => 1,
                            'title' => __('System Alert'),
                            'message' => __('The addon has been installed successfully'),
                            'reload' => true
                        ]);
                    } else {
                        return $this->sendJson([
                            'status' => 0,
                            'title' => __('System Alert'),
                            'message' => __('Can not extract the addon'),
                            'reload' => true
                        ]);
                    }
                } else {
                    return $this->sendJson([
                        'status' => 0,
                        'title' => __('System Alert'),
                        'message' => __('Can not install this Addon. Try again!'),
                        'reload' => true
                    ]);
                }
            } else {
                return $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not install this Addon. Try again!'),
                    'reload' => true
                ]);
            }
        }
    }

    public function curl($data = [])
    {
        $url = $this->url;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, count($data));

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }
}
