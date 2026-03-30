<?php

namespace Ycookies\MiniappManager\Http\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Ycookies\MiniappManager\Http\Forms\MiniappConfigForm;
use Ycookies\MiniappManager\Models\MiniappConfig;
use Ycookies\MiniappManager\Models\MiniprogramPage;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Card;
use Dcat\Admin\Widgets\Form as WidgetForm;
use Illuminate\Http\Request;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Widgets\Alert;
use Dcat\Admin\Widgets\Form as WidgetsForm;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Tab;
use Ycookies\MiniappManager\Http\Renderable\MinappPageQrcode;
use Ycookies\MiniappManager\Http\Renderable\MiniprogramPageAddForm;
use Ycookies\MiniappManager\Http\Renderable\MiniprogramPageEditForm;
use Ycookies\MiniappManager\Actions\Grid\QrcodeJumpPublish;
use Ycookies\MiniappManager\Actions\Grid\DeleteJumpQRCode;
use Ycookies\MiniappManager\Actions\Form\ActionJumpQRCode;
use Ycookies\MiniappManager\Repositories\MiniJumpQRCode;
use Ycookies\MiniappManager\Models\MiniprogramQrcode;

class WxMinappController extends Controller
{
    public $platform;
    public $nav_menu = [
        '1' => '基础配置',
        '2' => '支付配置',
        '3' => '页面管理',
        '4' => '模板消息',
        '5' => '小程序二维码',
    ];
    public function index(Content $content, string $platform)
    { 
        $this->platform = $platform;
                // 解决row col 间距
        Admin::style(<<<CSS
                            .no-gutters>.col, .no-gutters>[class*=col-] {
                            padding-left: 5px !important;
                        }
                        .content .content-wrapper{
                            padding:7rem .5rem 0 !important;
                        }
                        CSS            );

        return $content
            ->header('小程序管理')
            ->description('微信小程序')
            ->breadcrumb(['text' => '小程序管理', 'uri' => ''])
            ->row(function(Row $row) {
                $row->noGutters();// 无间距
                $row->column(2,  $this->cbox());

                $row->column(10, $this->pageMain());
            });
    }
    public function cbox($sc_id = 1){
        $nav_menu = $this->nav_menu;
        $datas = Request()->all();
        $hangzu_id = !empty($datas['hangzu_id'])? $datas['hangzu_id']:'';
        $hangzulist  = $nav_menu;
        $hzhtml = "<ul class='list-group list-group-flush'>";
        foreach ($hangzulist as $key => $items){
            $class = '';
            if(!empty($hangzu_id)){
                if($key == $hangzu_id){
                    $class = 'class="text-danger"';
                }
            }else{
                if($key == 1){
                    $class = 'class="text-danger"';
                }
            }

            $hzhtml .= '<li class="list-group-item"><a '.$class.' href="/admin/miniapp-manager/wechat/config?hangzu_id='.$key.'">'.$items.'</a></li>';
        }
        //$hzhtml .= '<li class="list-group-item"><a href="/merchant/wxgzh?&sc_id='.$sc_id.'" target="_blank">水电费</a></li>';
        $hzhtml .= '</ul>';
        $box = new Box('操作项', $hzhtml);
        //$box->collapsable();
        return $box;
    }

