<?php
$data = $this->db
    ->select( 'a.*,b.dirname,b.name as module_name,fn_get_file_url(a.fid) as small_pic')
    ->from('single_articles as a')
    ->leftJoin('single_module as b', 'a.m_id=b.id')
    ->where("a.id=$id")
    ->row();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $data['seo_title'];?></title>
    <meta name="description" content="<?php echo $data['seo_content'];?>" />
    <meta name="keywords" content="<?php echo $data['seo_kwds'];?>">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <link rel="stylesheet" href="/html/css/resize.css">
    <link rel="stylesheet" href="/html/css/style.css">
</head>
<body>
<iframe src="/html/iframe.html" frameborder="0" style="width: 100%; height: 100px;"></iframe>
<div class="activity_wrapper">
    <div class="activity_title"><img src="/html/images/title.png" alt=""><div class="heng"></div></div>
    <div class="activity_content">
        <div>
            <?php echo $data['content'];?>
        </div>
    </div>
</div>
</body>
</html>
<?php $flag = 1;?>