<?php
/**
 * 框架主类
 */
class Eason {
    public static ?array $config = null;
    static function init() {
        //自动加载 _分割
        spl_autoload_register(function ($className) {
            require_once self::join(ROOT_DIR, str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php');
        });
        define('ROOT_DIR', realpath('.'));
        define('LOG_DIR', self::join(ROOT_DIR, 'log'));
        define('SITE_URL', dirname($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']));
        define('IS_DEVELOP', true);

        self::$config = parse_ini_file(ROOT_DIR . "/config/site.ini", true);

        self::bootstrap();
    }

    static function join(...$path):string {
        return implode(DIRECTORY_SEPARATOR, $path);
    }
    /**
     * 引导
     * @return void
     */
    static function bootstrap() {
        $routeInfo = Eason::getRoute();
        if (file_exists($routeInfo[0])) {
            if (class_exists($routeInfo[1])) {
                try {
                    //记录路由到的控制器
                    define('ROUTE_CONTROLLER', $routeInfo[1]);
                    define('ROUTE_ACTION', $routeInfo[2]);
                    $className = $routeInfo[1];
                    $obj = new $className();
                    $obj->execute();
                } catch (frame_exception_fatal $e) {
                    //致命异常
                    $fatalStr = sprintf("exception occured,errno:[%s], msg:[%s] \n#  %s(%s) \n%s",
                        $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
                    lib_log::fatal('index', $fatalStr);
                } catch (Exception $e) {
                    lib_log::trace('index', $e->getMessage());
                }
            } else {
                echo 'controler nofound.';
            }
        } else {
            echo 'file nofound.';
        }
    }
    /**
     * 获取路由信息
     *
     * @return array
     */
    static function getRoute() {
        if ('/index.php' === $_SERVER['SCRIPT_NAME']) {
            $pathStr = $_SERVER['REQUEST_URI'];
        } else {
            $pathStr = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']) - strlen('/index.php'));
        }
        if (!preg_match("/^\/([a-zA-Z0-9]+)\/?([a-zA-Z0-9]+)?/", $pathStr, $matchs)) {
            return [
                0 => 'app/controller/index.php',
                1 => 'app_controller_index',
                2 => 'actionIndex',
            ];
        }
        if (empty($matchs[2])) {
            $matchs[2] = 'Index';
        }
        return [
            0 => 'app/controller/' . $matchs[1] . '.php',
            1 => 'app_controller_' . $matchs[1],
            2 => 'action' . $matchs[2],
        ];
    }

    /**
     * 展示视图
     * @param $name string 视图名称
     */
    static function displayPage($name, $param = []) {
        extract($param);
        $res = debug_backtrace();
        preg_match("/_([^_]+)$/", $res[1]['class'], $res);
        require_once implode(DIRECTORY_SEPARATOR, [ROOT_DIR, 'app', 'view', $res[1], $name . '.php']);
    }
}


