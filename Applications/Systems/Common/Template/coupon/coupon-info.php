<?php
$data = $this->db
    ->select( '*,fn_get_file_url(coupon_pic_fid) as small_pic')
    ->from('single_coupon')
    ->where("id=$id")
    ->row();
?>
<!DOCTYPE html>
<html> 
<head> 
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" /> 
<title><?php echo $data['coupon_name'];?></title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
<link rel="stylesheet" href="/html/css/resize.css">
<link rel="stylesheet" href="/html/css/style.css">

</head>
<body>
  <iframe src="/html/iframe.html" frameborder="0" style="width: 100%; height: 100px; display: block;"></iframe>
  <div class="couponXq">
      <div class="couponXq-img"><img src="<?php echo $data['small_pic']?>" alt=""></div>
      <ul class="couponXq-info">
      	<li>
      		<h2>优惠卷名称</h2>
      		<p><?php echo $data['coupon_name'];?></p>
      	</li>
      	<li>
      		<h2>使用方法</h2>
      		<p><?php echo $data['use_method'];?></p>
      	</li>
      	<li>
      		<h2>优惠范围</h2>
      		<p><?php echo $data['coupon_path'];?></p>
      	</li>
      	<li>
      		<h2>除外商品</h2>
      		<p><?php echo $data['except_goods'];?></p>
      	</li>
      	<li>
      		<h2>支付方式</h2>
      		<p><?php echo $data['payment'];?></p>
      	</li>
          <li>
              <h2>活动时间</h2>
              <p><?php echo $data['start_date'] . $data['end_date'];?></p>
          </li>
      </ul>
  </div>

<script src="/html/js/swiper/swiper.min.js"></script>
</body> 
</html>
<?php $flag = 1;?>