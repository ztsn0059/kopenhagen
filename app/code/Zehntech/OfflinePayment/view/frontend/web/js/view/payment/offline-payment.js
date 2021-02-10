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
                type: 'offlinepayment',
                component: 'Zehntech_OfflinePayment/js/view/payment/method-renderer/offlinepayment'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);