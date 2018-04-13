<?php
namespace Core\StoreDriver;

class Mongo {
    
/*** Mongodb类** examples:        
* $mongo = new mongo_model();  
* $mongo->selectDb("test_db");   
* 创建索引   
* $mongo->ensureIndexm("test_table", array("id"=>1), array('unique'=>true));   
* 获取表的记录   
* $mongo->countm("test_table");   
* 插入记录   
* $mongo->insertm("test_table", array("id"=>2, "title"=>"asdqw"));   
* 更新记录   
* $mongo->updatem("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"));   
* 更新记录-存在时更新，不存在时添加-相当于set   
* $mongo->updatem("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"),array("upsert"=>1));   
* 查找记录   
* $mongo->findm("c", array("title"=>"asdqw"), array("start"=>2,"limit"=>2,"sort"=>array("id"=>1)))   
* 查找一条记录   
* $mongo->findOnem("$mongo->findOne("ttt", array("id"=>1))", array("id"=>1));   
* 删除记录   
* $mongo->removem("ttt", array("title"=>"bbb"));   
* 仅删除一条记录   
* $mongo->removem("ttt", array("title"=>"bbb"), array("justOne"=>1));   
* 获取Mongo操作的错误信息   
* $mongo->getError();   
*/        
     
    //Mongodb连接     
    var $mongo;
    var $curr_db_name;        
    var $error;     
    /**   
    * 构造函数   
    * 支持传入多个mongo_server(1.一个出问题时连接其它的server 2.自动将查询均匀分发到不同server)   
    *   
    * 参数：   
    * $mongo_server:数组或字符串-array("127.0.0.1:1111", "127.0.0.1:2222")-"127.0.0.1:1111"   
    * $connect:初始化mongo对象时是否连接，默认连接   
    * $auto_balance:是否自动做负载均衡，默认是   
    *   
    * 返回值：   
    * 成功：mongo object   
    * 失败：false   
    */
    public function __construct($conf, $connect=true, $auto_balance=true)     
    { 
		$mongo_server = $conf['host'];
        if (is_array($mongo_server))     
        {     
            $mongo_server_num = count($mongo_server);     
            if ($mongo_server_num > 1 && $auto_balance)     
            {     
                $prior_server_num = rand(1, $mongo_server_num);     
                $rand_keys = array_rand($mongo_server,$mongo_server_num);     
                $mongo_server_str = $mongo_server[$prior_server_num-1];     
                foreach ($rand_keys as $key)     
                {     
                    if ($key != $prior_server_num - 1)     
                    {     
                        $mongo_server_str .= ',' . $mongo_server[$key];     
                    }     
                }     
            }     
            else     
            {     
                $mongo_server_str = implode(',', $mongo_server);     
            }                  
        }     
        else     
        {     
            $mongo_server_str = $mongo_server;     
        }     
        try {     
            $this->mongo = new \MongoClient($mongo_server, array('connect'=>$connect));
            $this->curr_db_name = $conf['database'];  
        }     
        catch (MongoConnectionException $e)     
        {     
            $this->error = $e->getMessage();     
            return false;     
        }
    }     
     
