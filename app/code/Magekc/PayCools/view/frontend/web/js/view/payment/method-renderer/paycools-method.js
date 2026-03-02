define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/redirect-on-success',
    'mage/url'
], function (Component, redirectOnSuccessAction, urlBuilder) {
    'use strict';
        
        return Component.extend({
            defaults: {
                template: 'Magekc_PayCools/payment/paycools-form'
            },

            getCode: function() {
                return 'paycools';
            },

            isActive: function() {
                return true;
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },
    
            afterPlaceOrder: function () {
                this.redirectAfterPlaceOrder = true;
                redirectOnSuccessAction.redirectUrl = urlBuilder.build('paycools/checkout/redirect');
            }
        });
    }
);
