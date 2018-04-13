<?php
$flag = 1;
$store = $this->db
    ->select( '*,fn_get_file_url(small_pic_fid) as small_pic')
    ->from('single_store')
    ->where("id=$id")
    ->row();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $store['store_name']?></title>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
	<link rel="stylesheet" href="/html/css/resize.css">
    <link rel="stylesheet" href="/html/css/style.css">
	<link rel="stylesheet" href="/html/js/swiper/swiper.min.css">

</head>
<body>
<iframe src="/html/iframe.html" frameborder="0" style="width: 100%; height: 100px;"></iframe>
<div class="particulars_wrapper">
	<div class="particulars_top">
    	<!-- 轮播图 -->
        <div class="swiper-container" style="max-height:200px" id="swiper-container1">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img src="<?php echo $store['small_pic'];?>" alt=""></div>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>
        <!-- 轮播图 -->
        <!-- 商家信息 -->
		<div class="merchant_info ">
            <h3><?php echo $store['store_name'];?><span class="fr" id="collect-store" data-val="<?php echo $id;?>"></span></h3>
			<p class="abstract"><?php echo $store['advertise'];?></p>
            <ul class="shop_introduce_li">
                <li><img src="/html/images/open.png" alt="">营业时间<span><?php echo $store['open_time'].'-'.$store['close_time'];?></span></li>
                <li><img src="/html/images/dz.png" alt="">商家地址<span><?php echo $store['address'];?></span></li>
                <li><img src="/html/images/jt.png" alt="">交通路线<span><?php echo $store['traffic'];?></span></li>
                <li><img src="/html/images/zf.png" alt="">支付方式<span><?php echo $store['payment'];?></span></li>
            </ul>
            <div class="introduce">
                <p class="introduce-title"><i></i>商家介绍</p>
                <div >
                    <?php echo $store['store_introduce'];?>
                </div>

            </div>
            <a href="http://www.google.cn/maps/place/<?php echo $store['address'];?>" class="check_map">查看地图 <i class="fr"></i></a>
		</div>
	</div>
    <?php
        $show_modules = $this->db->select('show_modules')->from('single_pmodule')->where("id=3")->single();
        if($show_modules != false) {
            $process = $this->db->select('id,dirname')->from('single_module')->where("id in ($show_modules)")->query();
        } else {
            $process = array();
        }
        $pdata = array();
        if(!empty($process)) {
            foreach($process as $key => $val) {
                $pdata[$val['dirname']] = $val;
            }
        }
        unset($process);
    ?>

    <!-- 热门活动 -->
    <?php
    if(isset($pdata['hotacts']) && !empty($pdata['hotacts'])) {
        $mid = $this->db
            ->select( 'id')
            ->from('single_module')
            ->where( "dirname = 'hotacts'")
            ->single();
        if($mid) {
            $data = $this->db
                ->select( 'id, seo_title, fn_get_file_url(fid) as small_pic')
                ->from('single_articles')
                ->where( "m_id = {$mid} and sid={$id}")
                ->query();
        }

        if(!empty($data)) {
            $html = "<div class=\"activity_h\">
                <p class=\"introduce-title\"><i></i>热门活动</p>
                <div class=\"swiper-container activity_list\" id=\"swiper-container\">
                    <div class=\"swiper-wrapper\">";
                    foreach($data as $v1) {
                        $html .= "<div class=\"swiper-slide\"><a href='/html/hotacts/hotacts-info-{$v1['id']}.shtml'><img src=\"{$v1['small_pic']}\" alt=\"\"><p>{$v1['seo_title']}</p></a></div>";
                    }
                   $html .= "</div>
                </div>
            </div>";

            echo $html;
        }

        unset($pdata['hotacts']);
    }
    ?>

   <!-- 推荐优惠券 -->
    <?php
    if(isset($pdata['recoupon']) && !empty($pdata['recoupon'])) {
        $brand_id = $store['brand_id'];
        $data = $this->db
            ->select( 'id,coupon_name,fn_get_file_url(small_pic_fid) as small_pic')
            ->from('single_coupon')
            ->where( "brand_id = {$brand_id}")
            ->query();

        if(!empty($data)) {
            $html = "<div class=\"discount_q_list\">
                        <p class=\"introduce-title\"><i></i>推荐优惠券</p>
                        <div class=\"swiper-container discount_list\" id=\"swiper-container2\">
                            <div class=\"swiper-wrapper\">";
                    foreach($data as $v1) {
                        $html .= "<div class=\"swiper-slide\"><a href='/html/coupon/coupon-info-{$v1['id']}.shtml'><img src=\"/html/images/yhq--di.png\" alt=\"\"><span>{$v1['coupon_name']}</span></a></div>";
                    }

                    $html .= "</div>
                </div>
            </div>";
            echo $html;
        }

        unset($pdata['recoupon']);
    }
    ?>
    <!-- 其他门店 -->
    <?php
        $brand_id = $store['brand_id'];
        $data = $this->db
            ->select( '*,fn_get_file_url(small_pic_fid) as small_pic')
            ->from('single_store')
            ->where("brand_id=$brand_id")
            ->where("id!={$store['id']}")
            ->query();

    if(!empty($data)) {
        $html = "<div class=\"else_shop\">
        <p class=\"introduce-title\"><i></i>其他门店</p>
        <div class=\"swiper-container discount_list\" id=\"swiper-container3\">
            <div class=\"swiper-wrapper\">";
        foreach($data as $v1) {
            $html .= "<div class=\"swiper-slide\">
                        <a href=\"/html/store/store-info-{$v1['id']}.shtml\">
                        <div><img src=\"{$v1['small_pic']}\" alt=\"\"></div>
                        <div class=\"info\">
                            <p class=\"title\">{$v1['store_name']}</p>
                            <p class=\"time\">{$v1['open_time']}-{$v1['close_time']}</p>
                            <p class=\"address\">{$v1['address']}</p>
                        </div>
                    </a>
                    <a href=\"\" class=\"check_map_btn\">{$v1['address']}</a>
                    </div>";
        }
            $html .= "</div>
            </div>
        </div>";
        echo $html;
    }

    unset($pdata['store']);

    ?>
