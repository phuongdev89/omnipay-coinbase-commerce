<?php

namespace Omnipay\CoinbaseCommerce\Message;

use GuzzleHttp\Exception\BadResponseException;

/**
 * Class PayByNameRequest
 * @package Omnipay\CoinbaseCommerce\Message
 */
class PayByNameRequest extends AbstractRequest {

	/**
	 * @return mixed
	 */
	public function getPbnTag() {
		return $this->getParameter('pbntag');
	}

	/**
	 * @param $value
	 *
	 * @return PayByNameRequest
	 */
	public function setPbgTag($value) {
		return $this->setParameter('pbntag', $value);
	}

	/**
	 * @return mixed
	 */
	public function getAutoConfirm() {
		return $this->getParameter('auto_confirm');
	}

	/**
	 * @param $value
	 *
	 * @return PayByNameRequest
	 */
	public function setAutoConfirm($value) {
		return $this->setParameter('auto_confirm', $value);
	}

	/**
	 * @return array|mixed
	 * @throws \Omnipay\Common\Exception\InvalidRequestException
	 */
	public function getData() {
		$this->validate('amount', 'currency', 'pbntag');
		return [
			'cmd'          => 'create_transfer',
			'amount'       => $this->getAmount(),
			'currency'     => $this->getCurrency(),
			'pbntag'       => $this->getPbnTag(),
			'auto_confirm' => $this->getAutoConfirm(),
		];
	}

	/**
	 * @param $hmac
	 *
	 * @return array
	 */
	protected function getHeaders($hmac) {
		return [
			'HMAC'         => $hmac,
			'Content-Type' => 'application/x-www-form-urlencoded',
		];
	}

	/**
	 * @param mixed $data
	 *
	 * @return PayByNameResponse|\Omnipay\Common\Message\ResponseInterface
	 */
	public function sendData($data) {
		$hmac = $this->getSig($data, 'create_transfer');
		$data['version'] = 1;
		$data['cmd']     = 'create_transfer';
		$data['key']     = $this->getPublicKey();
		$data['format']  = 'json';
		try {
			$response = $this->httpClient->request('POST', $this->getEndpoint(), $this->getHeaders($hmac), http_build_query($data));
		} catch (BadResponseException $e) {
			$response = $e->getResponse();
		}
		$result = json_decode($response->getBody()->getContents(), true);
		return new PayByNameResponse($this, $result);
	}
}
