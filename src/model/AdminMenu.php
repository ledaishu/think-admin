<?php

namespace suframe\thinkAdmin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class AdminMenu extends Model
{
    public function getAppNameZhAttr($value, $data)
    {
        if(!$data['app_name']){
            return '';
        }
        return AdminApps::where('app_name', $data['app_name'])->field('title')->value('title');
    }
   public function getShowMenuZhAttr($value, $data)
    {
        return $data['show_menu'] == 1 ? '是' : '否';
    }

    public function getChildCountAttr($value, $data)
    {
        if(!$data['app_name']){
            return '';
        }
        return AdminMenu::where('parent_id', $data['id'])->count();
    }

    public function getHasChildAttr($value, $data)
    {
        return !!$this->getChildCountAttr($value, $data);
    }

    /**
     * 下拉options
     * @param int $parent_id
     * @param bool $hasAll
     * @param string $key
     * @return array
     */
    public static function buildOptions($parent_id = 0, $hasAll = false, $key = 'value')
    {
        try {
            $model = AdminMenu::order('order', 'desc');
            if ($parent_id !== 'all') {
                $model->where('parent_id', $parent_id);
            }
            $data = $model->field(['id', 'title'])->select();

            $options = [];
            if ($hasAll) {
                $options[] = [$key => "0", 'label' => "请选择"];
            }
            foreach ($data as $item) {
                $options[] = [$key => $item['id'], 'label' => $item['title']];
            }
            return $options;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param int $pid
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function buildTree($pid = 0)
    {
        $rs = [];
        $data = AdminMenu::order('order', 'desc')
            ->where('parent_id', $pid)
            ->select();
        if (!$data) {
            return $data;
        }
        foreach ($data as $key => $item) {
            $children = static::buildTree($item['id']);
            $rs[$key] = [
                'id' => $item['id'],
                'label' => $item['title'],
                'children' => $children
            ];
        }
        return $rs;
    }

}
