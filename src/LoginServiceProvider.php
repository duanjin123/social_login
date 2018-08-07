<?php
/**
 * Created by Lincoo.
 * DateTime: 2018/8/7 上午11:07
 * Email: duan.jin@mydreamplus.com
 */

namespace Lincoo\Social_Login;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use Lincoo\Social_Login\QQ\OAuth2;

class LoginServiceProvider extends ServiceProvider
{
    /**
     * 发布配置文件(执行php artisan vendor:publish时生成配置文件)
     */
    public function boot() {
        $source = realpath(__DIR__ . '/../config/config.php');
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('login.php')]);
        }

        $this->mergeConfigFrom($source, 'social-login');
    }


    /**
     * 注册服务提供者
     */
    public function register()
    {
        // 绑定一个单例
        $this->app->singleton('Login', function () {
            $config = $this->app->make('config')->get('social-login');
            return new OAuth2($config['appid'], $config['appkey'], $config['callback']);
        });
    }
}