    // 页面
    public function pageMain() {
                $data = [];
        $tab  = Tab::make();
        $datas = Request()->all();
        $nav_menu = $this->nav_menu;
        $hangzu_id = !empty($datas['hangzu_id'])? $datas['hangzu_id']:'';
        if($hangzu_id == 1 || $hangzu_id == ''){
                $tab->add('微信小程序[自营模式]', $this->tab1());
        }
        if($hangzu_id == 2){
                $tab->add('小程序支付配置', $this->tab2());
        }
        if($hangzu_id == 3){
                $tab->add('页面管理', $this->tab3());
        }
        if($hangzu_id == 4){
                $tab->add('模板消息', $this->tab4());
        }
        if($hangzu_id == 5){
                $tab->add('小程序二维码', $this->tab5());
        }

        return $tab->withCard();
    }
    /**
     * 配置表单页
     */
    public function tab1()
    {
        $platform = $this->platform;

        if (!isset(MiniappConfig::$platforms[$platform])) {
            //return $content->title('错误')->body('不支持的平台类型');
        }

        $config = MiniappConfig::getByPlatform($platform);
        $platformName = MiniappConfig::$platforms[$platform];

        $form = $this->MiniappConfigForm($platform);

        $card = Card::make($platformName . ' 配置',$form);
        // 注入验证按钮的 JS
        Admin::script($this->verifyScript());
        return $card;
    }
    // 小程序支付配置
    public function tab2() {
        $formdata = admin_setting_group('wx_pay_config');
        $form     = new WidgetsForm($formdata);
        $form->action('web-config/save');
        $form->confirm('确认已经填写完整了吗？');
        $form->hidden('group_name')->value('wx_pay_config');
        $form->html('<h3>微信支付</h3>');
        $form->text('app_id', '公众号app_id')->required();
        $form->text('secret', '公众号secret')->required();
        $form->text('mch_id', '商户号')->required();
        $form->text('pay_key', '支付密钥');
        $form->text('cert_path', '证书 cert_path');
        $form->text('key_path', '证书key_path');
        $form->text('platform_pub_id', '微信支付公钥ID');
        $form->text('platform_pub_cert', '微信支付公钥')->help('公钥文件路径');
        $form->text('notify_url', '异步通知地址');
        $form->photo('photo','图片')
            ->nametype('datetime')
            ->remove(true)
            ->help('单图，可删除');
        $form->disableResetButton();
        $card = Card::make('微信支付参数', $form);
        return $card;
    }

    // 小程序页面管理
    public function tab3() {
        $grid = Grid::make(new MiniprogramPage(), function (Grid $grid) {
            $grid->model()->where(['hotel_id' => 143,'miniapp'=>'wx'])->orderBy('id', 'DESC');
            $grid->column('type','所属分类')->using(MiniprogramPage::Type_arr);
            $grid->column('name','名称');
            //$grid->column('open_type');
            //$grid->column('icon','图标');
            $grid->column('path','路径');
            $grid->column('qrcode','二维码')->modal('查看',function ($modal) {
                // 设置弹窗标题
                $modal->title('页面路径二维码');
                // 自定义图标
                $modal->icon('fa fa-qrcode');
                //$modal->body('这是二维码'.$this->name . '-'.$this->path);
                //$card = new Card(null, '这是二维码'.$this->name . '-'.$this->path);
                return MinappPageQrcode::make()->payload(['hotel_id'=> 43,'name'=>$this->name,'path'=> $this->path]);
            });;
            $grid->column('status','状态')->bool();

            $modal1 = Modal::make()
                ->lg()
                ->title('新增页面')
                ->body(MiniprogramPageAddForm::make())
                ->button('<button class="btn btn-primary"><i class="feather icon-plus"></i> 新增页面</span></button>');
            $grid->tools($modal1);

            $grid->quickSearch(['name', 'path'])->placeholder('页面名称,路径');
            $grid->setActionClass(Grid\Displayers\Actions::class);
            $grid->actions(function ($actions) {
                // 去掉删除
                $actions->disableDelete();
                // 去掉编辑
                $actions->disableEdit();
                $actions->disableView();

                $modal = Modal::make()
                    ->lg()
                    ->title('修改页面信息')
                    ->body(MiniprogramPageEditForm::make()->payload($actions->row->toArray()))
                    ->button('<i class="feather icon-edit-1 grid-action-icon tips" data-title="修改"></i>');
                $actions->append($modal);
            });
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->addTableClass(['table-text-center']);
            $grid->selector(function (Grid\Tools\Selector $selector) {
                $selector->select('type', '所属分类', MiniprogramPage::Type_arr);
            });
        });

        $card1 = Card::make('', $grid);
        return $card1;
    }

