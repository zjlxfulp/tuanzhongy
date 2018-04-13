<?php
$flag = 1;
$brand = $this->db
    ->select( '*,fn_get_file_url(fid) as small_pic')
    ->from('single_brand')
    ->where("id=$id")
    ->row();
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $brand['brand_name']?></title>
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
                    <div class="swiper-slide"><img src="<?php echo $brand['small_pic'];?>" alt=""></div>
                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
            </div>
            <!-- 轮播图 -->
            <!-- 商家信息 -->
            <div class="merchant_info ">
                <h3><?php echo $brand['brand_name'];?><span class="fr"></span></h3>
                <p class="abstract"><?php echo $brand['brand_name'];?></p>
                <ul class="shop_introduce_li">

                </ul>
                <div class="introduce">
                    <p class="introduce-title"><i></i>品牌介绍</p>
                    <div >
                        <?php echo $brand['describe'];?>
                    </div>

                </div>
            </div>
        </div>
        <?php
        $show_modules = $this->db->select('show_modules')->from('single_pmodule')->where("id=4")->single();
        if($show_modules) {
            $process = $this->db->select('id,dirname')->from('single_module')->where("id in ($show_modules)")->query();
            $pdata = array();
            if(!empty($process)) {
                foreach($process as $key => $val) {
                    $pdata[$val['dirname']] = $val;
                }
            }
            unset($process);

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

        }

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
            console.log($(obj));
            var str = $(obj).text();
            Gstr = str;
            if(str.length > 70){
                var str1 = str.substring(0,70);
                $(obj).text(str1+'...');
                $(obj).append("<span></span>")
                $(obj).find('span').attr('onclick','textMore(this)').text('阅读全部');
            }
        }

        function textMore(obj) {
            var parent  = $(obj).parent();
            parent.html(Gstr);
            parent.append("<span></span>");
            parent.find('span').attr('onclick','istextMore(this.parentNode)').text('收起');

        }
    </script>

    </body>
    </html>
<?php
$flag = 1;
?>