
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
                type: 'paycools',
                component: 'Magekc_PayCools/js/view/payment/method-renderer/paycools-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);