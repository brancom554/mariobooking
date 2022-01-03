<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Taxonomy;
use Illuminate\Support\Facades\File;

use Spatie\ImageOptimizer\OptimizerChainFactory;

class UpdateController extends Controller
{
    private $versions = [
        'version_1_1',
        'version_1_2',
        'version_1_2_1',
        'version_1_2_2',
        'version_1_2_3',
        'version_1_3',
        'version_1_3_1',
        'version_1_3_2',
        'version_1_3_3',
        'version_1_3_5',
    ];
    private $messages = [];

    public function _clearCache(Request $request, $name = '', $redirect = '')
    {
        $redirect = base64_decode($redirect);
        if (filter_var($redirect, FILTER_VALIDATE_URL) === FALSE) {
            $redirect = url('/');
        }

        $cache_path = public_path('caching');
        $types = ['header', 'footer'];
        $extensions = ['js', 'css'];
        foreach ($types as $type) {
            foreach ($extensions as $extension) {
                $file = $cache_path . '/' . $type . '-' . $name . '-minified-frontend.' . $extension;
                if (is_file($file)) {
                    File::delete($file);
                }
            }
        }
        return redirect($redirect);
    }

    public function _systemTools(Request $request)
    {
        return view('system.tools');
    }

    public function _systemToolsPost(Request $request)
    {
        $action = $request->get('system_tool');
        $password = $request->get('password');
        $system_password = env('SYSTEM_PASSWORD');
        if (!empty($system_password) && $system_password === $password && !empty($action)) {
            switch ($action) {
                case 'clear_cache':
                    Cache::pull('hh_langs_full');
                    Artisan::call('cache:clear');
                    $output = Artisan::output();
                    $this->messages[] = $output;

                    Artisan::call('view:clear');
                    $output = Artisan::output();
                    $this->messages[] = $output;

                    Artisan::call('route:clear');
                    $output = Artisan::output();
                    $this->messages[] = $output;

                    File::deleteDirectory(public_path('caching'));

                    $this->messages[] = __('Deleted scripts cache');

                    break;
                case 'clear_view';
                    Artisan::call('view:clear');
                    $output = Artisan::output();

                    $this->messages[] = $output;
                    break;
                case 'update_version':
                    foreach ($this->versions as $version) {
                        $updated = get_opt('awebooking_' . $version, false);
                        if (!$updated) {
                            $this->$version();
                            update_opt('awebooking_' . $version, 'updated');
                        } else {
                            $this->messages[] = sprintf(__('Has updated version %s'), $version);
                        }
                    }
                    break;
                case 'symlink':
                    Artisan::call('extension:link');
                    Artisan::call('storage:link');
                    $output = Artisan::output();

                    $this->messages[] = $output;

                    break;
                case 'on_coming':
                    Artisan::call('up');
                    $output = Artisan::output();

                    $this->messages[] = $output;
                    break;
                case 'off_coming':
                    Artisan::call('down');
                    $output = Artisan::output();

                    $this->messages[] = $output;
                    break;
                default:
                    $this->messages[] = __('Can not access this action');
            }
        } else {
            $this->messages[] = __('Can not access this action. Please check the password.');
        }

        return $this->sendJson([
            'status' => 1,
            'message' => view('common.update', ['messages' => $this->messages])->render()
        ]);
    }

    public function version_1_3_5(){
        $this->_migrate();

        $output = Artisan::output();

        $this->messages[] = $output;

        DB::table('language')->update([
            'rtl' => 'no'
        ]);

    }
    public function version_1_3_3(){
        $this->_migrate();

        $output = Artisan::output();

        $this->messages[] = $output;
    }
    public function version_1_3_2(){
        $this->_migrate();

        $output = Artisan::output();

        $this->messages[] = $output;
    }
    public function version_1_3_1(){
        $this->_migrate();

        $output = Artisan::output();

        $this->messages[] = $output;
    }

    public function version_1_3()
    {
        $this->_migrate();
        DB::table('home')->update([
            'enable_extra_guest' => 'off',
            'extra_guest_price' => 0,
            'apply_to_guest' => 1,
        ]);
        $output = Artisan::output();

        $this->messages[] = $output;
    }