    public function getInstancem($mongo_server, $flag=array())     
    {     
        static $mongodb_arr;     
        if (emptyempty($flag['tag']))     
        {     
            $flag['tag'] = 'default';          }     
        if (isset($flag['force']) && $flag['force'] == true)     
        {     
            $mongo = new mongo_model($mongo_server);     
            if (emptyempty($mongodb_arr[$flag['tag']]))     
            {     
                $mongodb_arr[$flag['tag']] = $mongo;     
            }     
            return $mongo;     
        }     
        else if (isset($mongodb_arr[$flag['tag']]) && is_resource($mongodb_arr[$flag['tag']]))     
        {     
            return $mongodb_arr[$flag['tag']];     
        }     
        else     
        {     
            $mongo = new mongo_model($mongo_server);     
            $mongodb_arr[$flag['tag']] = $mongo;     
            return $mongo;                  
		}          
	}          
        
     
    /**   
    * 创建索引：如索引已存在，则返回。   
    *   
    * 参数：   
    * $table:表名   
    * $index:索引-array("id"=>1)-在id字段建立升序索引   
    * $index_param:其它条件-是否唯一索引等   
    *   
    * 返回值：   
    * 成功：true   
    * 失败：false   
    */     
    public function ensureIndexm($table, $index, $index_param=array())     
    {     
        $dbname = $this->curr_db_name;     
        $index_param['safe'] = 1;     
        try {     
            $this->mongo->$dbname->$table->ensureIndex($index, $index_param);     
            return true;     
        }     
        catch (MongoCursorException $e)     
        {     
            $this->error = $e->getMessage();     
            return false;     
        }     
    }     
     
    /**   
    * 插入记录   
    *   
    * 参数：   
    * $table:表名   
    * $record:记录   
    *   
    * 返回值：   
    * 成功：true   
    * 失败：false   
    */     
    public function insertm($table, $record)     
    {     
	    list($usec, $sec) = explode(" ", microtime());
		$user = substr($usec,-5,3); 
	    $record['create_time'] = (int)($sec.$user);
        $dbname = $this->curr_db_name;  
        try {     
            $this->mongo->$dbname->$table->insert($record);     
            return true;     
        }     
        catch (MongoCursorException $e)     
        {     
            $this->error = $e->getMessage();     
            return false;     
        }     
    }     
     
    /**   
    * 查询表的记录数   
    *   
    * 参数：   
    * $table:表名   
    *   
    * 返回值：表的记录数   
    */     
    public function countm($table)     
    {     
        $dbname = $this->curr_db_name;     
        return $this->mongo->$dbname->$table->count();     
    }     
     
    /**   
    * 更新记录   
    *   
    * 参数：   
    * $table:表名   
    * $condition:更新条件   
    * $newdata:新的数据记录   
    * $options:更新选择-upsert/multiple   
    *   
    * 返回值：   
    * 成功：true   
    * 失败：false   
    */     
    public function updatem($table, $condition, $newdata, $options=array())     
    {   
        $dbname = $this->curr_db_name;     
        //$options['safe'] = 1;     
        if (!isset($options['multiple']))     
        {     
            $options['multiple'] = 0;          }     
        try {     
	        //print_r($condition);
			//print_r($newdata);
	        //print_r($options);
            $this->mongo->$dbname->$table->update($condition, $newdata, $options);     
            return true;     
        }     
        catch (MongoCursorException $e)     
        {     
            $this->error = $e->getMessage();     
            return false;     
        }          }     
     
    /**   
    * 删除记录   
    *   
    * 参数：   
    * $table:表名   
    * $condition:删除条件   
    * $options:删除选择-justOne   
    *   
    * 返回值：   
    * 成功：true   
    * 失败：false   
    */     
    public function removem($table, $condition, $options=array())     
    {     
        $dbname = $this->curr_db_name;     
        $options['w'] = 1;     
        try {     
            $this->mongo->$dbname->$table->remove($condition, $options);     
            return true;     
        }     
        catch (MongoCursorException $e)     
        {     
            $this->error = $e->getMessage();     
            return false;     
        }          }     
     
