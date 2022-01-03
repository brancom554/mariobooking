<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Sentinel;
use Illuminate\Support\Str;
use App\Models\TermRelation;
use App\Models\Term;
use App\Models\Taxonomy;

class PostController extends Controller
{
    public function __construct()
    {
        add_action('hh_dashboard_breadcrumb', [$this, '_addCreatePostButton']);
    }

	public function _changePostStatus(Request $request){
		$post_id = request()->get('serviceID');
		$post_encrypt = request()->get('serviceEncrypt');
		$status = request()->get('status', '');

		if (!hh_compare_encrypt($post_id, $post_encrypt) || !$status) {
			$this->sendJson([
				'status' => 0,
				'title' => __('System Alert'),
				'message' => __('The data is invalid')
			], true);
		}

		$model = new Post();
		$updated = $model->updatePost([
			'status' => $status
		], $post_id);

		if ($updated) {
			$this->sendJson([
				'status' => 1,
				'title' => __('System Alert'),
				'message' => __('Updated Successfully'),
				'reload' => true
			], true);
		} else {
			$this->sendJson([
				'status' => 0,
				'title' => __('System Alert'),
				'message' => __('Have error when saving')
			], true);
		}
	}

	public function _bulkActions(Request $request){
		$action = request()->get('action', '');
		$post_id = request()->get('post_id', '');

		if(!empty($action) && !empty($post_id)){
            $post_id = explode(',', $post_id);

			$postModel = new Post();
			switch ($action){
				case 'delete':
					$postModel->whereIn('post_id', $post_id)->delete();
					$commentModel = new Comment();
					$commentModel->whereIn('post_id', $post_id)->where('post_type', 'posts')->delete();
					$termRelationModel = new TermRelation();
					$termRelationModel->whereIn('service_id', $post_id)->delete();
					break;
				case 'publish':
				case 'trash':
				case 'draft':
					$postModel->updateMultiPost([
						'status' => $action
					], $post_id);
					break;
			}
			$this->sendJson( [
				'status'  => 1,
				'title'   => __( 'System Alert' ),
				'message' => __( 'Bulk action successfully' )
			], true );
		}
		$this->sendJson( [
			'status'  => 0,
			'title'   => __( 'System Alert' ),
			'message' => __( 'Data invalid' )
		], true );
	}

    public function _postComment($page = 1)
    {

        $folder = $this->getFolder();

        $search = request()->get('_s');
        $status = request()->get('status', '');

        $comment_obj = new Comment();
        $comments = $comment_obj->getAllComments([
            'type' => 'posts',
            'search' => $search,
            'page' => $page,
            'status' => $status
        ]);

        return view("dashboard.screens.{$folder}.post-comment", ['role' => $folder, 'bodyClass' => 'hh-dashboard', 'comments' => $comments]);
    }

    public function viewTag($term_slug, $page = 1)
    {
        $postModel = new Post();
        $post = $postModel->getAllPosts([
            'page' => $page,
            'orderby' => 'post_id',
            'order' => 'desc',
            'number' => posts_per_page(),
            'term_slug' => $term_slug
        ]);
        return view("frontend.archive", ['posts' => $post, 'slug' => 'term_slug', 'term' => [
            'term_slug' => $term_slug,
            'taxonomy' => 'Tag'
        ]]);
    }

    public function viewCategory($term_slug, $page = 1)
    {
        $postModel = new Post();
        $post = $postModel->getAllPosts([
            'page' => $page,
            'orderby' => 'post_id',
            'order' => 'desc',
            'number' => posts_per_page(),
            'term_slug' => $term_slug
        ]);
        return view("frontend.archive", ['posts' => $post, 'slug' => 'term_slug', 'term' => [
            'term_slug' => $term_slug,
            'taxonomy' => 'Category'
        ]]);
    }

    public function viewBlog($page = 1)
    {
        $postModel = new Post();
        $post = $postModel->getAllPosts([
            'page' => $page,
            'orderby' => 'post_id',
            'order' => 'desc',
            'number' => posts_per_page()
        ]);
        return view("frontend.archive", ['posts' => $post]);
    }

    public function viewPost(Request $request, $post_id, $post_slug = null)
    {
        $post = $this->getById($post_id, true);

        if (is_null($post) || !$post || $post->status != 'publish') {
            return view('frontend.404');
        } else {
            return view('frontend.blog.default');
        }
    }

