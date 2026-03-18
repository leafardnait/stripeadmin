
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paycools_multi_channel',
                component: 'Magekc_PayCools/js/view/payment/method-renderer/paycools-multi-channel'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);