    /**   
    * 查找记录   
    *   
    * 参数：   
    * $table:表名   
    * $query_condition:字段查找条件   
    * $result_condition:查询结果限制条件-limit/sort等   
    * $fields:获取字段   
    *   
    * 返回值：   
    * 成功：记录集   
    * 失败：false   
    */     
    public function findm($table, $query_condition=array(), $result_condition=array(), $fields=array(),$ts = 2)     
    {     
        $dbname = $this->curr_db_name;
        if($ts == 2){
	    	$query_condition['_trash'] = array('$exists'=>false);
	    }
	    $cursor = $this->mongo->$dbname->$table->find($query_condition, $fields);      
        if (!empty($result_condition['skip']))     
        {     
            $cursor->skip($result_condition['skip']);     
        }     
        if (!empty($result_condition['limit']))     
        {     
            $cursor->limit($result_condition['limit']);     
        }     
        if (!empty($result_condition['sort']))     
        {     
            $cursor->sort($result_condition['sort']);     
        } 
        if (!empty($result_condition['aggregate']))     
        {     
            $cursor->aggregate($result_condition['aggregate']);     
        } 
        $result = array();     
        try {     
            while ($cursor->hasNext())     
            {     
                $result[] = $cursor->getNext();     
            }     
        }     
        catch (MongoConnectionException $e)     
        {     
            $this->error = $e->getMessage();     
            return false;     
        }     
        catch (MongoCursorTimeoutException $e)     
        {     
            $this->error = $e->getMessage();     
            return false;     
        }     
        return $result;     
    }  
    
   	/***查询数量***/
   	public function findcount($table,$where)
   	{
	   	$dbname = $this->curr_db_name;
	   	if(empty($where)){
	        $result = $this->mongo->$dbname->$table->find()->count();
        } else {
	        $result = $this->mongo->$dbname->$table->find($where)->count();
        }     
        return $result; 
   	} 
     
    /**   
    * 查找一条记录   
    *   
    * 参数：   
    * $table:表名   
    * $condition:查找条件   
    * $fields:获取字段   
    *   
    * 返回值：   
    * 成功：一条记录   
    * 失败：false   
    */     
    public function findOnem($table, $condition, $fields=array())     
    {     
        $dbname = $this->curr_db_name;     
        return $this->mongo->$dbname->$table->findOne($condition, $fields);     
    }     
     
    /**   
    * 获取当前错误信息   
    *   
    * 参数：无   
    *   
    * 返回值：当前错误信息   
    */     
    public function getErrorm()     
    {     
        return $this->error;     
    }

    /*
     * 查找并更新
     *duyong add
     * @params findAndModify ( array $query [, array $update [, array $fields [, array $options ]]] )
     * */
    public function findAndModefym($table,$query,$update,$fields=array()){
        $dbname = $this->curr_db_name;
        return $this->mongo->$dbname->$table->findAndModify($query,$update,$fields);
    }


    /*Mongid*/
    public function getmongoid($uid) {
	    $cc = NEW MongoId($uid);
	    //var_dump($cc);exit;
    }

    
    public function groupm($table, $query_condition=array(), $result_condition=array()){
		//$m = new MongoClient('192.168.1.248:27017');
		//$db = $m->joymedia;
		//$people = $db->live_list;
		$one = '$'.$result_condition[0];
		$two = '$'.$result_condition[1];
		$three = +$result_condition[2];
		$stime = +$query_condition[0];
		 $etime = +$query_condition[1];
		$dbname = $this->curr_db_name;
	   $cursor = $this->mongo->$dbname->$table->aggregateCursor(
		    array(
			    //取出的参数
					array(
				        '$project' => array(
				            "user_id" => 1,
				            "zan_count"=>1,
				            "start_time"=>1,
				            "live_status"=>1,
				            "_trash"=>1,
				            "dou_count"=>1
				        )
				    ),
				    //where条件
				     array('$match' => array('start_time'=>array('$gt'=>$stime,'$lt'=>$etime),'live_status'=>array('$in'=>array(5,9,10,13,14)),'_trash'=>array('$exists'=>false))),				    //分类
					array(
						'$group'=>array(
							'_id'=>$one,
							'points'=>array('$sum'=>$two),
							'pyyyy'=>array('$sum'=>'$dou_count')
						),
					),
					//限制条件
					array('$sort'=>array('points'=>-1)),
					array('$limit'=>$three)
				)

	    );

       	foreach ($cursor as $key => $person) {
			if($person['points'] != 0){
				$result[$key]['user_id'] = $person['_id'];
				$result[$key]['zan'] = $person['points'];
				$result[$key]['dou'] = $person['pyyyy'];
			}
		}
		return $result;
	}
	
