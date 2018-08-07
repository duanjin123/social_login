<?php
/**
 * Created by Lincoo.
 * DateTime: 2018/8/7 上午11:07
 * Email: duan.jin@mydreamplus.com
 */

namespace Lincoo\Social_Login;


class Login
{
    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    protected $app;


    /**
     * Client constructor.
     */
    public function __construct()
    {
        $this->app = app('Login');
    }

}