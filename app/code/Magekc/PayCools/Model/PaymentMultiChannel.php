<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @category Magekc
 * @package  Magekc_PayCools
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)  
 *
 * @author   Kristian Claridad<kristianrafael.claridad@gmail.com>
 */

namespace Magekc\PayCools\Model;

use Magento\Framework\DataObject;

/**
 * PaymentMultiChannel class
 */
class PaymentMultiChannel extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'paycools_multi_channel';
    protected $_code = self::CODE;

    public function assignData(DataObject $data)
    {
        parent::assignData($data);
        $additionalData = $data->getData('additional_data');
        if (!empty($additionalData['channel'])) {
            $this->getInfoInstance()->setAdditionalInformation('channel', $additionalData['channel']);
        }
        return $this;
    }

}
