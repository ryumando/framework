<?php

namespace Common\Core;

/**
 * アプリケーションクラス
 */
class App
{
    /** @var string プロジェクトディレクトリ名 */
    public static $project_name;

    /** @var string プロジェクトディレクトリ絶対パス */
    public static $project_path;

    /** @var string 親ディレクトリ絶対パス */
    public static $parent_path;

    /** @var array 設定値 */
    public static $config;

    /** @var string 設定ファイルパス */
    private static $config_file = 'config/app.ini';

    /** @var string ルーティングクラス */
    private static $routing_class = '\Middleware\Routing';

    /**
     * コンストラクタ
     *
     * @param string $start_point
     */
    public function __construct(string $start_point)
    {
        self::$parent_path = self::unifyPath(dirname(__FILE__, 3));
        $paths = explode('/', preg_replace('#^' . preg_quote(self::$parent_path, '#') . '/#', '', self::unifyPath($start_point)));
        self::$project_name = $paths[0];
        self::$project_path = self::$parent_path . '/' . self::$project_name;
    }

    /**
     * 設定ファイル パス設定
     *
     * @param string $path
     * @return object
     */
    public function setConfigFile(string $path): object
    {
        self::$config_file = self::unifyPath(preg_replace('#^\.?/#', '', $path));
        return $this;
    }

    /**
     * ルーティングクラス設定
     *
     * @param string $path
     * @return object
     */
    public function setRoutingClass(string $path): object
    {
        self::$routing_class = self::convertPathToClass($path);
        return $this;
    }

    /**
     * 起動
     *
     * @param boolean $cli
     * @param array $argv
     * @return void
     */
    public function start($cli = false, $argv = []): void
    {
        // 設定値読込
        self::$config = parse_ini_file(self::$project_path . '/' . self::$config_file, true);

        // オートローダー
        spl_autoload_register(function($class) {
            if (preg_match('/^' . preg_quote(preg_replace('/(.)\\\\.*/', '$1', __NAMESPACE__), '/') . '\\\\/i', $class) === 1) {
                $original_path = self::$parent_path . '/' . str_replace('\\', '/', $class) . '.php';
                $path = self::convertPathToSnakeCase($original_path);
                if (file_exists($path)) {
                    $require = $path;
                } else if (file_exists($original_path)) {
                    $require = $original_path;
                }
            }
            if (empty($require)) {
                $original_path = self::$project_path . '/' . str_replace('\\', '/', $class) . '.php';
                $path = self::convertPathToSnakeCase($original_path);
                if (file_exists($path)) {
                    $require = $path;
                } else if (file_exists($original_path)) {
                    $require = $original_path;
                }
            }
            if (!empty($require)) require_once $require;
        });
        
        // 振分処理
        if ($cli) {
            if (!$this->cliDispatch($argv)) throw new \Exception('failed to start process');
        } else {
            if (!$this->httpDispatch()) http_response_code(404);
        }
    }

    /**
     * CLI 振分処理
     *
     * @param array $argv
     * @return boolean
     */
    protected function cliDispatch(array $argv = []): bool
    {
        if (isset($argv[1]) && isset($argv[2])) {
            $class = self::convertPathToClass($argv[1]);
            if (count($argv) > 3) {
                $args = array_slice($argv, 3);
            } else {
                $args = [];
            }
            return $this->dispatch($class, $argv[2], $args);
        }
        return false;
    }

    /**
     * HTTP 振分処理
     *
     * @return boolean
     */
    protected function httpDispatch(): bool
    {
        $routes = self::$routing_class::routes();
        $path = preg_replace('/[?#].*/', '', $_SERVER['REQUEST_URI']);
        foreach ($routes as $route) {
            if (count($route) !== 3) continue;
            if (preg_match('#^/?' . $route[0] . '/?$#i', $path, $matches) === 1) {
                if (count($matches) > 1) {
                    $args = array_slice($matches, 1);
                } else {
                    $args = [];
                }
                return $this->dispatch($route[1], $route[2], $args);
            }
        }
        return false;
    }

    /**
     * 振分処理
     *
     * @param string $class
     * @param string $method
     * @param array $args
     * @return boolean
     */
    private function dispatch(string $class, string $method, array $args = []): bool
    {
        if (class_exists($class)) {
            $instance = new $class();
            if (method_exists($instance, $method)) {
                if (!empty($args)) {
                    $instance->$method(...$args);
                } else {
                    $instance->$method();
                }
                return true;
            }
        } else {
            $converted_class = self::convertPathToUpperCamelCase($class);
            if ($class !== $converted_class) return $this->dispatch($converted_class, $method, $args);
        }
        return false;
    }

    /**
     * ディレクトリ区切り文字を統一
     *
     * @param string $string
     * @return string
     */
    private static function unifyPath(string $string): string
    {
        if (strpos($string, '\\') === false) return $string;
        return str_replace('\\', '/', $string);
    }

    /**
     * パスをクラス名に変換
     *
     * @param string $string
     * @return string
     */
    private static function convertPathToClass(string $string): string
    {
        return str_replace('/', '\\', preg_replace('#^\.?/|\.\w+$#', '', self::unifyPath($string)));
    }

    /**
     * パスをスネークケースに変換
     *
     * @param string $string
     * @return string
     */
    private static function convertPathToSnakeCase(string $string): string
    {
        return strtolower(preg_replace('#([^\\\\/])([A-Z])#', '$1_$2', $string));
    }

    /**
     * パスをアッパーキャメルケースに変換
     *
     * @param string $string
     * @return string
     */
    private static function convertPathToUpperCamelCase(string $string): string
    {
        return str_replace('_', '', ucwords(self::convertPathToSnakeCase($string), '\\\\/_'));
    }

    /**
     * パスをローワーキャメルケースに変換
     *
     * @param string $string
     * @return string
     */
    private static function convertPathToLowerCamelCase(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords(self::convertPathToSnakeCase($string), '_')));
    }

}





