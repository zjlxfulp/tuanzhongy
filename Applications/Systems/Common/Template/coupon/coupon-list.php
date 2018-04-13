<!DOCTYPE html> 
<html> 
<head> 
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" /> 
<title>全部优惠卷</title> 
<meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
<link rel="stylesheet" href="/html/css/resize.css">
<link rel="stylesheet" href="/html/css/style.css">

</head>
<body>
<?php $flag = 1;?>
  <iframe src="/html/iframe.html" frameborder="0" style="width: 100%; height: 100px; display: block;"></iframe>
  <div class="all-yhq">
    <ul>
        <?php
        $data = $this->db
            ->select( '
                    id,coupon_name,
                    fn_get_file_url(small_pic_fid) as small_pic'
            )
            ->from('single_coupon')
            ->query();

            if(!empty($data)) {
                foreach($data as $v1) {
                    $html .= "<li><a href=\"/html/coupon/coupon-info-{$v1['id']}.shtml\"><img src=\"/html/images/yhq-di.png\" alt=\"\"><span>{$v1['coupon_name']} </span></a></li>";
                    echo $html;
                }
            }
        ?>


    </ul>
  </div>
<script src="/html/js/swiper/swiper.min.js"></script>
</body> 
</html> 