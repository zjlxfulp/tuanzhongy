<?php
\Core\Router::$app = $app;
\Core\Router::any('/test','Home@test');
\Core\Router::any('/get_auth_code','Home@get_auth_code');
\Core\Router::any('/wechat/event','Home@event');
\Core\Router::any('/authorization','Home@authorization');
\Core\Router::any('/get_material','Home@get_material');
\Core\Router::any('/wx_public_list','Home@wx_public_list');
\Core\Router::any('/get_attention_info','Home@get_attention_info');
\Core\Router::any('/send_message','Home@send_message');
\Core\Router::any('/add_material','Home@add_material');
\Core\Router::any('/upload_img','Home@upload_img');
\Core\Router::any('/snsapi_base','Home@snsapi_base');
\Core\Router::any('/qrcode','Home@qrcode');
\Core\Router::any('/wechat_login','Home@wechat_login');
\Core\Router::any('/wechat_login2','Home@wechat_login2');
\Core\Router::any('/get_article_data','Home@get_article_data');
