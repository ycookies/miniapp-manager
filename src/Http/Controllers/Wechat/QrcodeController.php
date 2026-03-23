<?php

namespace Ycookies\MiniappManager\Http\Controllers\Wechat;

use Ycookies\MiniappManager\Models\MiniappQrcode;
use Ycookies\MiniappManager\Services\WechatMiniappService;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Storage;

class QrcodeController extends AdminController
{
    protected $title = '微信小程序码';

    protected function grid()
    {
        return Grid::make(new MiniappQrcode(), function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');

            $grid->column('id')->sortable();
            $grid->column('scene', '场景值');
            $grid->column('page', '页面路径');
            $grid->column('width', '宽度');
            $grid->column('file_path', '小程序码')->image('', 80, 80);
            $grid->column('remark', '备注');
            $grid->column('created_at', '生成时间')->date('Y-m-d H:i');

            $grid->quickSearch(['scene', 'page', 'remark']);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('scene', '场景值');
                $filter->like('page', '页面路径');
            });
        });
    }

    protected function detail($id)
    {
        return Show::make($id, new MiniappQrcode(), function (Show $show) {
            $show->field('id');
            $show->field('scene', '场景值');
            $show->field('page', '页面路径');
            $show->field('width', '宽度');
            $show->field('file_path', '小程序码')->image();
            $show->field('remark', '备注');
            $show->field('created_at', '生成时间');
        });
    }

    protected function form()
    {
        return Form::make(new MiniappQrcode(), function (Form $form) {
            $form->text('scene', '场景值')->required()->help('最多32个可见字符');
            $form->text('page', '页面路径')->help('小程序页面路径，如 pages/index/index');
            $form->number('width', '宽度')->default(430)->min(280)->max(1280)->help('二维码宽度 280-1280');
            $form->text('remark', '备注');

            $form->saving(function (Form $form) {
                try {
                    $service = new WechatMiniappService();
                    $content = $service->getUnlimitedQrcode(
                        $form->scene,
                        $form->page ?: '',
                        (int) $form->width ?: 430
                    );

                    $filename = 'miniapp_qrcodes/' . date('Ymd') . '/' . uniqid() . '.png';
                    Storage::disk('public')->put($filename, $content);
                    $form->file_path = 'storage/' . $filename;
                } catch (\Throwable $e) {
                    return $form->response()->error('生成小程序码失败: ' . $e->getMessage());
                }
            });
        });
    }
}
