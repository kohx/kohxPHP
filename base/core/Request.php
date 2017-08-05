<?php
/**
 * stdfw
 *
 * @author kohx <kohei.0728@gmail.com>
 * @copyright 2017
 * @license 
 */
namespace core;

/**
 * クラスや関数単位のコメントはそれらの直前。
 * 
 * このクラスはダミーなので特に意味のある中身はない。
 * 
 * @package core
 * @uses Arr.php
 * @version 1.0.0
 * 
 */
class Request {

    protected $values = [];

    public function __construct()
    {
        
    }

    /**
     * Return server items
     * 
     * @param string $index
     * @param mix $default
     * @return mix;
     */
    public static function server($index = null, $default = null)
    {
        return (func_num_args() === 0) ? $_SERVER : Arr::get($_SERVER, strtoupper($index), $default);
    }

    /**
     * Return's the protocol that the request was made with
     *
     * @return  string
     */
    public static function protocol()
    {
        return (empty(static::server('HTTPS')) ? 'http' : 'https');
    }
    
    /**
	 * Base path
	 *
	 * @return string
	 */
	public static function route()
	{
		$request_uri = self::server('REQUEST_URI');
        $baseurl = static::baseurl();
        
        return trim(str_replace($baseurl, '', trim($request_uri, '/')), '/');
	}

    /**
     * Retuen base url
     * 
     *      Request::baseurl();         // xxx/xxx      
     *      Request::baseurl(true);     // http://xxx/xxx    
     *      Request::baseurl('https);   // https://xxx/xxx    
     * 
     * @param bool|string $protocol
     *      protocol is bool or string
     * @return string
     */
    public static function baseurl($protocol = null)
    {
        $request_uri = self::server('REQUEST_URI');
        $script_name = self::server('SCRIPT_NAME');
        $basepath = '';

        // use index.php
        if (strpos($request_uri, $script_name) === 0)
        {
            $basepath = trim($script_name, '/');
        }
        // not use index.php
        elseif (strpos($request_uri, dirname($script_name)) === 0)
        {
            $basepath = trim(dirname($script_name), '/');
        }

        if ($protocol === true)
        {

            return static::protocol() . '://' . $basepath;
        }
        elseif (is_string($protocol))
        {

            return $protocol . '://' . $basepath;
        }
        else
        {
            return $basepath;
        }
    }

    /**
     * If from ajax when return true
     * 
     * @return bool
     */
    public static function isAjax()
    {
        return (static::server('HTTP_X_REQUESTED_WITH') !== null) and strtolower(static::server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    /**
     * Return refferrer
     * 
     * @param string $default
     * @return string
     */
    public static function referrer($default = null)
    {
        return static::server('HTTP_REFERER', $default);
    }

    /**
     * Return query string
     * 
     * @param string $default
     * @return string
     */
    public static function queryString($default = '')
    {
        return static::server('QUERY_STRING', $default);
    }

    /**
     * Method
     * 
     *      Request::method();			// get method name
     *      Request::method('post');	// is post, return bool
     * 
     * @param string $method
     * @param string $default
     * @return bool|string
     */
    public static function method($method = null, $default = 'GET')
    {
        // Get method
        $request_method = self::server('REQUEST_METHOD', $default);

        // Has not method
        if (is_null($method))
        {
            return $request_method;
        }
        else
        {
            return (bool) ($request_method === strtoupper($method));
        }
    }

    /**
     * Return get items
     * 
     * @param string $key
     * @param string $default
     * @return mix
     */
    public static function Get($key = null, $default = null)
    {
        $return = null;

        if ($key == null)
        {
            $return = $_GET;
        }
        else
        {
            $return = Arr::get($_GET, $key, $default);
        }

        return $return;
    }

    /**
     * return post items
     * 
     * @param string $key
     * @param string $default
     * @return mix
     */
    public static function Post($key = null, $default = null)
    {
        $return = null;

        if ($key == null)
        {
            $return = $_POST;
        }
        else
        {
            $return = Arr::get($_POST, $key, $default);
        }

        return $return;
    }

    /**
     * User agent
     * 
     * @return string
     */
    public static function userAgent()
    {
        $user_agent = self::server('HTTP_USER_AGENT');

        return $user_agent;
    }

    /**
     * accept_lang
     */
    public static function acceptLang()
    {
        return locale_accept_from_http(self::server('HTTP_ACCEPT_LANGUAGE'));
    }
    
    public static function isPhone()
    {
        $user_agent = static::userAgent();

        if (preg_match('/iPhone|iPod|iPad|Android/ui', $user_agent))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function device($strict = false)
    {
        $user_agent = static::userAgent();

        if (!$strict)
        {
            if (preg_match('/iPhone|iPod|iPad/ui', $user_agent))
            {
                return 'ios';
            }
            elseif (preg_match('/Android/ui', $user_agent))
            {
                return 'android';
            }
            else
            {
                return 'pc';
            }
        }
        else
        {
            if (preg_match('/Windows Phone/ui', $user_agent))
            { //UAにAndroidも含まれるので注意
                return 'WindowsPhone';
            }
            else if (preg_match('/Windows/', $user_agent))
            {
                return 'Windows';
            }
            else if (preg_match('/Macintosh/', $user_agent))
            {
                return 'Macintosh';
            }
            else if (preg_match('/iPhone/', $user_agent))
            {
                return 'iPhone';
            }
            else if (preg_match('/iPad/', $user_agent))
            {
                return 'iPad';
            }
            else if (preg_match('/iPod/', $user_agent))
            {
                return 'iPod';
            }
            else if (preg_match('/Android/', $user_agent))
            {
                if (preg_match('/Mobile/', $user_agent))
                {
                    return 'Android';
                }
                else
                {
                    return 'AndroidTablet';
                }
            }
            else
            {
                return false;
            }
        }
    }
}
