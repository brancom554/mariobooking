<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Models\Media;

class MediaController extends Controller
{
	public function assignDataByUserID($user_id, $user_assign){
		$model = new Media();
		$model->updateAuthor(['author' => $user_assign], $user_id);
	}

    public function _mediaBulkActions(Request $request)
    {
        $media_ids = request()->get('post_id');
        $action = request()->get('action', '');
        if (!empty($action) && !empty($media_ids)) {
            $media_ids = explode(',', $media_ids);
            $mediaModel = new Media();
            switch ($action) {
                case 'delete':
                default:
                    $mediaModel->whereIn('media_id', $media_ids)->delete();
                    break;
            }
            $this->sendJson([
                'status' => 1,
                'title' => __('System Alert'),
                'message' => __('Bulk action successfully')
            ], true);
        }
        $this->sendJson([
            'status' => 0,
            'title' => __('System Alert'),
            'message' => __('Data is invalid')
        ], true);
    }

    public function _getAttachments(Request $request)
    {
        $attachments = request()->get('attachments');
        $attachments = explode(',', $attachments);
        $size = request()->get('size', 'full');
        if (is_numeric($size)) {
            $size = [$size, $size];
        } elseif (strpos(',', $size) !== FALSE) {
            $size = explode(',', $size);
            if (count($size) == 1) {
                $size[] = $size[0];
            }
        }
        $html = '';
        $url = [];
        if (!empty($attachments)) {
            foreach ($attachments as $attachment_id) {
                $attachment = get_attachment_info($attachment_id, $size);
                if ($attachment) {
                    $url[] = $attachment['url'];
                    $html .= '<div class="attachment-item"><div class="thumbnail"><img src="' . esc_attr($attachment['url']) . '" alt="' . esc_attr($attachment['description']) . '"></div></div>';
                }

            }
        }

        $this->sendJson([
            'status' => 1,
            'html' => $html,
            'url' => $url
        ], true);
    }

    public function _getAdvanceAttachments(Request $request)
    {
        $attachments = request()->get('attachments');
        $attachments = explode(',', $attachments);
        $post_type = request()->get('post_type', 'home');
        $size = explode(',', request()->get('size', '450,320'));
        $html = '';
        $isFeatured = '';
        if (!empty($attachments) && is_array($attachments)) {
            $postID = request()->get('postID');
            $post = get_post($postID, $post_type);
            $isFeatured = $post->thumbnail_id;
            foreach ($attachments as $id) {
                if (!$isFeatured) {
                    set_post_thumbnail($postID, $id, $post_type);
                    $isFeatured = $id;
                }
                $img = get_attachment_url($id);
                $classFeatured = ($id == $isFeatured) ? 'is-featured' : '';
                $html .= '<div class="col-6 col-md-3 item"><div class="gallery-item">
                    <div class="gallery-image">
                        <div class="hh-loading ">
                            <div class="lds-ellipsis loading-gallery">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>
                        <div class="gallery-action">
                            <a href="javascript: void(0)" class="hh-gallery-add-featured ' . esc_attr($classFeatured) . '" data-style="' . esc_attr(implode(',', $size)) . '" data-post-type="' . esc_attr($post_type) . '" data-post-id="' . esc_attr($postID) . '" data-id="' . esc_attr($id) . '" title="set is featured"><i class="fe-bookmark"></i></a>
                            <a href="javascript: void(0)" class="hh-gallery-delete" data-post-id="' . esc_attr($postID) . '" data-id="' . esc_attr($id) . '" title="' . __('Delete') . '"><i class="dripicons-trash"></i></a>
                        </div>
                        <img src="' . esc_attr($img) . '" alt="' . esc_attr(get_attachment_alt($id)) . '"
                             class="img-responsive">
                    </div>
                </div></div>';
            }
        }

        $this->sendJson([
            'status' => 1,
            'html' => $html,
            'featured_image' => get_attachment_url($isFeatured, $size)
        ], true);
    }

