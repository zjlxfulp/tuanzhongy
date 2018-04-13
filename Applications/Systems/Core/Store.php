<?php
/**
 */
namespace Core;
/**
 * 存储类
 */
class Store
{
    /**
     * 实例数组
     * @var array
     */
    protected static $instance = array();
    
    /**
     * 获取实例
     * @param string
     * @throws \Exception
     */
    public static function instance($dbtype, $single = true, $app_name = 'Common') {
        $flag = $app_name . '_' . $dbtype;
        $dbtype = ucfirst($dbtype);
        $classNume = 'Core\StoreDriver\\' . $dbtype;
        if($single == true) {
            if(empty(self::$instance[$flag])) {
                self::$instance[$flag] = new $classNume($app_name, $single);
            }
        } else {
            self::$instance[$flag] = new $classNume($app_name, $single);
        }

        return self::$instance[$flag];
    }

	/**
     * 关闭实例
     *
     * @param string $config_name
     */
    public static function close($config_name)
    {
        if (isset(self::$instance[$config_name])) {
            self::$instance[$config_name]->closeConnection();
            self::$instance[$config_name] = null;
        }
    }

    /**
     * 关闭所有实例
     */
    public static function closeAll()
    {
        foreach (self::$instance as $connection) {
            $connection->closeConnection();
        }
        self::$instance = array();
    }
}
