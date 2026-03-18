define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/redirect-on-success',
    'mage/url',
    'ko'
], function (Component,redirectOnSuccessAction, urlBuilder, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magekc_PayCools/payment/paycools-multi-channel',
            channels: window.checkoutConfig.payment.paycools_multi_channel.channels || [],
            selectedChannel: null
        },
        isRadioButtonVisible : function() {
            return false;
        },
        getCode: function () {
            return 'paycools_multi_channel';
        },
        getChannels: function () {
            var channels = window.checkoutConfig.payment.paycools_multi_channel.channels || [];
            var map = {
                'GCASH_URL': 'GCASH Webpay',
                'PAYMAYA_URL': 'PAYMAYA Webpay',
                'GRPY_URL': 'GRABPAY Webpay',
                'COINS_URL': 'COINS Webpay',
                'BPIA_URL': 'BPI Webpay',
                'UBPB_URL': 'UBP Webpay',
                'ZFB_URL': 'Alipay Webpay',
                'MASTER_CARD_URL': 'MASTER Credit Card',
                'VISA_CARD_URL': 'VISA Credit Card',
                'CARD_URL': 'Maya Card Webpay'
            };
            return channels.map(function(code){ return {code: code, label: map[code] || code}; });
        },

        selectChannel: function (channel) {
            this.selectedChannel = channel;
        },
        getData: function () {
            return {
                method: this.getCode(),
                additional_data: {
                    channel: this.selectedChannel
                }
            };
        },
        afterPlaceOrder: function () {
                this.redirectAfterPlaceOrder = true;
                redirectOnSuccessAction.redirectUrl = urlBuilder.build('paycools/checkout/redirectpaymentchannel');
            }

    });
});