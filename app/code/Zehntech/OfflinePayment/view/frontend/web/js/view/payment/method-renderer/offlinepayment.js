define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Zehntech_OfflinePayment/payment/offlinepayment'
            },
            /** Returns send check to info */
            getMailingAddress: function () {
                return window.checkoutConfig.payment.offlinepayment.mailingAddress;
            },
            /** Returns payable to info */
            /*getPayableTo: function() {
            return window.checkoutConfig.payment.checkmo.payableTo;
            }*/
        });
    }
);