    public function _updateMediaItemDetail(Request $request)
    {
        $attachment_id = request()->get('media_id');
        $media_title = request()->get('media_title');
        $media_description = request()->get('media_description');

        $media = new Media();
        $mediaObject = $media->issetAttachment($attachment_id);
        if ($mediaObject) {
            $data = [
                'media_title' => $media_title,
                'media_description' => $media_description
            ];
            $updated = $media->updateMedia($data, $attachment_id);
            if (!is_null($updated)) {
                $this->sendJson([
                    'status' => 1,
                    'title' => __('System Alert'),
                    'message' => __('Updated successfully')
                ], true);
            } else {
                $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not update this attachment')
                ], true);
            }
        } else {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('Not found this attachment')
            ], true);
        }
    }

    public function _mediaItemDetail(Request $request)
    {
        $attachment_id = request()->get('attachment_id');
        $media = new Media();
        $mediaObject = $media->getById($attachment_id);
        if (!empty($mediaObject) && is_object($mediaObject)) {
            $html = view('dashboard.components.media-item-detail', (array)$mediaObject)->render();
            $this->sendJson([
                'status' => 1,
                'message' => __('Loaded successfully'),
                'html' => $html
            ], true);
        }

        $this->sendJson([
            'status' => 0,
            'title' => __('System Alert'),
            'message' => __('Not found this attachment'),
        ], true);
    }

    public function _allMedia(Request $request)
    {
        $type = request()->get('type', '');
        $sort = request()->get('sort', 'grid');
        $media = new Media();
        $allMedia = $media->listAttachments();
        $html = '';
        if (!empty($allMedia) && is_object($allMedia)) {
            foreach ($allMedia as $key => $attachment) {
                $attachment = (array)$attachment;
                $attachment['type'] = $type;
                $html .= view('dashboard.components.media-item', ['attachment' => $attachment, 'sort' => $sort])->render();
            }
            $this->sendJson([
                'status' => 1,
                'title' => __('System Alert'),
                'message' => __('Loaded Media'),
                'html' => $html
            ], true);
        } else {
            $this->sendJson([
                'status' => 1,
                'title' => __('System Alert'),
                'message' => __('Not found media'),
                'html' => $html
            ], true);
        }
    }

    public function _addMedia(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'file' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]
        );
        if ($validator->fails()) {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => $validator->errors()->first()
            ]);
        }
        $file = $request->file('file');
        $name = $file->getClientOriginalName();
        if (!empty($name)) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $title = pathinfo($name, PATHINFO_FILENAME);
            $size = $file->getSize();
            $name = Str::slug($title);
            $savedName = $name . '-' . time() . '.' . $ext;
            $folder = $this->getMediaFolder();
            $saved = $file->move(storage_path($folder), $savedName);
            if (!empty($saved) && is_object($saved)) {
                $tinypng_enable = get_opt('tinypng_enable', 'off');
                if ($tinypng_enable == 'on') {
                    try {
                        \Tinify\setKey(get_opt('tinypng_api_key', ''));
                        \Tinify\validate();
                        $source = \Tinify\fromFile($saved->getPathname());
                        $source->toFile($saved->getPathname());
                    } catch (\Tinify\Exception $e) {

                    }
                }
                $data = [
                    'media_title' => $title,
                    'media_name' => $name,
                    'media_url' => $this->getMediaFolder(true) . '/' . $savedName,
                    'media_path' => $saved->getPathname(),
                    'media_size' => $size,
                    'media_type' => $saved->getExtension(),
                    'media_description' => $title,
                    'author' => Sentinel::getUser()->getUserId(),
                    'created_at' => time()
                ];

                $media = new Media();
                $media_id = $media->create($data);
                if ($media_id) {
                    $this->sendJson([
                        'status' => 2,
                        'title' => __('System Alert'),
                        'message' => sprintf(__('The attachment %s is uploaded successfully'), $title),
                    ], true);
                } else {
                    $this->sendJson([
                        'status' => 0,
                        'title' => __('System Alert'),
                        'message' => __('Have error when saving')
                    ], true);
                }
            } else {
                $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Have error when uploading')
                ], true);
            }
        } else {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This file is invalid')
            ], true);
        }
    }

    public function _deleteMediaItem(Request $request)
    {
        $attachment_id = request()->get('attachment_id');
        $media = new Media();
        $mediaObject = $media->getById($attachment_id);
        if (!empty($mediaObject) && is_object($mediaObject)) {
            $path = $mediaObject->media_path;
            if (is_file($path)) @unlink($path);
            $deleted = $media->deleteAttachment($attachment_id);
            if ($deleted) {
                $this->sendJson([
                    'status' => 1,
                    'title' => __('System Alert'),
                    'message' => __('Deleted successfully')
                ], true);
            } else {
                $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not delete this attachment')
                ], true);
            }
        } else {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('Not found this attachment')
            ], true);
        }
    }

    public function _getMedia()
    {
        $folder = $this->getFolder();
        return view("dashboard.screens.{$folder}.media", ['role' => $folder, 'bodyClass' => 'hh-dashboard']);
    }

    public function getMediaFolder($storage = false)
    {
        $user = Sentinel::getUser();
        $user_id = $user->getUserId();
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        if ($storage) {
            return asset('storage/u-' . $user_id . '/' . $year . '/' . $month . '/' . $day);
        } else {
            return 'app/public/u-' . $user_id . '/' . $year . '/' . $month . '/' . $day;
        }
    }

	public static function get_inst()
	{
		static $instance;
		if (is_null($instance)) {
			$instance = new self();
		}

		return $instance;
	}
}
