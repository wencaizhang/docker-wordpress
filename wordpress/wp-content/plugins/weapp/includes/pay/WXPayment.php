<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 2018/9/8
 * Time: 15:42
 */

include_once __DIR__ . '/WXPayConfig.php';
class WXPayment
{
	/**
	 * @param $config
	 *
	 * @return null|TransferHelper
	 */
	public static function transfer($config)
	{
		static $Transfer = null;
		if (!$Transfer) {
			require_once __DIR__ . '/TransferHelper.php';
			$Transfer = new TransferHelper($config);
		}
		return $Transfer;
	}
}