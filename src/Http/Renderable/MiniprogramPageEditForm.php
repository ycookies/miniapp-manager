<?php
namespace Ycookies\MiniappManager\Http\Renderable;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Ycookies\MiniappManager\Models\MiniprogramPage;

class MiniprogramPageEditForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function handle(array $input)
    {
        $updata = [
            'type' => $input['type'] ?? '',
            'path' => $input['path'] ?? '',
            'name' => $input['name'] ?? '',

        ];
        MiniprogramPage::where(['id'=> $input['id']])->update($updata);
        return $this->response()->success('保存成功')->refresh();
    }

    public function default()
    {
        return [
            // 展示上个页面传递过来的值
            'id' => $this->payload['id'] ?? '',
            'hotel_id' => $this->payload['hotel_id'] ?? '',
            'type' => $this->payload['type'] ?? '4',
            'path' => $this->payload['path'] ?? '',
            'name' => $this->payload['name'] ?? '',
        ];
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

    // 保存小程序页面路径
    public function savePages($hotel_id){
        $json = '{"pages":[{"path":"pages/index/index","style":{"navigationBarTitleText":"酒店预定","navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756","enablePullDownRefresh":false}},{"path":"pages/order/index","style":{"navigationBarTitleText":"订单","navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756","enablePullDownRefresh":true}},{"path":"pages/news/news","style":{"navigationBarTitleText":"消息中心","navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756","enablePullDownRefresh":true}},{"path":"pages/my/my","style":{"navigationBarTitleText":"用户中心","navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756","navigationStyle":"custom"}},{"path":"pages/login/login","style":{"navigationBarTitleText":"用户登陆","navigationStyle":"custom","navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"pages/login/register","style":{"navigationBarTitleText":"用户注册","navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"pages/income/index","style":{"navigationBarTitleText":"收益"}},{"path":"pages/withdrawal/index","style":{"navigationBarTitleText":"提现","navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756","app-plus":{"titleNView":{"buttons":[{"color":"#ffffff","fontSize":"17.5rpx","text":"提现记录 >"}]}}}},{"path":"pages/withdrawal/record","style":{"navigationBarTitleText":"提现记录","enablePullDownRefresh":true}},{"path":"pages/order/lists","style":{"navigationBarTitleText":"订单管理","navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756","enablePullDownRefresh":true}},{"path":"pages/order/detail","style":{"navigationBarTitleText":"订单详情","navigationBarTextStyle":"white","enablePullDownRefresh":false,"navigationBarBackgroundColor":"#101756"}},{"path":"pages/order/form","style":{"navigationBarTitleText":"客房预定","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"pages/order/pingjia","style":{"navigationBarTitleText":"住店评价"}},{"path":"pages/news/detail","style":{"navigationBarTitleText":"在线咨询","enablePullDownRefresh":false}},{"path":"pages/user/about","style":{"navigationBarTitleText":"关于我们","enablePullDownRefresh":false}},{"path":"pages/user/xieyi","style":{"navigationBarTitleText":"服务协议","navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756","enablePullDownRefresh":false}},{"path":"pages/user/membervip","style":{"navigationBarTitleText":"vip会员","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"pages/user/commoninfo","style":{"navigationBarTitleText":"常用信息","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"pages/user/personal","style":{"navigationBarTitleText":"个人资料","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}}],"subPackages":[{"root":"pages1","pages":[{"path":"pointsmall/index","style":{"navigationBarTitleText":"积分商城","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}}]},{"root":"pages2","pages":[{"path":"fuwu/jiaoxin","style":{"navigationBarTitleText":"预约叫醒","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"hotel/hotel_detail","style":{"navigationBarTitleText":"酒店简介","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"hotel/sheshi_fuwu","style":{"navigationBarTitleText":"酒店信息","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"hotel/pingjia","style":{"navigationBarTitleText":"住客评价","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"hotel/room_detail","style":{"navigationBarTitleText":"房型信息","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756","usingComponents":{"towxml":"/wxcomponents/towxml/towxml"}}},{"path":"article/lists","style":{"navigationBarTitleText":"列表","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"article/detail","style":{"navigationBarTitleText":"详情","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"help/lists","style":{"navigationBarTitleText":"列表","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"help/detail","style":{"navigationBarTitleText":"详情","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"topic/lists","style":{"navigationBarTitleText":"列表","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"topic/detail","style":{"navigationBarTitleText":"详情","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"webview/webview","style":{"navigationBarTitleText":"","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/coupon","style":{"navigationBarTitleText":"我的优惠券","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/hongbao","style":{"navigationBarTitleText":"我的红包","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/chuzhi","style":{"navigationBarTitleText":"储值中心","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/jifen","style":{"navigationBarTitleText":"积分历史","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/invoice","style":{"navigationBarTitleText":"我的发票","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/user_invoice","style":{"navigationBarTitleText":"我的发票","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/invoicing","style":{"navigationBarTitleText":"开具发票","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/quan_center","style":{"navigationBarTitleText":"领券中心","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/settings","style":{"navigationBarTitleText":"设置","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/pay_order_lists","style":{"navigationBarTitleText":"线下消费订单列表","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/pay_order_detail","style":{"navigationBarTitleText":"交易订单详情","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/apply_member","style":{"navigationBarTitleText":"加入酒店会员","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/lvkelist","style":{"navigationBarTitleText":"常用旅客","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/add_lvke","style":{"navigationBarTitleText":"添加常用旅客","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/user_level","style":{"navigationBarTitleText":"会员权益","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/user_center","style":{"navigationBarTitleText":"会员中心","enablePullDownRefresh":false,"navigationBarTextStyle":"white","navigationBarBackgroundColor":"#101756"}},{"path":"extend/share","style":{"navigationBarTitleText":"邀请好友"}},{"path":"extend/recharge","style":{"navigationBarTitleText":"充值"}},{"path":"extend/balance_log","style":{"navigationBarTitleText":"交易明细"}},{"path":"extend/set_pay_password","style":{"navigationBarTitleText":"设置支付密码"}},{"path":"extend/share_apply","style":{"navigationBarTitleText":"领取好友红包"}},{"path":"extend/trade_order","style":{"navigationBarTitleText":"向商家付款"}},{"path":"extend/parking_pay_car_cost","style":{"navigationBarTitleText":"停车缴费"}}]}]}';
        $json_arr = json_decode($json,true);
        $insdata = [];
        $pages = $json_arr['pages'];
        foreach ($pages as $items) {
            $insdata = [
                'hotel_id'=> $hotel_id,
                'miniapp'=> 'wx',
                'type' => '1',
                'name' => $items['style']['navigationBarTitleText'],
                'path' => $items['path'],
            ];
            MiniprogramPage::firstOrCreate(['hotel_id'=> $hotel_id,'path'=> $insdata['path']], $insdata);
        }
        if(!empty($json_arr['subPackages'])){
            $subpages = $json_arr['subPackages'];
            foreach ($subpages as $subPackages) {
                foreach ($subPackages['pages'] as $items) {
                    $insdata = [
                        'hotel_id'=> $hotel_id,
                        'miniapp'=> 'wx',
                        'type' => '4',
                        'name' => $items['style']['navigationBarTitleText'],
                        'path' => $subPackages['root'].'/'.$items['path'],
                    ];
                    MiniprogramPage::firstOrCreate(['hotel_id'=> $hotel_id,'path'=> $insdata['path']], $insdata);
                }
            }
        }
    }
}