    // 小程序模板消息
    public function tab4() {
        $formdata = admin_setting_group('wx_minapp_msg_tpl');
        // $formdata = HotelSetting::getlists($field,Admin::user()->hotel_id);
        $form1 = new WidgetsForm($formdata);
        $form1->width(8,4);
        $form1->text('wx_minapp_msg_tpl_pay_success', '订单支付成功通知 模板ID:<br/> wx_minapp_msg_tpl_success')->help('类目: 住宿服务');
        $form1->disableResetButton();
        $form1->disableSubmitButton();
        $tips_html = "<ul><li>以下订阅模板消息的设置来自于小程序后台</li></ul>";
        $alert     = Alert::make($tips_html, '说明')->info();

        $card1 = Card::make('', $alert.$form1);
        return $card1;
    }

    // 小程序码管理
    public function tab5() {
        $grid = Grid::make(new MiniprogramQrcode(), function (Grid $grid) {
            $grid->model()->where(['platform'=> 'wechat'])->orderBy('id', 'desc');
            $grid->column('page_path','小程序页面');
            $grid->column('qrcode_url','二维码')->image(true, 100, 100);
            $grid->column('status','状态')->using(['0'=>'未生成','1'=> '已生成']);

            $modal = Modal::make()
                ->lg()
                ->title('生成小程序码')
                ->body(ActionJumpQRCode::make())
                ->button('<button class="btn btn-primary"> <i class="feather icon-plus"></i> 生成小程序码</button>');

            $grid->rightTools($modal);
            $grid->disableCreateButton();

            $grid->disableRowSelector();
            $grid->setActionClass(Grid\Displayers\Actions::class);
            $grid->actions(function ($actions) {
                // 去掉删除
                $actions->disableDelete();
                // 去掉编辑
                $actions->disableEdit();
                $actions->disableView();
                //if($actions->row->state == 1){
                    $actions->append(QrcodeJumpPublish::make());
                //}
                $actions->append(DeleteJumpQRCode::make());

                $modal = Modal::make()
                    ->lg()
                    ->title('修改二维码规则')
                    ->body(ActionJumpQRCode::make()->payload($actions->row->toArray()))
                    ->button('&nbsp; 修改');
                $actions->append($modal);

            });

        });
        //$alert = Alert::make('扫普通链接二维码打开小程序 规则管理 <a href="https://developers.weixin.qq.com/miniprogram/introduction/qrcode.html" target="_blank">查看官方文档</a>','提示')->info();
        $card1 = Card::make('',$grid);
        return $card1;
    }
    // 表单
    public function MiniappConfigForm(string $platform)
    {
        $labels = $this->platformLabels($platform);
        $config = MiniappConfig::getByPlatform($platform);
        $isVerified = $config && $config->is_verified;
        $form = WidgetForm::make($config);
        $form->action(admin_url('miniapp-manager/' . $platform . '/config/save'));
        $verifiedBadge = $isVerified
            ? '<span class="badge badge-success">已验证</span>'
            : '<span class="badge badge-warning">未验证</span>';

        $form->html($verifiedBadge, '状态');

        $form->text('app_id', $labels[0])->required();
        $form->text('app_secret', $labels[1])->required();
        $form->text('token', $labels[2]);
        $form->text('encoding_aes_key', $labels[3]);

        $verifyUrl = admin_url('miniapp-manager/' . $platform . '/config/verify');
        $form->html(
            '<button type="button" class="btn btn-success btn-verify-config" data-url="' . $verifyUrl . '">验证配置</button>
            <span class="ml-2 text-muted">保存后点击验证，通过平台 API 校验配置是否正确</span>',
            ' '
        );
        return $form;
        
    }
    protected function platformLabels($platform): array
    {
        return match ($platform) {
            'alipay' => ['AppID', '应用私钥', 'Token（可选）', 'AES密钥（可选）'],
            default  => ['AppID', 'AppSecret', 'Token（消息推送，可选）', 'EncodingAESKey（可选）'],
        };
    }
    
