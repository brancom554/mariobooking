<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Sentinel;


class PackageController extends Controller
{

    public function __construct()
    {
        add_action('hh_dashboard_breadcrumb', [$this, '_addCreatePackageButton']);
    }

    public function _getListPackage(){
	    $folder = $this->getFolder();
	    $packageModel = new Package();
	    $allPackage = $packageModel->getAllPackages();
	    return view("dashboard.screens.{$folder}.list-package", ['bodyClass' => 'hh-dashboard', 'allPackages' => $allPackage]);
    }

    public function _addCreatePackageButton()
    {
        $screen = current_screen();
        if ($screen == 'package') {
            echo view('dashboard.components.quick-add-package')->render();
        }
    }

    public function _getPackageItem(Request $request)
    {
        $package_id = request()->get('packageID');
        $package_encrypt = request()->get('packageEncrypt');
        if (!hh_compare_encrypt($package_id, $package_encrypt)) {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This Package is invalid')
            ], true);
        }

        $packageModel = new Package();
        $packageObject = $packageModel->getById($package_id);
        if (!empty($packageObject) && is_object($packageObject)) {
            $html = view('dashboard.components.package-form', ['packageObject' => $packageObject])->render();

            $this->sendJson([
                'status' => 1,
                'html' => $html
            ], true);
        } else {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This package is invalid')
            ], true);
        }
    }

    public function _deletePackageItem(Request $request)
    {
        $package_id = request()->get('packageID');
        $package_encrypt = request()->get('packageEncrypt');

        if (!hh_compare_encrypt($package_id, $package_encrypt)) {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' =>__( 'This Package is invalid')
            ], true);
        }
        $packageModel = new Package();
        $packageObject = $packageModel->getById($package_id);

        if (!empty($packageObject) && is_object($packageObject)) {
            $deleted = $packageModel->deletePackage($package_id);

            if ($deleted) {
                $this->sendJson([
                    'status' => 1,
                    'title' => __('System Alert'),
                    'message' => __('This Package is deleted'),
                    'reload' => true
                ], true);
            } else {
                $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not delete this package')
                ], true);
            }
        } else {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This Package is invalid')
            ], true);
        }
    }

    public function _updatePackageItem(Request $request)
    {
        $package_id = request()->get('packageID');
        $package_encrypt = request()->get('packageEncrypt');

        if (!hh_compare_encrypt($package_id, $package_encrypt)) {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This Package is invalid')
            ], true);
        }
        $packageModel = new Package();
        $packageObject = $packageModel->getById($package_id);

        if (!empty($packageObject) && is_object($packageObject)) {

	        $default_commission = get_option('partner_commission', 0);
	        $package_name = request()->get('package_name');
	        $package_price = request()->get('package_price');
	        $package_time = request()->get('package_time', '');
	        $package_commission = request()->get('package_commission', $default_commission);
	        $package_description = request()->get('package_description', '');

	        if($package_name){
		        if ($package_price < 0 || !is_numeric($package_price))
			        $package_price = 0;

		        if($package_commission < 0 || !is_numeric($package_commission)){
			        $package_commission = 0;
		        }

		        if($package_commission > 100){
			        $package_commission = 100;
		        }

		        $data = [
			        'package_name' => $package_name,
			        'package_price' => $package_price,
			        'package_time' => (int)$package_time,
			        'package_commission' => $package_commission,
			        'package_description' => $package_description
		        ];

                $updated = $packageModel->updatePackage($data, $package_id);

                if ($updated) {
                    $this->sendJson([
                        'status' => 1,
                        'title' => __('System Alert'),
                        'message' => __('Updated Successfully'),
                    ], true);
                } else {
                    $this->sendJson([
                        'status' => 0,
                        'title' => __('System Alert'),
                        'message' => __('Can not update this package')
                    ], true);
                }
            } else {
                $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Some fields is incorrect')
                ], true);
            }

        } else {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This Package is invalid')
            ], true);
        }
    }

    public function _addNewPackage(Request $request)
    {
    	$default_commission = get_option('partner_commission', 0);
        $package_name = request()->get('package_name');
        $package_price = request()->get('package_price');
        $package_time = request()->get('package_time', '');
        $package_commission = request()->get('package_commission', $default_commission);
        $package_description = request()->get('package_description', '');

        if ($package_name) {
	        $packageModel = new Package();

	        if ($package_price < 0 || !is_numeric($package_price))
		        $package_price = 0;

	        if($package_commission < 0 || !is_numeric($package_commission)){
		        $package_commission = 0;
	        }

	        if($package_commission > 100){
		        $package_commission = 100;
	        }

            $data = [
                'package_name' => $package_name,
                'package_price' => $package_price,
                'package_time' => (int)$package_time,
                'package_commission' => $package_commission,
                'package_description' => $package_description,
                'created_at' => time()
            ];

            $newPackage = $packageModel->createPackage($data);
            if ($newPackage) {
                $this->sendJson([
                    'status' => 1,
                    'title' => __('System Alert'),
                    'message' => __('Created Successfully'),
                    'reload' => true
                ], true);
            } else {
                $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not create this package')
                ], true);
            }
        } else {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('Some fields is incorrect')
            ], true);
        }
    }

    public function _allPackages(Request $request, $page = 1)
    {
        $folder = $this->getFolder();
        $search = request()->get('_s');
        $packageModel = new Package();
	    $allPackage = $packageModel->getAllPackages(
            [
                'search' => $search,
                'page' => $page
            ]
        );

        return view("dashboard.screens.{$folder}.package", ['bodyClass' => 'hh-dashboard', 'allPackages' => $allPackage]);
    }

}
