<?php

class Akatus_Akatus_Block_Info_Pay extends Mage_Payment_Block_Info
{

    protected function _prepareSpecificInformation($transport = null)
	{

        if (null !== $this->_akatusSpecificInformation) {
			return $this->_akatusSpecificInformation;
		}

		$info = $this->getInfo();
		$transport = new Varien_Object();
		$transport = parent::_prepareSpecificInformation($transport);
	
    	if ($info->getCheckFormapagamento() == 'boleto') {
			echo ("<table>
                        <tbody>
                            <tr>
                                <th>
                                <strong>Forma de Pagamento:</strong>
                                </th>
                            </tr>
                            <tr>
                                <td>Boleto Bancário</td>
                            </tr>
                            <tr>
                                <th>
                                <strong>Segunda Via</strong>
                                </th>
                            </tr>
                            <tr>
                                <td><a href = '{$info->getCheckBoletourl()}' target='_blank'>Imprimir</a></td>
                            </tr>
                        </tbody>
                    </table>");

		} elseif ($info->getCheckFormapagamento() == 'cartaodecredito') {
			$checkBandCC = $info->getCheckCartaobandeira();

			if($checkBandCC == "cartao_amex"){

				$numeroCartao = $info->getCheckNumerocartao();
				$last5 = substr($numeroCartao,(strlen($numeroCartao)-5),strlen($numeroCartao));

				$numCart = "XXXX.XXXXXX." . $last5;

			}else{

				$numeroCartao = $info->getCheckNumerocartao();
				$last4 = substr($numeroCartao,(strlen($numeroCartao)-4),strlen($numeroCartao));

				$numCart = "XXXX.XXXX.XXXX." . $last4;

			}
			
			$cartaoLabel = str_replace("cc_", "", $info->getCheckCartaobandeira());
			switch($cartaoLabel){
				case "cartao_amex":
					$cartao = "Cartão American Express";
					break;
				case "cartao_elo":
					$cartao = "Cartão Elo";
					break;
				case "cartao_master":
					$cartao = "Cartão Master";
					break;
				case "cartao_diners":
					$cartao = "Cartão Diners";
					break;
				case "cartao_visa":
					$cartao = "Cartão Visa";
					break;					
			}

			$array = array(
				(Mage::helper('payment')->__('Bandeira do Cartão')) => ($cartao),
				Mage::helper('payment')->__('Nome') => $info->getCheckNome(),
				Mage::helper('payment')->__('Cpf') => $info->getCheckCpf(),
				(Mage::helper('payment')->__('Numero do Cartão')) => $numCart
			);

		} else {

			$array = array(
				Mage::helper('payment')->__('Bandeira') => $info->getCheckTefbandeira()
			);
		}


        if ($this->isToShowRefund($info->getOrder())) {

            $estornoURL = $this->getEstornoURL($info->getOrder()->getId());

            echo ("<table>
                        <tbody>
                            <tr>
                                <th>
                                    <strong>Estorno:</strong>
                                </th>
                            </tr>
                            <tr>
                                <td><a href ='$estornoURL'>Solicitar estorno</a></td>
                            </tr>
                        </tbody>
                    </table>");
        }

		$transport->addData($array);
		return $transport;
	}
   
    private function isToShowRefund($order) 
    {
        $adminSession = Mage::getSingleton('admin/session', array('name' => 'adminhtml'));
        $isAdmin = $adminSession->isLoggedIn();

        return $isAdmin && $order->getStatus() === Mage_Sales_Model_Order::STATE_COMPLETE;
    }

    private function getEstornoURL($orderId)
    {
        return Mage::helper("adminhtml")->getUrl("refund/refund/index", array("order" => $orderId));
    }
}
