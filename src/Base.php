<?php
/**
 * Created by Lincoo.
 * DateTime: 2018/8/7 上午10:40
 * Email: duan.jin@mydreamplus.com
 */

namespace Lincoo\Social_Login;
use Yurun\Util\HttpRequest;

abstract class Base
{

    public $http;

    public $appid;

    public $appSecret;

    public $callbackUrl;

    public $state;

    public $scope;

    public $result;

    public $accessToken;

    public $openid;

    public $loginAgentUrl;

    /**
     * 构造方法
     * @param string $appid 应用的唯一标识
     * @param string $appSecret appid对应的密钥
     * @param string $callbackUrl 登录回调地址
     */
    public function __construct($appid = null, $appSecret = null, $callbackUrl = null)
    {
        $this->appid = $appid;
        $this->appSecret = $appSecret;
        $this->callbackUrl = $callbackUrl;
        $this->http = new HttpRequest;
    }

    /**
     * 把jsonp转为php数组
     * @param string $jsonp jsonp字符串
     * @param boolean $assoc 当该参数为true时，将返回array而非object
     * @return array
     */
    public function jsonp_decode($jsonp, $assoc = false)
    {
        $jsonp = trim($jsonp);
        if(isset($jsonp[0]) && $jsonp[0] !== '[' && $jsonp[0] !== '{') {
            $begin = strpos($jsonp, '(');
            if(false !== $begin)
            {
                $end = strrpos($jsonp, ')');
                if(false !== $end)
                {
                    $jsonp = substr($jsonp, $begin + 1, $end - $begin - 1);
                }
            }
        }
        return json_decode($jsonp, $assoc);
    }

    /**
     * http_build_query — 生成 URL-encode 之后的请求字符串
     * @param array $query_data
     * @param string $numeric_prefix
     * @param string $arg_separator
     * @param int $enc_type
     * @return mixed
     */
    public function http_build_query($query_data, $numeric_prefix = '', $arg_separator = '&', $enc_type = PHP_QUERY_RFC1738)
    {
        return \http_build_query($query_data, $numeric_prefix, $arg_separator, $enc_type);
    }


    /**
     * 获取state值
     * @param string $state
     * @return string
     */
    protected function getState($state = null)
    {
        if(null === $state)
        {
            if(null === $this->state)
            {
                $this->state = md5(\uniqid('', true));
            }
        }
        else
        {
            $this->state = $state;
        }
        return $this->state;
    }


    /**
     * 检测state是否相等
     * @param string $storeState 本地存储的正确的state
     * @param string $state 回调传递过来的state
     * @return bool
     */
    public function checkState($storeState, $state = null)
    {
        if(null === $state)
        {
            if(null === $this->state)
            {
                if(isset($_GET['state']))
                {
                    $state = $_GET['state'];
                }
                else
                {
                    $state = '';
                }
            }
            else
            {
                $state = $this->state;
            }
        }
        return $storeState === $state;
    }


    /**
     * 第一步:获取登录页面跳转url
     * @param string $callbackUrl 登录回调地址
     * @param string $state 状态值，不传则自动生成，随后可以通过->state获取。用于第三方应用防止CSRF攻击，成功授权后回调时会原样带回。一般为每个用户登录时随机生成state存在session中，登录回调中判断state是否和session中相同
     * @param array $scope 请求用户授权时向用户显示的可进行授权的列表。可空
     * @return string
     */
    public abstract function getAuthUrl($callbackUrl = null, $state = null, $scope = null);


    /**
     * 第二步:处理回调并获取access_token。与getAccessToken不同的是会验证state值是否匹配，防止csrf攻击。
     * @param string $storeState 存储的正确的state
     * @param string $code 第一步里$callbackUrl地址中传过来的code，为null则通过get参数获取
     * @param string $state 回调接收到的state，为null则通过get参数获取
     * @return string
     */
    public function getAccessToken($storeState = '', $code = null, $state = null)
    {
        if(!$this->checkState($storeState, $state))
        {
            throw new \InvalidArgumentException('state验证失败');
        }
        return $this->__getAccessToken($storeState, $code, $state);
    }


    /**
     * 第二步:处理回调并获取access_token。与getAccessToken不同的是会验证state值是否匹配，防止csrf攻击。
     * @param string $storeState 存储的正确的state
     * @param string $code 第一步里$callbackUrl地址中传过来的code，为null则通过get参数获取
     * @param string $state 回调接收到的state，为null则通过get参数获取
     * @return string
     */
    protected abstract function __getAccessToken($storeState, $code = null, $state = null);
    /**
     * 获取用户资料
     * @param string $accessToken
     * @return array
     */
    public abstract function getUserInfo($accessToken = null);

    /**
     * 刷新AccessToken续期
     * @param string $refreshToken
     * @return bool
     */
    public abstract function refreshToken($refreshToken);
    /**
     * 检验授权凭证AccessToken是否有效
     * @param string $accessToken
     * @return bool
     */
    public abstract function validateAccessToken($accessToken = null);

    /**
     * 输出登录代理页内容，用于解决只能设置一个回调域名/地址的问题
     * @throws \ReflectionException
     */
    public function displayLoginAgent()
    {
        $ref = new \ReflectionClass(get_called_class());
        echo file_get_contents(dirname($ref->getFileName()) . '/loginAgent.html');
    }

    /**
     * 获取回调地址
     * @return string
     */
    public function getRedirectUri()
    {
        return null === $this->loginAgentUrl ? $this->callbackUrl : ($this->loginAgentUrl . '?' . http_build_query(array('redirect_uri'=>$this->callbackUrl)));
    }
}