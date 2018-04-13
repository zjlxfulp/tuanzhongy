<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
    <title>首页</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <!-- Link Swiper's CSS -->
    <link rel="stylesheet" href="/html/js/swiper/swiper.min.css">
    <link rel="stylesheet" href="/html/css/resize.css">
    <link rel="stylesheet" href="/html/css/style.css">
    <script src="/html/js/swiper/swiper.min.js"></script>
</head>
<body>

<div class="loader">
    <div class="loader-inner line-scale">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
<div class="index-content">
    <iframe src="/html/iframe.html" frameborder="0" style="width: 100%; height: 100px; display: block;"></iframe>
    <!-- 轮播图 -->
    <?php
    if(isset($pdata['banner']) && !empty($pdata['banner'])) {
        $data = $this->db
            ->select( '
                    single_banner.*,
                    fn_get_file_url(single_banner.fid) as url,
                    single_articles.id as aid'
            )
            ->from('single_banner')
            ->leftJoin( 'single_articles',  'single_banner.aid=single_articles.id')
            ->where( 'single_banner.status=1')
            ->query();

        if(!empty($data)) {
            $html =  '<div class="swiper-container"  id="swiper-container1">
                            <div class="swiper-wrapper">';
            foreach($data as $v1) {
                $html .= "<div class='swiper-slide'>";
                if($v1['aid']) {
                    $html .= "<a href='/html/articles/articles-info-{$v1['aid']}.shtml'>";
                } else {
                    $html .= "<a href='#'>";
                }
                $html .= "<img title='{$v1['title']}' src='{$v1['url']}' alt='{$v1['alt']}'></a></div>";
            }
            $html .= ' </div>
          <div class="swiper-pagination"></div>
      </div>';
            echo $html;
        }

        unset($pdata['banner']);
    }
    ?>
    <!-- 轮播图 -->
    <!-- 店铺 -->
    <?php
    if(isset($pdata['store']) && !empty($pdata['store'])) {

        $data = $this->db
            ->select( '
                    id,store_name,
                    fn_get_file_url(small_pic_fid) as small_pic'
            )
            ->from('single_store')
            ->limit(8)
            ->query();

        if(!empty($data)) {
            $html =  ' <div class="discount_q_list coupon">
                        <p class="introduce-title clear">
                          <span class="fl"><i></i>热门店铺</span>
                          <a class="check_more fr" href="/html/store/store-list-1.shtml"><img src="/html/images/ChevronCopy.png" width="8px" alt=""></a>
                        </p>
                        <ul class="coupon-content clear">';
            foreach($data as $v1) {
                $html .= "<li><a href='/html/store/store-info-{$v1['id']}.shtml'><img src='{$v1['small_pic']}' alt=\"\"><p>{$v1['store_name']}</p></a></li>";
            }
            $html .= '</ul>
                  </div>';
            echo $html;
        }

        unset($pdata['store']);
    }
    ?>
    <!-- 店铺 -->
    <!-- 品牌 -->
    <?php
    if(isset($pdata['brand']) && !empty($pdata['brand'])) {

        $data = $this->db
            ->select( '
                    id,brand_name,
                    fn_get_file_url(fid) as small_pic'
            )
            ->from('single_brand')
            ->limit(4)
            ->query();

        if(!empty($data)) {
            $html =  ' <div class="discount_q_list coupon">
                        <p class="introduce-title clear">
                          <span class="fl"><i></i>热门品牌</span>
                          <a class="check_more fr" href="/html/brand/brand-list-1.shtml"><img src="/html/images/ChevronCopy.png" width="8px" alt=""></a>
                        </p>
                        <ul class="coupon-content clear">';
            foreach($data as $v1) {
                $html .= "<li><a href='/html/brand/brand-info-{$v1['id']}.shtml'><img src='{$v1['small_pic']}' alt=\"\"><p>{$v1['brand_name']}</p></a></li>";
            }
            $html .= '</ul>
                  </div>';
            echo $html;
        }

        unset($pdata['store']);
    }
    ?>
    <!-- 品牌 -->
    <!-- 优惠商家适用券 -->
    <?php
    if(isset($pdata['coupon']) && !empty($pdata['coupon'])) {
        $data = $this->db
            ->select( '
                    id,coupon_name,
                    fn_get_file_url(small_pic_fid) as small_pic'
            )
            ->from('single_coupon')
            ->limit(4)
            ->query();

        if(!empty($data)) {
            $html =  ' <div class="discount_q_list coupon">
                        <p class="introduce-title clear">
                          <span class="fl"><i></i>优惠商家适用券</span>
                          <a class="check_more fr" href="/html/coupon/coupon-list-1.shtml"><img src="/html/images/ChevronCopy.png" width="8px" alt=""></a>
                        </p>
                        <ul class="coupon-content clear">';
            foreach($data as $v1) {
                $html .= "<li><a href='/html/coupon/coupon-info-{$v1['id']}.shtml'><img src='{$v1['small_pic']}' alt=\"\"><p>{$v1['coupon_name']}</p></a></li>";
            }
            $html .= '</ul>
                  </div>';
            echo $html;
        }


        unset($pdata['coupon']);
    }
    ?>
    <!-- 优惠商家适用券 -->
    <!-- 其他图文模块 -->
    <?php
    if(!empty($pdata)) {
        foreach($pdata as $val) {
            $minfo = $this->db->select('id,name')->from('single_module')->where("dirname='{$val['dirname']}'")->row();//模块id
            if(!empty($minfo)) {
                $data = $this->db
                    ->select( 'id, seo_title, fn_get_file_url(fid) as small_pic')
                    ->from('single_articles')
                    ->where( "m_id = {$minfo['id']}")
                    ->query();
                if(!empty($data)) {


                    if(!empty($data)) {
                        $html =  ' <div class="activity_h">
                                    <p class="introduce-title clear">
                                      <span class="fl"><i></i>' . $minfo['name'] . '</span>
                                    </p>
                                    <div class="swiper-container activity_list" id="swiper-container-' . $val['dirname'] . '" >'.
                            '<div class="swiper-wrapper">';
                        foreach($data as $v1) {
                            $html .= "<div class=\"swiper-slide\">
                                            <a href='/html/{$val['dirname']}/{$val['dirname']}-info-{$v1['id']}.shtml'>
                                                <img src=\"{$v1['small_pic']}\" alt=\"\"><p>{$v1['seo_title']}</p>
                                            </a>
                                        </div>";
                        }
                        $html .= '</div>
                                </div>
                              </div>';

                        $html .= "
                      <script type=\"text/javascript\">
                          var {$val['dirname']} = new Swiper('#swiper-container-{$val['dirname']}', {
                                autoplay: 3000,      
                                loop:false,
                                slidesPerView: 1.5,
                                spaceBetween: 15
                          });
                      </script>
                      ";
                        echo $html;
                    }
                }
            }
        }
    }

    ?>
    <!-- 其他图文模块 -->
</div>

<script type="text/javascript">
    var swiper = new Swiper('#swiper-container1', {
        autoplay: 3000,
        pagination: '.swiper-pagination',
        paginationClickable: true
    });
</script>
</body>
</html>