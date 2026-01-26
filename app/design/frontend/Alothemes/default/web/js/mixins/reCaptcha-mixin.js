define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_ReCaptchaFrontendUi/js/registry',
    'Magento_ReCaptchaFrontendUi/js/reCaptchaScriptLoader',
    'Magento_ReCaptchaFrontendUi/js/nonInlineReCaptchaRenderer'
], function (Component, $, ko, _, registry, reCaptchaLoader, nonInlineReCaptchaRenderer) {
    'use strict';

    return function(Component) {
        return Component.extend({
            initCaptcha: function () {
                if (typeof this.settings === 'undefined') { 
                    return; 
                }
                this._super();
            },
            
            getIsInvisibleRecaptcha: function () {
                if (typeof this.settings === 'undefined') { 
                    return; 
                }
                return this.settings.invisible;
            }
        });   
    }
});