    public function save($platform, Request $request)
    {
        if (!isset(MiniappConfig::$platforms[$platform])) {
            return (new WidgetForm())->response()->error('不支持的平台类型');
        }

        $appId     = $request->input('app_id', '');
        $appSecret = $request->input('app_secret', '');

        if (empty($appId) || empty($appSecret)) {
            return (new WidgetForm())->response()->error('AppID 和 AppSecret 不能为空');
        }

        $config = MiniappConfig::getByPlatform($platform);

        $saveData = [
            'platform'         => $platform,
            'name'             => MiniappConfig::$platforms[$platform],
            'app_id'           => $appId,
            'app_secret'       => $appSecret,
            'token'            => $request->input('token', ''),
            'encoding_aes_key' => $request->input('encoding_aes_key', ''),
            'is_verified'      => 0,
            'is_enabled'       => 0,
        ];

        if ($config) {
            $config->update($saveData);
        } else {
            MiniappConfig::create($saveData);
        }

        return (new WidgetForm())->response()->success('配置已保存，请点击「验证配置」按钮验证');
    }


    /**
     * 验证配置（调用对应平台API）
     */
    public function verify(string $platform): JsonResponse
    {
        $config = MiniappConfig::getByPlatform($platform);

        if (!$config || empty($config->app_id) || empty($config->app_secret)) {
            return response()->json(['status' => false, 'message' => '请先保存配置']);
        }

        try {
            $result = $this->verifyPlatformConfig($platform, $config);
        } catch (\Throwable $e) {
            $config->update(['is_verified' => 0, 'is_enabled' => 0]);
            return response()->json(['status' => false, 'message' => '验证失败: ' . $e->getMessage()]);
        }

        if ($result['success']) {
            $config->update(['is_verified' => 1, 'is_enabled' => 1]);
            return response()->json(['status' => true, 'message' => '✅ 验证通过，配置已启用']);
        }

        $config->update(['is_verified' => 0, 'is_enabled' => 0]);
        return response()->json(['status' => false, 'message' => '验证失败: ' . ($result['error'] ?? '未知错误')]);
    }

    /**
     * 按平台调用API验证
     */
    protected function verifyPlatformConfig(string $platform, MiniappConfig $config): array
    {
        $appId     = $config->app_id;
        $appSecret = $config->getDecryptedSecret();

        return match ($platform) {
            MiniappConfig::PLATFORM_WECHAT => $this->verifyWechat($appId, $appSecret),
            MiniappConfig::PLATFORM_ALIPAY => $this->verifyAlipay($appId, $appSecret),
            MiniappConfig::PLATFORM_DOUYIN => $this->verifyDouyin($appId, $appSecret),
            default => ['success' => false, 'error' => '不支持的平台'],
        };
    }

    protected function verifyWechat(string $appId, string $appSecret): array
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?' . http_build_query([
            'grant_type' => 'client_credential',
            'appid'      => $appId,
            'secret'     => $appSecret,
        ]);

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (!empty($data['access_token'])) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => $data['errmsg'] ?? '获取 access_token 失败'];
    }

    protected function verifyAlipay(string $appId, string $appSecret): array
    {
        return ['success' => true];
    }

    protected function verifyDouyin(string $appId, string $appSecret): array
    {
        return ['success' => true];
    }

    /**
     * 验证按钮 JS
     */
    protected function verifyScript(): string
    {
        return <<<'JS'
$(document).on('click', '.btn-verify-config', function() {
    var btn = $(this);
    var url = btn.data('url');
    btn.prop('disabled', true).text('验证中...');
    $.ajax({
        url: url,
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': Dcat.token },
        dataType: 'json',
        success: function(data) {
            btn.prop('disabled', false).text('验证配置');
            if (data.status) {
                Dcat.success(data.message);
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                Dcat.error(data.message);
            }
        },
        error: function() {
            btn.prop('disabled', false).text('验证配置');
            Dcat.error('验证请求失败');
        }
    });
});
JS;
    }
}