    public function _deletePostAction(Request $request)
    {
        $post_id = request()->get('serviceID');
        $post_encrypt = request()->get('serviceEncrypt');
        $delete_type = request()->get('type');

        if (!hh_compare_encrypt($post_id, $post_encrypt)) {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This post is invalid')
            ], true);
        }
        $post = new Post();
        $postObject = $post->getById($post_id);

        if (!empty($postObject) && is_object($postObject)) {
            $deleted = $post->deletePost($post_id);

            if ($deleted) {
                $res = [
                    'status' => 1,
                    'title' => __('System Alert'),
                    'message' => __('This Post is deleted')
                ];
                if ($delete_type == 'in-post') {
                    $res['redirect'] = dashboard_url('all-post');
                } else {
                    $res['reload'] = true;
                }
                $this->sendJson($res, true);
            } else {
                $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not delete this post')
                ], true);
            }
        } else {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This Post is invalid')
            ], true);
        }
    }

    public function _editPostAction()
    {

        $post_id = request()->get('postID');
        $post_encrypt = request()->get('postEncrypt');
        if (!hh_compare_encrypt($post_id, $post_encrypt)) {
            $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This post is invalid')
            ], true);
        }
        $post = new Post();
        $postObject = $post->getById($post_id);

        if (!empty($postObject) && is_object($postObject)) {
            $post_title = set_translate('post_title');
            $post_content = set_translate('post_content');
            $thumbnail_id = request()->get('thumbnail_id');
            $post_status = request()->get('post_status');
            $post_slug = request()->get('post_slug');
            $author = request()->get('author', get_current_user_id());

            if (empty($post_slug)) {
                $post_slug = $postObject->post_slug;
            }

            if ($post_title) {
                $data = [
                    'post_title' => $post_title,
                    'post_content' => $post_content,
                    'thumbnail_id' => $thumbnail_id,
                    'author' => $author,
                    'status' => $post_status
                ];

                $post_slug_convert = Str::slug($post_slug, '-');
                if ($post_slug_convert != $postObject->post_slug) {
                    $data['post_slug'] = render_service_slug($post_title, $post_slug, 'post');
                }

                $updated = $post->updatePost($data, $post_id);

                /* Category update */
                $category = request()->get('post_category', []);
                if (is_array($category)) {
                    $termRelation = new TermRelation();
                    $termRelation->deleteRelationByServiceID($post_id, 'post-category');
                    if (!empty($category)) {
                        foreach ($category as $termID) {
                            $termRelation->createRelation($termID, $post_id);
                        }
                    }
                }

                //Tags Update
                $termRelation = new TermRelation();
                $termRelation->deleteRelationByServiceID($post_id, 'post-tag');
                $tags = request()->get('post_tag', '');
                if (!empty($tags)) {
                    $tags = json_decode($tags);
                    $term = new Term();
                    $taxonomy = new Taxonomy();
                    $taxObject = $taxonomy->getByName('post-tag');
                    if (!empty($tags)) {
                        foreach ($tags as $k => $v) {
                            if (is_multi_language()) {
                                $term_exists = $term->getByName($v->value, 'post-tag', true);
                            } else {
                                $term_exists = $term->getByName($v->value, 'post-tag');
                            }
                            if (!empty($term_exists)) {
                                $termRelation->createRelation($term_exists->term_id, $post_id);
                            } else {
                                $new_term = $term->createTerm([
                                    'term_title' => $v->value,
                                    'term_name' => Str::slug($v->value),
                                    'term_description' => '',
                                    'term_image' => '',
                                    'term_icon' => '',
                                    'taxonomy_id' => $taxObject->taxonomy_id,
                                    'created_at' => time(),
                                ]);
                                $termRelation->createRelation($new_term, $post_id);
                            }
                        }
                    }
                }

                if (!is_null($updated)) {
                    $this->sendJson([
                        'status' => 1,
                        'title' => __('System Alert'),
                        'message' => __('Updated Successfully'),
                        'reload' => true
                    ], true);
                } else {
                    $this->sendJson([
                        'status' => 0,
                        'title' => __('System Alert'),
                        'message' => __('Can not update this post')
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
                'message' => __('This post is invalid')
            ], true);
        }
    }

    public function _editPost($post_id = '')
    {
        $folder = $this->getFolder();
        if (!empty($post_id)) {
            $post = new Post();
            $currentPost = $post->getById($post_id);
            if (!empty($currentPost)) {
                return view("dashboard.screens.{$folder}.post-add-new", ['bodyClass' => 'hh-dashboard', 'post_id' => $post_id, 'current_post' => $currentPost]);
            }
        }
        return view("dashboard.components.not-found", ['bodyClass' => 'hh-dashboard']);
    }

    public function _addNewPostAction(Request $request)
    {
        $post_title = set_translate('post_title');
        $post_content = set_translate('post_content');
        $thumbnail_id = request()->get('thumbnail_id');
        $post_status = request()->get('post_status');
        $post_slug = request()->get('post_slug');
        $author = request()->get('author', get_current_user_id());

        if ($post_title) {
            $data = [
                'post_title' => $post_title,
                'post_content' => $post_content,
                'thumbnail_id' => $thumbnail_id,
                'post_slug' => render_service_slug($post_title, $post_slug, 'post'),
                'status' => $post_status,
                'author' => (int)$author,
                'created_at' => time()
            ];

            $post = new Post();

            $newPost = $post->createPost($data);

            /* Term update */
            $category = request()->get('post_category', []);
            if (is_array($category) && !empty($category)) {
                $termRelation = new TermRelation();
                foreach ($category as $termID) {
                    $termRelation->createRelation($termID, $newPost);
                }
            }

            //Tags Update
            $termRelation = new TermRelation();
            $tags = request()->get('post_tag', '');
            if (!empty($tags)) {
                $tags = json_decode($tags);
                $term = new Term();
                $taxonomy = new Taxonomy();
                $taxObject = $taxonomy->getByName('post-tag');
                if (!empty($tags)) {
                    foreach ($tags as $k => $v) {
                        $term_exists = $term->getByName($v->value, 'post-tag');
                        if (!empty($term_exists)) {
                            $termRelation->createRelation($term_exists->term_id, $newPost);
                        } else {
                            $new_term = $term->createTerm([
                                'term_title' => $v->value,
                                'term_name' => Str::slug($v->value),
                                'term_description' => '',
                                'term_image' => '',
                                'term_icon' => '',
                                'taxonomy_id' => $taxObject->taxonomy_id,
                                'created_at' => time(),
                            ]);
                            $termRelation->createRelation($new_term, $newPost);
                        }
                    }
                }
            }

            if ($newPost) {
                $this->sendJson([
                    'status' => 1,
                    'title' => __('System Alert'),
                    'message' => __('Created Successfully'),
                    'redirect' => dashboard_url('edit-post/' . $newPost)
                ], true);
            } else {
                $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not create this post')
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

    public function _addCreatePostButton()
    {
        $screen = current_screen();
        if ($screen == 'all-post') {
            echo view('dashboard.components.add-post')->render();
        }
    }

    public function _allPost($page = 1)
    {

        $folder = $this->getFolder();
        $search = request()->get('_s');
        $orderBy = request()->get('orderby', 'post_id');
        $order = request()->get('order', 'desc');
        $status = request()->get('status', '');

        $postModel = new Post();
        $allPosts = $postModel->getAllPosts(
            [
                'search' => $search,
                'page' => $page,
                'orderby' => $orderBy,
                'order' => $order,
                'status' => $status
            ]
        );
        return view("dashboard.screens.{$folder}.post", ['bodyClass' => 'hh-dashboard', 'allPosts' => $allPosts]);
    }

    public function _addNewPost()
    {
        $folder = $this->getFolder();
        return view("dashboard.screens.{$folder}.post-add-new", ['bodyClass' => 'hh-dashboard', 'post_id' => '-1']);
    }

    public function getById($post_id, $global = false)
    {
        $post_object = new Post();
        $post_item = $post_object->getById($post_id);
        if(!is_null($post_item)){
            $post_item = $this->setup_post_data($post_item);
        }
        if ($global) {
            global $post;
            $post = $post_item;
        }

        return $post_item;
    }

    public function getByName($post_name, $global = false)
    {
        $post_object = new Post();
        $post_item = $post_object->getByName($post_name);
        if(!is_null($post_item)){
            $post_item = $this->setup_post_data($post_item);
        }
        if ($global) {
            global $post;
            $post = $post_item;
        }

        return $post_item;
    }


    public function setup_post_data($post)
    {
        return $this->_storeData($post);
    }

    private function _storeData($post)
    {
        // can add more data for $post

        return $post;
    }

    public function getAllPosts($data = [])
    {
        $post = new Post();
        return $post->getAllPosts($data);
    }

    public function listOfPosts($data = [])
    {
        $post = new Post();
        return $post->listOfPosts($data);
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
