<?php
namespace Ycookies\MiniappManager\Http\Renderable;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Ycookies\MiniappManager\Models\MiniprogramPage;
use Dcat\Admin\Admin;
class MiniprogramPageAddForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function handle(array $input)
    {
        $hotel_id = Admin::user()->hotel_id;
        $insdata = [
            'hotel_id'=> $hotel_id,
            'miniapp'=> 'wx',
            'type' => $input['type'] ?? '4',
            'name' => $input['name'] ?? '',
            'path' => $input['path'] ?? '',
        ];
        MiniprogramPage::firstOrCreate(['hotel_id'=> $hotel_id,'path'=> $insdata['path']], $insdata);

        return $this->response()->success('保存成功')->refresh();
    }

    public function default()
    {
        return [];
    }

    public function form()
    {
        $this->hidden('id');
        $this->select('type','所属分类')->options(MiniprogramPage::Types_arr);
        $this->text('name','页面名称');
        $this->text('path','路径');
        //$this->disableSubmitButton();
        $this->disableResetButton();
    }
}
