<?php

namespace Ycookies\MiniappManager\Http\Controllers\Wechat;

use App\Models\MemberOauth;
use App\Models\MemberUser;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserController extends AdminController
{
    protected $title = '微信小程序用户';

    protected function grid()
    {
        return Grid::make(MemberOauth::with('memberUser'), function (Grid $grid) {
            $grid->model()->where('type', MemberOauth::WX_MINI)->orderBy('id', 'desc');

            $grid->column('id')->sortable();
            $grid->column('memberUser.avatar', '头像')->image('', 40, 40);
            $grid->column('memberUser.username', '用户名');
            $grid->column('memberUser.nickname', '昵称');
            $grid->column('open_id', 'OpenID')->copyable()->limit(20);
            $grid->column('union_id', 'UnionID')->limit(20);
            $grid->column('memberUser.phone', '手机号');
            $grid->column('memberUser.status', '状态')
                ->using(MemberUser::$status_arr)
                ->label([0 => 'danger', 1 => 'success']);
            $grid->column('created_at', '绑定时间')->date('Y-m-d H:i');

            $grid->quickSearch(['open_id', 'info_nick']);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('open_id', 'OpenID');
                $filter->like('info_nick', '昵称');
                $filter->where('phone', function ($query) {
                    $query->whereHas('memberUser', function ($q) {
                        $q->where('phone', 'like', "%{$this->input}%");
                    });
                }, '手机号');
            });

            $grid->disableCreateButton();
            $grid->disableEditButton();
        });
    }

    protected function detail($id)
    {
        return Show::make($id, MemberOauth::with('memberUser'), function (Show $show) {
            $show->field('id');
            $show->field('open_id', 'OpenID');
            $show->field('union_id', 'UnionID');
            $show->field('info_nick', '授权昵称');
            $show->field('info_avatar', '授权头像')->image();
            $show->field('memberUser.username', '用户名');
            $show->field('memberUser.phone', '手机号');
            $show->field('memberUser.nickname', '昵称');
            $show->field('memberUser.status', '状态')->using(MemberUser::$status_arr);
            $show->field('created_at', '绑定时间');
            $show->field('updated_at', '更新时间');
        });
    }
}
