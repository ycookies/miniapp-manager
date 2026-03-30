<?php

namespace Ycookies\MiniappManager\Repositories;

use Dcat\Admin\Repositories\Repository;
use Dcat\Admin\Grid;
use Illuminate\Pagination\LengthAwarePaginator;
use Dcat\Admin\Admin;

class MiniJumpQRCode extends Repository
{
    protected $keyName = 'prefix';
    /**
     * 定义主键字段名称
     *
     * @return string
     */
    public function getPrimaryKeyColumn()
    {
        return 'prefix';
    }
    /**
     * Model.
     *
     * @var string
     */
    // protected $eloquentClass = Model::class;


    public function get(Grid\Model $model){
        $page     = $model->getCurrentPage();
        $pageSize = $model->getPerPage();

        $collection = $this->all();

        return $model->makePaginator(
            100,
            $collection
        );
    }

    public function all(){
        $openPlatform = app('wechat.open');
        $miniProgram = $openPlatform->hotelMiniProgram(Admin::user()->hotel_id);
        $res = $miniProgram->setting->getJumpQRCode();
        if(isset($res['errcode']) && $res['errcode'] == 0){
            return $res['rule_list'];
        }
        return [];

    }
}
