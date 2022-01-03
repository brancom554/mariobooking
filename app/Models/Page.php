<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sentinel;

class Page extends Model
{
    protected $table = 'page';
    protected $primaryKey = 'post_id';


    public function getById($home_id)
    {
        $post = DB::table($this->table)->where('post_id', $home_id)->get()->first();
        return (!empty($post) && is_object($post)) ? $post : null;
    }

    public function getByName($home_name)
    {
        $post = DB::table($this->table)->where('post_slug', $home_name)->get()->first();
        return (!empty($post) && is_object($post)) ? $post : null;
    }

    public function getNumberSuffixes($slug)
    {
        $result = DB::select("SELECT COUNT(*) AS Number_Suffixes FROM {$this->table} WHERE  post_slug  = '{$slug}'");

        return $result[0]->Number_Suffixes;
    }

    public function deletePage($post_id)
    {
        return DB::table($this->table)->where('post_id', $post_id)->delete();
    }

	public function updateMultiPage($data, $post_id)
	{
		return DB::table($this->getTable())->whereIn('post_id', $post_id)->update($data);
	}

    public function updatePage($data, $post_id)
    {
        return DB::table($this->getTable())->where('post_id', $post_id)->update($data);
    }

    public function createPage($data = [])
    {
        return DB::table($this->getTable())->insertGetId($data);
    }

    public function getAllPages($data = [])
    {
        $default = [
            'search' => '',
            'page' => 1,
            'orderby' => 'post_id',
            'order' => 'desc',
            'status' => '',
	        'number' => posts_per_page()
        ];
        $data = wp_parse_args($data, $default);

        $number = $data['number'];

        $sql = DB::table($this->getTable())->selectRaw("SQL_CALC_FOUND_ROWS page.*")
            ->orderBy($data['orderby'], $data['order']);
		if($number != -1){
			$offset = ($data['page'] - 1) * $number;
			$sql->limit($number)->offset($offset);
		}
        if (!empty($data['search'])) {
            $data['search'] = esc_sql($data['search']);
            $search = esc_sql($data['search']);
            $sql->whereRaw("page.post_id = '{$search}' OR page.post_title LIKE '%{$search}%'");
        }

        if(!empty($data['status']) && in_array($data['status'], ['publish', 'draft'])){
            $sql->where('status', $data['status']);
        }
        $results = $sql->get();

        $count = DB::select("SELECT FOUND_ROWS() as `row_count`")[0]->row_count;

        return [
            'total' => $count,
            'results' => $results
        ];

    }
}
