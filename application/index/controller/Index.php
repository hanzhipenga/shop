<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use scws\pscws4;
use app\admin\model\Car as CarModel;
use app\admin\model\Cat as CatModel;
use app\admin\model\Type as TypeModel;
use app\admin\model\Attr as AttrModel;
use app\admin\model\Goods as GoodsModel;
use app\index\model\Person as PersonModel;
use app\index\validate\Person as PersonValidate;
use app\admin\model\City as CityModel;
use app\admin\model\Address as AddressModel;
use app\admin\model\Guigename as GuigenameModel;
use app\admin\model\Guigevalue as GuigevalueModel;
use app\admin\model\Goodsprice as GoodspriceModel;
class Index extends Base
{
    protected $is_check_login = ['shopping_car','lookorderform','showdetail','add_car','logout','del'];
    public function index()
    {
        $c = new CatModel();
        $cat = $c->where('pid',0)->select();
        $this->assign('cat',$cat);
        $parent = $c->where('sort_order',2)->select();
        $this->assign('parent',$parent);
        $son = $c->where('sort_order','>',2)->select();
        $this->assign('son',$son);
        return $this->fetch();
    }
    public function login()
    {
        return $this->fetch();
    }
    public function register()
    {
        return $this->fetch();
    }
    public function add()
    {
        $data = input('post.');
        $val = new PersonValidate();
        if(!$val->check($data)){
            $this->error($val->getError());
            exit;
        }
        $person = new PersonModel($data);
        $result = $person->allowField(true)->save();
        if($result){
            $this->success('注册成功', 'login');
        }else{
            $this->error('注册失败');
        }
    }
    public function check(){
        $data = input('post.');
        $person = new PersonModel();
        $result = $person->whereor('email',$data['name'])->whereor('tel',$data['name'])->find();
        if($result){
            if($result['password'] === $data['password']){
                session('name',$result['name']);
                session('person_id',$result['person_id']);
                $this->success('登录成功','index');
            }else{
                $this->error('密码不正确','login');
            }
        }else{
            $this->error('用户名不存在','login');
        }
    }
    public function lost(){
        return $this->fetch();
    }
    public function changePwd(){
        $data = input('post.');
        $val = new PersonValidate();
        if(!$val->check($data)){
            $this->error($val->getError());
            exit;
        }
        $person = new PersonModel($data);
        $result = $person->whereor('email',$data['account'])->whereor('tel',$data['account'])->find();
        if($result){
            $ret = $person->where('id', $result['id'])->update(['password'=>$data['password'],'repassword'=>$data['repassword']]);
            if($ret){
                $this->success('修改密码成功', 'login');
            }else{
                $this->error('修改密码失败', 'lost');
            }
        }else{
            $this->error('用户不存在','lost');
        }
    }
    public function goodslist(){
        $c = new CatModel();
        $one = $c->where('pid',0)->select();
        $this->assign('one',$one);
        $two = $c->where('sort_order',2)->select();
        $this->assign('two',$two);
        $son = $c->where('sort_order','>',2)->select();
        $this->assign('son',$son);
        return $this->fetch();
    }
    public function bypageshow(){
        $cat_id = input('get.cat_id');
        $c = new CatModel();
        $data = $c->where('cat_id',$cat_id)->find();
        $g = new GoodsModel();
        $a = new AttrModel();
        $attr = $a->where('cat_id',$cat_id)->field('attr_name,attr_value')->select();
        $this->assign('attr',$attr);
        if($data['sort_order'] == 2){
            $child = $c->where('pid',$cat_id)->field('cat_id')->select();
            $ids = array();
            $ids = array_column($child,'cat_id');
            $goods = $g->alias('a')->join('work_shop b','a.shop_id=b.shop_id','left')->where('cat_id','in',$ids)->where('up_goods',1)->paginate(32,false,['query'=>request()->param()]);
            $goods_id = $g->where('cat_id','in',$ids)->count();
            $this->assign('goods_id',$goods_id);
            $this->assign('goods',$goods);

        }else{
            $goods = $g->alias('a')->join('work_shop b','a.shop_id=b.shop_id','left')->where('cat_id',$cat_id)->where('up_goods',1)->paginate(32,false,['query'=>request()->param()]);
            $goods_id = $g->where('cat_id',$cat_id)->count();
            $this->assign('goods_id',$goods_id);
            $this->assign('goods',$goods);
        }
        
        return $this->fetch();
    }
    //购物车
    public function shopping_car(){
        session('id',null);
        session('guige',null);
        session('num',null);
        session('price',null);
        session('image',null);
        $name = input('get.name');
        $c = new CarModel();
        $car_goods = $c->alias('a')->join('work_goods b','a.goods_id=b.goods_id','left')->field('a.guige,a.num,b.goods_name,a.price,a.image,a.car_id')->where('name',$name)->select();
        $this->assign('car_goods',$car_goods);
        return $this->fetch();
    }
    //用户删除购物车中的商品
    public function del(){
        $car_id = input('get.car_id');
        $c = new CarModel();
        $ret = $c->destroy($car_id,true);
        if($ret){
            $this->success('删除成功','index');
        }else{
            $this->error('删除失败');
        }
    }
    //用户批量删除
    public function del_all(){
        $car_id = $_POST['arr'];
        $c = new CarModel();
        $ret = $c->where('car_id','in',$car_id)->delete(true);
        if($ret){
            return 1;
        }
    }
    //session记录商品id规格购买个数价格和图片，用户直接购买
    public function buy(){
        $guige = implode(',',$_POST['guige']);
        session('guige',$guige);
        session('num',$_POST['num']);
        session('image',$_POST['image']);
        session('price',$_POST['price']);
        return 1;
    }
    //用session获取购物车里选中的购物车id
    public function lookorder(){
        session('id',$_POST['id']);
        return 1;
    }
    //订单页面
    public function lookorderform(){
        $request = Request::instance();
        if($request->has('goods_id','get')){
            $goods_id = $_GET['goods_id'];
            $guige['guige'] = session('guige');
            $num['num'] = session('num');
            $price['price'] = session('price');
            $image['image'] = session('image');
            $g = new GoodsModel();
            $good = $g->where('goods_id',$goods_id)->field('goods_name')->find();
            $goods['goods_name'] = $good['goods_name'];
            $arr[0] = array_merge($goods,$guige,$num,$image,$price);
            $this->assign('arr',$arr);
        }else{
            $car_id = session('id');
            $car = new CarModel();
            $carinfo = $car->where('car_id','in',$car_id)->field('goods_id')->select();
            $goods_id = array();
            $goods_id = array_column($carinfo,'goods_id');
            $arr = $car->alias('a')
                        ->join('work_goods b','a.goods_id=b.goods_id','left')
                        ->field('a.guige,a.num,b.goods_name,a.price,a.image')
                        ->where('b.goods_id','in',$goods_id)
                        ->select();
            $this->assign('arr',$arr);
    }
        $person_id = session('person_id');
        $c = new CityModel();
        $city = $c->where('pid',1)->select();
        $this->assign('province',$city);
        $a = new AddressModel();
        $old_address = $a->where('person_id',$person_id)->select();
        $this->assign('old_address',$old_address);
        return $this->fetch();
    }
    //获取市级
    public function city(){
        $id = $_POST['provinceid'];
        $c = new CityModel();
        $city = $c->where('pid',$id)->select();
        return $city;
    }
    //获取县区
    public function area(){
        $id = $_POST['cityid'];
        $c = new CityModel();
        $area = $c->where('pid',$id)->select();
        return $area;
    }
    //添加用户地址
    public function add_address(){
        $data['person_id'] = $_GET['person_id'];
        $data['addressee'] = $_POST['addressee'];
        $data['address'] = $_POST['address'];
        $data['tel'] = $_POST['tel'];
        $a = new AddressModel($data);
        $ret = $a->allowField(true)->save();
        if($ret){
            return 1;
        }
    }
    //用户查看商品页面
    public function showdetail(){
        $id = input('get.goods_id');
        $good = new GoodsModel();
        $goods = $good->alias('a')
                        ->join('work_shop b','a.shop_id=b.shop_id','left')
                        ->where('goods_id',$id)->select();
        $this->assign('goods',$goods);
        $tupian=$good->where('type_id',$goods[0]['type_id'])->where('shop_id',$goods[0]['shop_id'])->select();
        $this->assign('tupian',$tupian);
        $haikan=$good->where('type_id',$goods[0]['type_id'])->select();
        $this->assign('haikan',$haikan);
        $g = new GoodspriceModel();
        $price = $g->where('goods_id',$id)->select();
        $k_ey = array();
        $k_ey = array_column($price,'k_ey');
        $arr = array();
        for($i=0;$i<count($k_ey);$i++){
            $values[] = explode('_', $k_ey[$i]);
        }
        foreach ($values as $key => $value) {
                foreach ($value as $k => $v) {
                    array_push($arr,$v);
                } 
            }
        $value_id = array_unique ($arr);
        $v = new GuigevalueModel();
        $val = $v->where('value_id','in',$value_id)->select();
        $guige_id = array();
        $guige_id = array_column($val,'guige_id');
        $g_name = new GuigenameModel();
        $data = $g_name->where('id','in',$guige_id)->select();
        $this->assign('data',$data);
        $this->assign('val',$val);
        return $this->fetch();
    }
    //用户选择的商品显示对应的价格图片库存
    public function price(){
        $goods_id = $_GET['goods_id'];
        $guige = $_POST['guige'];     
        $g = new GoodspriceModel();
        $arr = array();
        if(count($guige)>1){
            foreach ($guige as $key => $value) {
                $ret = $g->where('key_name','like','%'.$value.'%')->where('goods_id',$goods_id)->field('price_id')->select();
                $price_id = array();
                $price_id = array_column($ret,'price_id');
                $price[]= $price_id;
            }
            foreach ($price as $key => $value) {
                foreach ($value as $k => $v) {
                    array_push($arr,$v);
                } 
            }
        //去掉数组中的重复值
        $unique_arr = array_unique ( $arr ); 
        // 获取重复数据的数组 
        $repeat_arr = array_diff_assoc ( $arr, $unique_arr );
        $data = $g->where('price_id','in',$repeat_arr)->select();
        }else{
            $data = $g->where('key_name','in',$guige)->where('goods_id',$goods_id)->select();
        }
        return $data;

    }
    //用户选择的商品规格添加进购物车
    public function add_car(){ 
        $data['name'] = session('name');
        $person = new PersonModel();
        $data['person_id'] = $person->where('name',$data['name'])->value('person_id');
        $arr['guige'] = $_POST['guige'];
        $data['guige']  =  implode(',', $arr['guige'] );
        $data['num'] = $_POST['num'];
        $data['price'] = $_POST['price'];
        $data['image'] = $_POST['image'];
        $data['goods_id'] = $_GET['goods_id'];
        $car = new CarModel($data);
        $ret = $car->allowField(true)->save();
        $name = $data['name'];
        if($ret){      
         return $name; 

    }
}
//用户购物车加减商品数量操作
    public function num(){
        $car_id = $_GET['car_id'];
        $num = $_POST['t'];
        $c = new CarModel();
        $ret = $c->where('car_id',$car_id)->update(['num'=>$num]);     
        if($ret){
            return 1;
        }
    } 
    //退出登录
    public function logout(){
        session('name',null);     
        session('person_id',null);
        $this->success('退出登录成功','index');
      
    }
    //用户搜索功能
    public function search(){
        $title = $_POST['searchMess'];
        $pscws = new pscws4();
        $pscws->set_dict('../extend/scws/lib/dict.utf8.xdb');
        $pscws->set_rule('../extend/scws/lib/rules.utf8.ini');
        $pscws->set_ignore(true);
        $pscws->send_text($title);
        $words = $pscws->get_tops(5);
        $pscws->close();
        $tags=array();
        foreach($words as $val) {
            $tags[] = $val['word'];
        }
        $c = new CatModel();
        $g = new GoodsModel();
        $arr2 = array();
        if(count($tags)>1){    
            foreach($tags as $v){
                $ret = $c->where('cat_name','like','%'.$v.'%')->field('cat_id')->select();
                $cat_id = array();
                $cat_id = array_column($ret,'cat_id');
                $cat[]= $cat_id;
            }
            foreach ($cat as $key => $value) {
                foreach ($value as $k => $v) {
                    array_push($arr2,$v);
                } 
            }
            //去掉数组中的重复值
            $unique_arr = array_unique ( $arr2 ); 
            // 获取重复数据的数组 
            $repeat_arr = array_diff_assoc ( $arr2, $unique_arr );
            $goods = $g->alias('a')->join('work_shop b','a.shop_id=b.shop_id','left')->where('cat_id','in',$unique_arr)->where('up_goods',1)->paginate(32,false,['query'=>request()->param()]);
            $a = new AttrModel();
            $attr = $a->where('cat_id','in',$repeat_arr)->select();
            $this->assign('attr',$attr);
            $this->assign('goods',$goods);
            $this->assign('title',$title);
            return $this->fetch();
        }else if(count($tags)==1){
            $cat = $c->where('cat_name','in',$tags)->field('cat_id')->select();
            $repeat_arr = array();
            $repeat_arr = array_column($cat,'cat_id');  
             foreach ($tags as $k => $v) {
                $goods = $g->alias('a')->join('work_shop b','a.shop_id=b.shop_id','left')->where('goods_name','like','%'.$v.'%')->where('up_goods',1)->paginate(32,false,['query'=>request()->param()]);
            }
            $a = new AttrModel();
            $attr = $a->where('cat_id','in',$repeat_arr)->select();
            $this->assign('attr',$attr);
            $this->assign('goods',$goods);
            $this->assign('title',$title);
            return $this->fetch();
        }
        else{
            echo("<script type='text/javascript'> 
                alert('没有此搜索记录');
                window.history.go(-1);
                </script>");
        }
    }
  public function dingdan(){
    session('id',null);
    session('guige',null);
    session('num',null);
    session('price',null);
    session('image',null);
    return $this->fetch();
  }
}
?>