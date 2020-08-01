<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use app\admin\model\User as UserModel;
use app\admin\model\Cat as CatModel;
use app\admin\model\Car as CarModel;
use app\admin\model\Type as TypeModel;
use app\admin\model\Attr as AttrModel;
use app\admin\model\Goods as GoodsModel;
use app\admin\model\Shop as ShopModel;
use app\admin\model\City as CityModel;
use app\admin\model\Guigename as GuigenameModel;
use app\admin\model\Guigevalue as GuigevalueModel;
use app\admin\model\Goodsprice as GoodspriceModel;
class Goods extends Base
{
	//后台主页面
	public function index(){
		$u = new UserModel();
		$user = $u->select();
		$this->assign('user',$user);
		return $this->fetch();
	}
	//后台店铺界面
	public function shop(){
		$s = new ShopModel();
		$shop = $s->paginate(8,false,['query'=>request()->param()]);
		$this->assign('shop',$shop);
		return $this->fetch();
	}
	//添加店铺页面
	public function addshop(){
		$c = new CityModel();
		$province = $c->where('type',1)->select();
		$this->assign('province',$province);
		return $this->fetch();
	}
	//获得市
	public function city(){
		$id = $_POST['provinceid'];
        $c = new CityModel();
        $city = $c->where('pid',$id)->select();
        return $city;
	}
	//添加店铺到数据库
	public function add_shop(){
		$data['shop_name'] = $_POST['shop_name'];
		$data['shop_address'] = $_POST['address'];
		$s = new ShopModel();
		$ret = $s->allowField(true)->save($data);
		return 1;
		
	}
	//后台购物车管理页面
	public function car(){
		$car = new CarModel();
		$info = $car->alias('a')->join('work_goods b','a.goods_id=b.goods_id','LEFT')->field('a.num,a.guige,a.name,a.person_id,a.car_id,b.goods_name,a.price')->paginate(8,false,['query'=>request()->param()]);
		$this->assign('info',$info);
		return $this->fetch();
	}
	//删除人员
	public function user_del(){
		$data['user_id'] = input('get.');
		$u = new UserModel();
		$ret = $u->destroy($id,true);
			if($ret){
				$this->success('删除成功','index');
			}else{
				$this->error('删除失败','index');
			}
	}
	//商品展示页面
	public function goodslist(){
		$g = new GoodsModel();
		$goods = $g->paginate(8,false,['query'=>request()->param()]);
		$this->assign('goods', $goods);
		return $this->fetch();
	}
	//添加商品页面
	public function addgoods(){
		$c = new CatModel();
		$tree = $c->select();
		$data = $c->tree($tree);
		$this->assign('data',$data);
		$t = new TypeModel();
		$type = $t->select();
		$this->assign('type',$type);
		$s = new ShopModel();
		$shop = $s->field('shop_id,shop_name')->select();
		$this->assign('shop',$shop);
		return $this->fetch();
	}
	//添加对应商品属性
	public function addgoodattr(){
		$data = $_GET['goods_id'];
		$g = new GoodsModel();
		$type = $g->where('goods_id',$data)->field('type_id')->select();	
		$type_id = array();
        $type_id = array_column($type,'type_id');
		$gui = new GuigenameModel();
		$name = $gui->where('type_id','in',$type_id)->select();
		$this->assign('name',$name);
		$guige_id = array();
		$guige_id = array_column($name, 'id');
		$v = new GuigevalueModel();
		$value = $v->where('guige_id','in',$guige_id)->select();
		$this->assign('goods',$data);
		$this->assign('value',$value);
		return $this->fetch();
	}
	//添加对应商品属性
	public function addgoodsattr(){
		$g = new GoodspriceModel();
		$name = $_POST['name'];
		$k = $_POST['k'];
		$price = $_POST['price'];
		$kucun = $_POST['kucun'];
		$files = request()->file('image');
        foreach($files as $file){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            $image[] = '/upload/'.$info -> getSaveName();
        }
		$goods_id = $_POST['goods_id'];
		$j=1;
		if($name[0]==$name[2]){
			for($i=0;$i<count($name);$i=$i+2){
				$key_name[] = ($name[$i]."_".$name[$j]);
				$k_ey[] = $k[$i]."_".$k[$j];
				$j=$j+2;
			}
		}else{
			$key_name = $name;
			$k_ey = $k;
		}

		for ($v=0;$v<count($price);$v++) { 
			$data[] = [
				'k_ey'=>$k_ey[$v],
				'key_name'=>$key_name[$v],
				'price'=>$price[$v],
				'kucun'=>$kucun[$v],
				'image'=>$image[$v],
				'goods_id'=>$goods_id
			];
		}
		$ret = $g->allowField(true)->saveAll($data);
		if($ret){
			return $this->success('添加对应商品价格成功','goodslist');
		}else{
			return $this->error('添加对应商品价格失败','goodslist');
		}
	}
	//对应分类属性添加业务逻辑
	public function gdatr_add(){
		$cat_id = $_POST['cat_id'];
		$attr_name = $_POST['attr_name'];
		$attr_value = $_POST['attr_value'];
		$a = new AttrModel();
		for($i=0;$i<count($attr_name);$i++){
			$data[]=[
				'attr_name'=>$attr_name[$i],
				'attr_value'=>$attr_value[$i],
				'cat_id'=>$cat_id
			];
		}
		$ret = $a->allowField(true)->saveAll($data);
		if($ret){
			return $this->success('添加分类属性成功','catlist');
		}else{
			return $this->error('添加分类属性失败');
		}
	}
	//添加商品业务逻辑
	public function goods_add(){
		//1.收集表单数据
   		$data['goods_name'] = $_POST['goods_name'];
   		$data['goods_price'] = $_POST['goods_price'];
   		$data['goods_desc'] = $_POST['goods_desc'];
   		$data['cat_id'] = $_POST['cat_id'];
   		$data['shop_id'] = $_POST['shop_id'];
   		$data['type_id'] = $_POST['type_id'];
   		$data['up_goods'] = isset($_POST['up_goods']) ? '1' : '0';
   		$data['goods_promote'] = isset($_POST['goods_promote']) ? implode(',', $_POST['goods_promote']) : '';
   		$files = request()->file('image_ori');
        foreach($files as $file){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            $img[] = '/upload/'.$info -> getSaveName();
        }
        $data['image_ori'] = implode(',',$img);
		$g = new GoodsModel();
		$ret = $g->allowField(true)->save($data);
		if($ret){
			return $this->success('添加商品成功','goodslist');
		}else{
			return $this->error('添加商品失败','addgoods');
		}
	}
	//分类列表页面
	public function catlist(){
		$c = new CatModel();
		$parent = $c->where('pid',0)->select();
		$this->assign('parent',$parent);
		$er = $c->where('sort_order',2)->select();
		$this->assign('er',$er);	
		$son = $c->where('sort_order','>',2)->select();
		$this->assign('son',$son);
		return $this->fetch();
	}
	//分类页的编辑页面
	public function edit(){
		$id = input('get.cat_id');
		$c = new CatModel();
		$cats = $c->get($id);
		$this->assign('cats',$cats);
		$list = $c->select();
		$data = $c->tree($list);
		$this->assign('data',$data);
		$t = new TypeModel();
		$type = $t->select();
		$this->assign('type',$type);
		return $this->fetch();
	}
	//分类页编辑的更新业务逻辑
	public function update(){
		$data = input('post.');
		$id = input('post.cat_id');
		$c = new CatModel();
		$ret = $c->allowField(true)->save($data,['id'=>$id]);
		if($ret){
			$this->success('修改分类成功','catlist');
		}else{
			$this->error('修改分类失败','catlist');
		}
	}
	//分类页删除业务逻辑
	public function delete(){
		$id = input('get.cat_id');
		$c = new CatModel();
		$data = $c->where('pid',$id)->find();
		if($data){
			$this->error('分类下面有子分类，不允许删除','catlist');
		}else{
			$ret = $c->destroy($id,true);
			if($ret){
				$this->success('删除分类成功','catlist');
			}else{
				$this->error('删除分类失败','catlist');
			}
		}
	}
	//添加分类页面
	public function addcat(){
		$c = new CatModel();
		$list = $c->select();
		$cats = $c->tree($list);
		$this->assign('cats',$cats);
		return $this->fetch();
	} 
	//添加分类业务逻辑
	public function insert(){
		$data = input('post.');
		$c = new CatModel($data);
		$result = $c->allowField(true)->save();
		if($result){
			$this->success('添加分类成功','catlist');
		}else{
			$this->error('添加分类失败','addcat');
		}
	}
	//添加分类属性页面
	public function cat_attr(){
		$cat_id = $_GET['cat_id'];
		$this->assign('cat',$cat_id);
		return $this->fetch();
	}
	//类型列表页面
	public function typelist(){
		$t = new TypeModel();
		$types = $t->paginate(10,false,['query'=>request()->param()]);
		$this->assign('types',$types);
		return $this->fetch();
	} 
	//添加类型页面
	public function addtype(){
		return $this->fetch();
	} 
	//添加类型业务逻辑
	public function add_type(){
		$data = input('post.');
		$t = new TypeModel();
		$ret = $t->allowField(true)->save($data);
		if($ret){
			$this->success('添加类型成功','typelist');
		}else{
			$this->error('添加类型失败','addtype');
		}
	}
	//属性列表页面
	public function attributeindex(){
		$data = input('get.type_id');
		$t = new TypeModel();
		$types = $t->select();	
		$this->assign('data',$data);	
		$this->assign('types',$types);
		$g = new GuigenameModel();
		$attrs = $g->alias('a')->join('work_guigevalue b','a.id=b.guige_id','LEFT')->where('a.type_id',$data)->paginate(10,false,['query'=>request()->param()]);
		$this->assign('attrs',$attrs);
		return $this->fetch();
	}
	//属性添加页面
	public function addguigename(){
		$t = new TypeModel();
		$types = $t->select();
		$this->assign('types',$types);
		return $this->fetch();
	}
	//属性添加业务逻辑
	public function addguige_name(){
		$data = input('post.');
		$a = new GuigenameModel();
		$ret = $a->allowField(true)->save($data);
		if($ret){
			$this->success('添加规格名成功','addguigename');
		}else{
			$this->error('添加规格名失败','addguigename');
		}
	}
	//添加规格值页面
	public function addguigevalue(){
		$g = new GuigenameModel();
		$guige = $g->alias('a')->join('work_type b','a.type_id=b.type_id','LEFT')->select();
		$this->assign('guige',$guige);
		return $this->fetch();
	}
	//规格值添加到数据库
	public function value_add(){
		$guige_id = $_POST['guige_id'];
		$guige_value = $_POST['guige_value'];
		$a = new GuigevalueModel();
		for($i=0;$i<count($guige_value);$i++){
			$data[]=[
				"guige_id"=>$guige_id,
				"guige_value"=>$guige_value[$i]
			];
		}
		$ret = $a->allowField(true)->saveAll($data);
		if($ret){
			$this->success('添加规格值成功','addguigevalue');
		}else{
			$this->error('添加规格值失败','addguigevalue');
		}
	}
	public function attr_del(){
		$id = input('get.id');
		$g = new GuigenameModel();
		$gui = new GuigevalueModel();
		$ret = $g->destroy($id,true);
		$result = $gui->where('guige_id',$id)->destroy(true);
		if($result){
			$this->success('删除规格值成功','typelist');
		}else{
			$this->error('删除规格值失败','typelist');
		}
	}

	//退出登录业务逻辑
	public function logout(){
        session('user_name',null);    
        $this->success('退出登录成功','user/login');
      
    }

}


?>