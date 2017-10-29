<?php

/**
 * Log class
 * 
 * @package    Deraemon/Log
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require 'HttpException.php';

class Log {

	private static $_logdir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
	private static $_display_on;

	/**
	 * Log Start
	 */
	public static function start($display_on = true)
	{
		static::$_display_on = $display_on;

		set_error_handler('Log::error_handler');
		set_exception_handler('Log::exception_handler');
		register_shutdown_function('Log::shutdown_handler');
	}

	/**
	 * Logger
	 * 
	 * @param string $title
	 * @param string $message
	 * @param int $code
	 */
	public static function logger($title, $message, $code = '')
	{
		$logfile = static::_get_logfile();
		$now = new DateTime('now');
		error_log(sprintf("[%s] %s[%d]\n%s\n\n", $now->format('Y-m-d H:i:s'), $title, $code, $message), 3, $logfile);
	}

	/**
	 * Get log file
	 * 
	 * @return string
	 */
	private static function _get_logfile()
	{
		$logdir = realpath(static::$_logdir);

		$now = new DateTime('now');
		$year_dir = $logdir . DIRECTORY_SEPARATOR . $now->format('Y');
		$month_dir = $year_dir . DIRECTORY_SEPARATOR . $now->format('m');
		$logfile = $month_dir . DIRECTORY_SEPARATOR . $now->format('d') . '.log';

		foreach ([$year_dir, $month_dir] as $dir)
		{
			if (!is_dir($dir))
			{
				mkdir($dir);
				chmod($dir, 0666);
			}
		}

		if (!file_exists($logfile))
		{
			touch($logfile);
			chmod($logfile, 0666);
		}

		return $logfile;
	}

	/**
	 * Format trace
	 * 
	 * @param array $trace
	 * @return array
	 */
	private static function _format_trace($trace)
	{
		$stack = array();
		foreach ($trace as $i => $t)
		{
			// 引数は型が分かるよう文字列に整形
			$args = '';
			if (isset($t['args']) && !empty($t['args']))
			{
				// 配列は一階層目のみ回す
				$args = implode(', ', array_map(function($arg)
						{
							if (is_array($arg))
							{
								$vars = array();
								foreach ($arg as $key => $var)
								{
									$vars[] = sprintf('%s=>%s', static::_formatVar($key), static::_formatVar($var));
								}
								return sprintf('Array[%s]', implode(', ', $vars));
							}
							return static::_formatVar($arg);
						}, $t['args']));
			}

			$stack[] = sprintf(
					'#%d %s(%d): %s%s%s(%s)', //
					$i, //
					(isset($t['file'])) ? $t['file'] : '', // ファイル
					(isset($t['line'])) ? $t['line'] : '', // 行番号
					(isset($t['class'])) ? $t['class'] : '', // クラス名
					(isset($t['type'])) ? $t['type'] : '', // コール方式(->, ::)
					(isset($t['function'])) ? $t['function'] : '', // 関数名、メソッド名
					$args
			);
		}

		return $stack;
	}

	/**
	 * Format Value
	 * 
	 * @param type $var
	 * @return string
	 */
	private static function _formatVar($var)
	{
		if (is_null($var))
		{
			return 'NULL';
		}
		if (is_int($var))
		{
			return sprintf('Int(%d)', $var);
		}
		if (is_float($var))
		{
			return sprintf('Float(%F)', $var);
		}
		if (is_string($var))
		{
			return sprintf('"%s"', $var);
		}
		if (is_bool($var))
		{
			return sprintf('Bool(%s)', $var ? 'true' : 'false');
		}
		if (is_array($var))
		{
			return 'Array';
		}
		if (is_object($var))
		{
			return sprintf('Object(%s)', get_class($var), $var);
		}
		return sprintf('%s', gettype($var));
	}

	/**
	 * Write
	 * 
	 * @param string $message
	 * @return boolean
	 */
	private static function _write($message)
	{
		$logfile = static::_get_logfile();
		$now = new DateTime('now');
		error_log(sprintf("[%s] %s\n", $now->format('Y-m-d H:i:s'), $message), 3, $logfile);
	}

	/**
	 * Displey
	 * 
	 * @param int $status
	 * @param string $title
	 * @param string $message
	 */
	private static function _display($status, $title, $message)
	{
		if (static::$_display_on)
		{

			$message_br = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
			$content = <<< CONTENT
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="robots" content="noindex,nofollow" />
<title>{$title}</title>
</head>
<body>
<h1>{$title}</h1>
<p>{$message_br}</p>
</body>
</html>
CONTENT;
			header(sprintf('HTTP/1.1 %d %s', $status, $title));
			echo $content;
		}
	}

	/**
	 * Error Handler
	 * 
	 * @throws ErrorException
	 */
	public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$labels = array(
			E_WARNING => 'Warning',
			E_NOTICE => 'Notice',
			E_STRICT => 'Strict Standards',
			E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
			E_DEPRECATED => 'Depricated',
			E_USER_ERROR => 'User Fatal Error',
			E_USER_WARNING => 'User Warning',
			E_USER_NOTICE => 'User Notice',
			E_USER_DEPRECATED => 'User Depricated',
		);

		$label = isset($labels[$errno]) ? $labels[$errno] : '';
		$message = sprintf('%s[%d]: %s', $label, $errno, $errstr);

		if (!(error_reporting() & $errno))
		{
			static::_write($message);
			return false;
		}

		throw new ErrorException($message, 0, $errno, $errfile, $errline);
	}

	/**
	 * Exception Handler
	 * 
	 * @param Exception $e
	 */
	public static function exception_handler(Exception $e)
	{
		$status = 500;
		$title = 'Internal Server Error';
		if ($e instanceof HttpException)
		{
			$status = $e->getCode();
			switch ($status)
			{
				case 400:
					$title = 'Bad Request';
					break;

				case 403:
					$title = 'Forbidden';
					break;

				case 404:
					$title = 'Not Found';
					break;

				default:
					break;
			}
		}

		$stack_trace = implode("\n", static::_format_trace($e->getTrace()));

		$message = sprintf(
				"%s[%d]\n'%s' \n in %s:%d\n%s", //
				get_class($e), //
				$e->getCode(), //
				$e->getMessage(), //
				$e->getFile(), //
				$e->getLine(), //
				$stack_trace ? "[ Stack trace ]\n" . $stack_trace . "\n" : ''//
				);

		static::_write($message);
		static::_display($status, $title, $message);
	}

	/**
	 * Shutdown Handler
	 */
	public static function shutdown_handler()
	{
		$error = error_get_last();

		if ($error)
		{
			ob_start();
			ob_implicit_flush(false);

			$errno = $error['type'];
			$errstr = $error['message'];
			$file = $error['file'];
			$line = $error['line'];

			$status = 500;
			$title = 'Internal Server Error';

			$labels = array(
				E_ERROR => 'Fatal Error',
				E_PARSE => 'Parse',
				E_CORE_ERROR => 'Core Error',
				E_CORE_WARNING => 'Core Warning',
				E_COMPILE_ERROR => 'Compile Error',
				E_COMPILE_ERROR => 'Compile Warning',
			);

			$label = isset($labels[$errno]) ? $labels[$errno] : '';

			$message = sprintf("%s[%d]\n%s\n in %s:%d", $label, $errno, $errstr, $file, $line);
			static::_write($message);
			static::_display($status, $title, $message);

			if ($label)
			{
				echo ob_get_clean();
			}
			else
			{
				ob_end_clean();
			}
		}
	}

}