</div>
<script src="/html/js/jquery-1.12.4.js"></script>
<script src="/html/js/swiper/swiper.min.js"></script>
<script>
	var swiper = new Swiper('#swiper-container1', {
        pagination: '.swiper-pagination',
        paginationClickable: true
    });
    var mySwiper2 = new Swiper('#swiper-container', {
        // autoplay: 3000,      //可选选项，自动滑动
        loop:false,
        slidesPerView: 1.5,
        spaceBetween: 15
    });
    var mySwiper2 = new Swiper('#swiper-container2', {
        // autoplay: 3000,      //可选选项，自动滑动
        loop:false,
        slidesPerView: 1.18,
        spaceBetween: 15
    });
    var mySwiper2 = new Swiper('#swiper-container3', {
        // autoplay: 3000,      //可选选项，自动滑动
        loop:false,
        slidesPerView: 1.5,
        spaceBetween: 20
    });


    istextMore ('.introduce > div');
    var Gstr; 
    function istextMore (obj) {
        var str ='<?php echo $store['store_introduce'];?>';
        Gstr = str;
        if(str.length > 70){
            var str1 = str.substring(0,70);
            $(obj).text(str1+'...');
            $(obj).append("<span></span>");
            $(obj).find('span').attr('onclick','textMore(this)').text('阅读全部');
        }
    }
    isCollect();
    function isCollect() {
        var sid = <?php echo $id?>;
        $.ajax({
            method: "POST",
            url: "/home/collect.front",
            data:{sid:sid},
            dataType:"json",
            success: function (res) {
                if(res.code == 0) {
                    var iscol = false;
                    var _d = res.info;
                    for(var i in _d) {
                        iscol = true;
                    }
                    if(iscol == false) {
                        $("#collect-store").css("background-image","url(/html/images/collect-d.png)");
                        $("#collect-store").attr('data-val','d');
                    } else {
                        $("#collect-store").css('background-image','url(/html/images/collect-h.png)');
                        $("#collect-store").attr('data-val','h');
                    }
                } else{
                    throw new Error('failed');
                }
            }
        });
    }

    function textMore(obj) {
        var parent  = $(obj).parent();
        parent.html(Gstr);
        parent.append("<span></span>");
        parent.find('span').attr('onclick','istextMore(this.parentNode)').text('收起');
        
    }

    $("#collect-store").click(function() {
        var sid = <?php echo $id?>;
        var iscol = $("#collect-store").attr('data-val');
        if(iscol == 'h'){
            iscol = 1;
            $("#collect-store").css('background-image','url(/html/images/collect-d.png)');
            $("#collect-store").attr('data-val','d');
        } else {
            iscol = 0;
            $("#collect-store").css('background-image','url(/html/images/collect-h.png)');
            $("#collect-store").attr('data-val','h');
        }
        $.ajax({
            method: "POST",
            url: "/home/options.front",
            data:{sid:sid, iscol:iscol},
            dataType:"json",
            success: function (res) {
                if(res.code == 0) {

                } else{
                    throw new Error('failed');
                }
            }
        });
    })
</script>
	
</body>
</html>
<?php
$flag = 1;
?>