<?php
ini_set('error_reporting', E_ALL);
//ini_set('error_reporting', E_ALL & ~E_NOTICE  );
ini_set('display_errors', '0');
ini_set('log_errors', '1');
require_once './classese/Log.php';

Log::start();

Log::logger('info', 'あいうえお');

class Create_error {

	public $c = null;

	public static function er()
	{
		NotExsistsClass::hogehoge;
	}

	public static function er0($a, $b)
	{
		echo $a + $b;
	}

	public static function er1()
	{
		echo $b;
	}

	public static function er2(&$var)
	{
		$var += 10;
	}

	public static function er3()
	{
		return function()
		{
			return 'Create_error';
		};
	}

	public static function er4()
	{

		$e = self::er3();
		$e = new $e();
	}

}

//try
//{
	if (isset($_POST['exception']) && isset($_POST['status']) && ctype_digit($_POST['status']))
	{
		throw new HttpException('Test', intval($_POST['status']));
	}

	if (isset($_POST['error']))
	{
		switch ($_POST['error'])
		{
			case 'error':
				Create_error::er();
				break;
			
			case 'warning':
				Create_error::er0();
				break;

			case 'notice':
				Create_error::er1();
				break;

			case 'strict':
				$var = 1;
				Create_error::er2( ++$var);
				break;

			case 'recoverable':
				echo 'recoverable!';
				break;

			case 'depricated':
				echo 'depricated!';
				break;

//
			case 'user_error':
				trigger_error('Test', E_USER_ERROR);
				break;

			case 'user_warning':
				trigger_error('Test', E_USER_WARNING);
				break;

			case 'user_notice':
				trigger_error('Test', E_USER_NOTICE);
				break;

			case 'user_depricated':
				trigger_error('Test', E_USER_DEPRECATED);
				break;

			default:
				break;
		}
	}
//}
//catch (HttpException $e)
//{
//	echo $e->getCode();
//}
//catch (Exception $e)
//{
//		echo $e->getCode();
//}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="robots" content="noindex,nofollow" />
		<title>エラー制御</title>
	</head>
	<body>
		<form action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES, 'UTF-8') ?>" method="post">
			<label><input type="radio" name="status" value="400" />400</label>
			<label><input type="radio" name="status" value="403" />403</label>
			<label><input type="radio" name="status" value="404" />404</label>
			<label><input type="radio" name="status" value="500" />500</label>
			<input type="submit" name="exception" value="Exception" />
			<br />
			<button type="submit" name="error" value="error">Error</button>
			<button type="submit" name="error" value="warning">Warning</button>
			<button type="submit" name="error" value="notice">Notice</button>
			<button type="submit" name="error" value="strict">Strict</button>
			<button type="submit" name="error" value="recoverable">Recoverable</button>
			<button type="submit" name="error" value="depricated">Depricated</button>
			<br />
			<button type="submit" name="error" value="user_error">User error</button>
			<button type="submit" name="error" value="user_warning">User Warning</button>
			<button type="submit" name="error" value="user_notice">User Notice</button>
			<button type="submit" name="error" value="user_depricated">User Depricated</button>
		</form>
	</body>
</html>	