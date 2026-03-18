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

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProviderMulti implements ConfigProviderInterface
{
    const XML_PATH_CHANNELS = 'payment/paycools_multi_channel/channels';

    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        $channels = $this->scopeConfig->getValue(self::XML_PATH_CHANNELS);
        $channelsArray = $channels ? array_map('trim', explode(',', $channels)) : [];

        return [
            'payment' => [
                'paycools_multi_channel' => [
                    'channels' => $channelsArray
                ]
            ]
        ];
    }
}