    public function version_1_2_3()
    {
        $this->_migrate();
        DB::table('home')->update(['post_type' => 'home']);
        DB::table('experience')->update(['post_type' => 'experience']);
        DB::table('car')->update(['post_type' => 'car']);

        $output = Artisan::output();

        // Rename media folder by user id
        $media_path = storage_path('app/public');
        $folders = glob($media_path . '/*', GLOB_ONLYDIR);
        if (!empty($folders) && is_array($folders)) {
            foreach ($folders as $key => $folder) {
                $folder_arr = explode(DIRECTORY_SEPARATOR, $folder);
                $folder_name = end($folder_arr);
                $user = get_user_by_email($folder_name);
                if (is_object($user)) {
                    $user_id = $user->getUserId();
                    $media_object = new Media();
                    $media_by_user = $media_object->getByAuthor($user_id);
                    if ($media_by_user) {
                        foreach ($media_by_user as $media_item) {
                            $media_u = str_replace($user->email, 'u-' . $user_id, $media_item->media_url);
                            $media_p = str_replace($user->email, 'u-' . $user_id, $media_item->media_path);
                            $media_object->updateMedia([
                                'media_url' => $media_u,
                                'media_path' => $media_p,
                            ], $media_item->media_id);
                        }
                    }
                    rename($media_path . '/' . $folder_name, $media_path . '/u-' . $user_id);
                }
            }
        }
        $this->messages[] = $output;
    }

    public function version_1_2_2()
    {
        $this->_migrate();

        DB::table('home')->update(['use_long_price' => 'off']);

        $output = Artisan::output();

        $this->messages[] = $output;
    }

    public function version_1_2_1()
    {
        $this->_migrate();

        $output = Artisan::output();

        $this->messages[] = $output;
    }

    public function version_1_2()
    {
        $this->_migrate();

        $output = Artisan::output();

        // Add new taxonomy for experiences
        DB::table('taxonomy')->whereRaw("taxonomy_name IN ('post-category', 'post-tag')")->update(['post_type' => 'post']);
        DB::table('taxonomy')->whereRaw("taxonomy_name IN ('home-type', 'home-amenity')")->update(['post_type' => 'home']);

        DB::table('taxonomy');
        $tax = new Taxonomy();
        $data = [
            [
                'taxonomy_title' => 'Languages',
                'taxonomy_name' => 'experience-languages',
                'post_type' => 'experience',
                'created_at' => time()
            ],
            [
                'taxonomy_title' => 'Inclusions',
                'taxonomy_name' => 'experience-inclusions',
                'post_type' => 'experience',
                'created_at' => time()
            ],
            [
                'taxonomy_title' => 'Exclusions',
                'taxonomy_name' => 'experience-exclusions',
                'post_type' => 'experience',
                'created_at' => time()
            ],
            [
                'taxonomy_title' => 'Experience Types',
                'taxonomy_name' => 'experience-type',
                'post_type' => 'experience',
                'created_at' => time()
            ],
            [
                'taxonomy_title' => 'Car Types',
                'taxonomy_name' => 'car-type',
                'post_type' => 'car',
                'created_at' => time()
            ],
            [
                'taxonomy_title' => 'Car Equipments',
                'taxonomy_name' => 'car-equipment',
                'post_type' => 'car',
                'created_at' => time()
            ],
            [
                'taxonomy_title' => 'Car Features',
                'taxonomy_name' => 'car-feature',
                'post_type' => 'car',
                'created_at' => time()
            ]
        ];

        foreach ($data as $args) {
            if (!$tax->getByName($args['taxonomy_name'])) {
                $tax->create($args);
            }
        }

        // Update 'service_type' column for booking table
        DB::table('booking')->update(['service_type' => 'home']);

        $this->messages[] = $output;
    }

    public function version_1_1()
    {
        $this->_migrate();
        DB::table('home')->update(['booking_form' => 'instant']);

    }

    private function _migrate()
    {
        Artisan::call('migrate');
        $output = Artisan::output();
        $this->messages[] = $output;
    }


    public function _setIconSVG(Request $request)
    {
        global $hh_fonts;
        if (!$hh_fonts) {
            include_once public_path('fonts/fonts.php');
            if (isset($fonts)) {
                $hh_fonts = $fonts;
            }
        }
        return $this->sendJson([
            'status' => 1,
            'icons' => $hh_fonts
        ]);
    }


    public function _getIconSVG(Request $request)
    {
        $name = $request->get('name', '');
        $color = $request->get('color', '');
        $width = $request->get('width', '');
        $height = $request->get('height', '');
        $stroke = $request->get('stroke', '');

        return $this->sendJson([
            'status' => 1,
            'icon' => get_icon($name, $color, $width, $height, $stroke, true)
        ]);
    }
}
