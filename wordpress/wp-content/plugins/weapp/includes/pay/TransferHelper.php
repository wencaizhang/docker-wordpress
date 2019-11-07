<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 2018/1/25
 * Time: 15:08
 */

require_once __DIR__ . '/PayHelper.php';

class TransferHelper extends PayHelper
{
	protected function createXml()
	{
		if (!isset($parameters["partner_trade_no"])) { // 商户订单号
			throw new PayException("缺少必填参数: partner_trade_no！" . "<br>");
		}
		if (!isset($parameters['openid'])) { // 用户openid
			throw new PayException("缺少必填参数: openid！" . "<br>");
		}

		if (!isset($parameters['check_name'])) { // 校验用户姓名选项
			$parameters['check_name'] = 'NO_CHECK';
		}

		if ($parameters['check_name'] == 'FORCE_CHECK') {
			if (!isset($parameters['re_user_name']) || !$parameters['re_user_name']) {
				// 收款用户真实姓名。如果check_name设置为FORCE_CHECK，则必填用户真实姓名
				throw new PayException("缺少必填参数: re_user_name！" . "<br>");
			}
		}

		if (!isset($parameters['desc']) || !$parameters['desc']) {
			// 企业付款操作说明信息
			throw new PayException("缺少必填参数: desc！" . "<br>");
		}

		if (!isset($parameters['amount'])) { // 金额
			throw new PayException("缺少必填参数: amount！" . "<br>");
		}

		if (!isset($parameters["spbill_create_ip"])) {
			$parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; //终端ip
		}

		$parameters["mch_appid"] = $this->appId; //公众账号ID
		$parameters["mchid"]     = $this->mchId; //商户号
		$parameters["nonce_str"] = $this->createNonceStr(); //随机字符串
		$parameters["sign"]      = $this->getSign($parameters); //签名
		$xml                     = $this->arrayToXml($parameters);

		return $xml;
	}

	public function getResult()
	{
		$this->postXmlSSL();
		$this->result = $this->xmlToArray($this->response);

		return $this->result;
	}

	/**
	 * 企业付款
	 * 用于企业向微信用户个人付款
	 *
	 * @param array $parameters
	 *
	 * @return bool|mixed
	 * @throws PayException
	 */
	public function toBalance($parameters = [])
	{
		$api = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

		$xml            = $this->createToBalanceXML($parameters);
		$this->response = $this->postSSL($api, $xml);
		$this->result   = $this->xmlToArray($this->response);

		return $this->result;
	}

	public function queryBalanceResult($partner_trade_no)
	{
		$api = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';

		$parameters['partner_trade_no'] = $partner_trade_no;

		$xml            = $this->createQueryTransferResultXML($parameters);
		$this->response = $this->postSSL($api, $xml);
		$this->result   = $this->xmlToArray($this->response);

		return $this->result;
	}

	public function toBankCard($parameters = [])
	{
		$api = 'https://api.mch.weixin.qq.com/mmpaysptrans/query_bank';

		$xml            = $this->createToBalanceXML($parameters);
		$this->response = $this->postSSL($api, $xml);
		$this->result   = $this->xmlToArray($this->response);

		return $this->result;
	}

	public function queryBankCardResult($partner_trade_no)
	{
		$api = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';

		$parameters['partner_trade_no'] = $partner_trade_no;

		$xml            = $this->createQueryTransferResultXML($parameters);
		$this->response = $this->postSSL($api, $xml);
		$this->result   = $this->xmlToArray($this->response);

		return $this->result;
	}

	private function createToBalanceXML($parameters)
	{
		if (!isset($parameters["partner_trade_no"])) { // 商户订单号
			throw new PayException("缺少必填参数: partner_trade_no！" . "<br>");
		}

		if (!isset($parameters['openid'])) { // 用户openid
			throw new PayException("缺少必填参数: openid！" . "<br>");
		}

		if (!isset($parameters['check_name'])) { // 校验用户姓名选项
			$parameters['check_name'] = 'NO_CHECK';
		}

		if ($parameters['check_name'] == 'FORCE_CHECK') {
			if (!isset($parameters['re_user_name']) || !$parameters['re_user_name']) {
				// 收款用户真实姓名。如果check_name设置为FORCE_CHECK，则必填用户真实姓名
				throw new PayException("缺少必填参数: re_user_name！" . "<br>");
			}
		}

		if (!isset($parameters['desc']) || !$parameters['desc']) {
			// 企业付款操作说明信息
			throw new PayException("缺少必填参数: desc！" . "<br>");
		}

		if (!isset($parameters['amount'])) { // 金额
			throw new PayException("缺少必填参数: amount！" . "<br>");
		}

		if (!isset($parameters["spbill_create_ip"])) {
			$parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; //终端ip
		}

		$parameters = $this->buildParameters($parameters);
		$xml        = $this->arrayToXml($parameters);

		return $xml;
	}

	private function createQueryTransferResultXML($parameters)
	{
		if (!isset($parameters["partner_trade_no"])) { // 商户订单号
			throw new PayException("缺少必填参数: partner_trade_no！" . "<br>");
		}

		$parameters['mch_id']    = $this->mchId;
		$parameters['appid']     = $this->appId;
		$parameters["nonce_str"] = $this->createNonceStr(); //随机字符串
		$parameters["sign"]      = $this->getSign($parameters); //签名
		$xml                     = $this->arrayToXml($parameters);

		return $xml;
	}

	private function createToBankCardXML($parameters)
	{
		if (!isset($parameters["partner_trade_no"])) { // 商户订单号
			throw new PayException("缺少必填参数: partner_trade_no！" . "<br>");
		}

		if (!isset($parameters['enc_bank_no'])) { // 收款方银行卡号
			throw new PayException("缺少必填参数: enc_bank_no！" . "<br>");
		}

		if (!isset($parameters['enc_true_name'])) { // 收款方用户名
			throw new PayException("缺少必填参数: enc_true_name！" . "<br>");
		}

		if (!isset($parameters['bank_code'])) { // 收款方开户行
			throw new PayException("缺少必填参数: bank_code！" . "<br>");
		}

		if (!isset($parameters['amount'])) { // 付款金额, 不含手续费
			throw new PayException("缺少必填参数: amount！" . "<br>");
		}

		if (!isset($parameters["spbill_create_ip"])) {
			$parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; //终端ip
		}

		$parameters = $this->buildParameters($parameters);
		$xml        = $this->arrayToXml($parameters);

		return $xml;
	}

	protected function buildParameters($parameters)
	{
		$parameters["mch_appid"] = $this->appId; //公众账号ID
		$parameters["mchid"]     = $this->mchId; //商户号
		$parameters["nonce_str"] = $this->createNonceStr(); //随机字符串
		$parameters["sign"]      = $this->getSign($parameters); //签名

		return $parameters;
	}
}