	public function groupmc($table,$stime,$etime,$ttype,$user_id){
		$dbname = $this->curr_db_name;
	 	if($ttype == 0){
	     	 $cursor = $this->mongo->$dbname->$table->aggregateCursor(
		    array(
			    //取出的参数
					array(
				        '$project' => array(
					        'addtime' => 1,
					        'type' => 1,
					        'user_id' => 1,
					        'exp' => 1   
				        )
				    ),
				    //where条件
					//$where,
					array('$match' => array('addtime'=>array('$gt'=>$stime,'$lt'=>$etime),'user_id'=>$user_id)),
				    //分类
					array(
						'$group'=>array(
							'_id'=>'$user_id',
							'points'=>array('$sum'=>'$exp')  
						),
					)
				)

			);
    	} else {
	    	 $cursor = $this->mongo->$dbname->$table->aggregateCursor(
		    array(
			    //取出的参数
					array(
				        '$project' => array(
					        'addtime' => 1,
					        'type' => 1,
					        'user_id' => 1,
					        'exp' => 1   
				        )
				    ),
				    //where条件
					//$where,
					array('$match' => array('addtime'=>array('$gt'=>$stime,'$lt'=>$etime),'type'=>$ttype,'user_id'=>$user_id)),
				    //分类
					array(
						'$group'=>array(
							'_id'=>'$user_id',
							'points'=>array('$sum'=>'$exp')  
						),
					)
				)

			);
    	}
		foreach ($cursor as $key => $person) {
			if($person['points'] != 0){
				$result[$key]['user_id'] = $person['_id'];
				$result[$key]['exp'] = $person['points'];
			}
		}
		return $result;
	}
	
	
	public function groupmd($table,$limit){
		$dbname = $this->curr_db_name;
	 	$where = array('$match' => array('addtime'=>array('$gt'=>$stime,'$lt'=>$etime),'type'=>$ttype,'user_id'=>$user_id));
	    $cursor = $this->mongo->$dbname->$table->aggregateCursor(
		    array(
			    //取出的参数
					array(
				        '$project' => array(
					        'beiguanzhu' => 1,
					        'guanzhu' => 1,
					        '_trash' => 1,  
				        )
				    ),
				    //where条件
					//$where,
				    //分类
					array(
						'$group'=>array(
							'_id'=>'$beiguanzhu',
							'count'=>array('$sum'=>1)
						),
					),
					array('$sort'=>array('count'=>-1)),
					array('$limit'=>$limit)
				)

	    );
		foreach ($cursor as $key => $person) {
			if($person['count'] != 0){
				$result[$key]['user_id'] = $person['_id'];
				$result[$key]['num'] = $person['count'];
			}
		}
		return $result;
	}

    /*
     * duyong add 分组返回每组详细信息并且统计数量
     * table 表明
     * keys  根据分组字段
     * initial  分组后返回什么,申明返回后字段的数据类型及字段名称
     * reduce js回调函数将结果处理到initial中
     * condition 分组前的条件
     * */
    public function mongo_group($table,$keys,$initial,$reduce,$condition){
        $dbname = $this->curr_db_name;
        $re = $this->mongo->$dbname->$table->group($keys,$initial,$reduce,$condition);
        return $re;
    }

    /*
     *duyong add 条件查询总量
     * */
    public function countd($table,$where=array())
    {
        $dbname = $this->curr_db_name;
        return $this->mongo->$dbname->$table->count($where);
    }

    public function remove($table,$where=array()){
        $dbname=$this->curr_db_name;
        return $this->mongo->$dbname->$table->remove($where);
    }
	
    public function dropm($table){
        $dbname=$this->curr_db_name;
        return $this->mongo->$dbname->$table->drop();
    }
	
	public function closeConnection() {
		
	}
}     
     
?> 