<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>品牌列表</title>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
	<link rel="stylesheet" href="/html/css/resize.css">
	<link rel="stylesheet" href="/html/css/style.css">
</head>
<body>
<iframe src="/html/iframe.html" frameborder="0" style="width: 100%; height: 100px;"></iframe>
<div class="merchant_wrapper">
	<!-- <div id="letter" ></div> -->
	<ul class="merchant_list sort_box">
        <?php $flag = 1;
            $data = $this->db
                ->select( '*,fn_get_file_url(fid) as small_pic')
                ->from('single_brand')
                ->query();

            if(!empty($data)) {
                $html = '';
                foreach($data as $v1) {
                    $html .=  "<li class=\"sort_list\">
                                <div class=\" item\">
                                     <a href=\"/html/brand/brand-info-{$v1['id']}.shtml\" class=\"clear \">
                                        <div class=\"merchant_img fl\"><img src=\"{$v1['small_pic']}\" alt=\"\"></div>
                                        <div class=\"merchant_info fl\">
                                            <h3 class=\"num_name\">{$v1['brand_name']}</h3>
                                            <p>{$v1['describe']}</p>
                                        </div>
                                    </a>
                                </div>
                            </li>";
                }

                echo $html;
            }
        ?>
	</ul>

</div>
<div class="initials">
		<ul>
			<!-- <li><img src="img/068.png"></li> -->
		</ul>
</div>
<script type="text/javascript" src="/html/js/jquery-1.12.4.js"></script>
<script src="/html/js/jquery.charfirst.pinyin.js"></script>
<script src="/html/js/sort.js"></script>

</body>
</html>