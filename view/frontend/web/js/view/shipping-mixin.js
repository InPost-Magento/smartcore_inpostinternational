define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote',
    'uiRegistry'
], function ($, ko, quote, registry) {
    'use strict';

    return function (shipping) {
        return shipping.extend({
            defaults: {
                shippingMethodListTemplate: 'Smartcore_InPostInternational/shipping-address/shipping-method-list'
            },

            initialize: function() {
                this._super();
                registry.get('checkout.inpost-geowidget', (component) => {
                    this.geowidget = component;
                });

                quote.shippingMethod.subscribe(this.onShippingMethodChange.bind(this));

                return this;
            },

            onShippingMethodChange: function(method) {
                if (!method) return;

                const inpostGeowidgetMethods = window.checkoutConfig.inpostGeowidget?.geowidgetShippingMethods || [];
                const isInpostGeowidgetMethod = inpostGeowidgetMethods.includes(method.carrier_code);

                if (isInpostGeowidgetMethod && this.geowidget) {
                    const selectedPoint = this.getSelectedPoint(method.carrier_code);
                    if (selectedPoint) {
                        this.geowidget.updateInpostInternationalInputField(selectedPoint);

                        // Check if the point is from localStorage but not from the server
                        const isFromLocalStorage = localStorage.getItem('inpostinternational_locker_id_' + method.carrier_code);
                        const isFromServer = window.checkoutConfig?.inpostGeowidget?.['savedPoint_' + method.carrier_code];

                        if (isFromLocalStorage && !isFromServer) {
                            // Save the point to the backend to ensure it's included in the quote and order
                            this.geowidget.savePoint(selectedPoint);
                        }
                    }
                }
            },

            getTemplateForMethod: function(method) {
                const inpostMethods = window.checkoutConfig.inpostGeowidget?.shippingMethods || [];
                const geowidgetInpostMethods = window.checkoutConfig.inpostGeowidget?.geowidgetShippingMethods || [];

                if(geowidgetInpostMethods.includes(method.carrier_code)) {
                    return 'Smartcore_InPostInternational/shipping-method-item'
                }

                if(inpostMethods.includes(method.carrier_code)) {
                    return 'Smartcore_InPostInternational/shipping-method-item-no-geowidget'
                }

                return 'Magento_Checkout/shipping-address/shipping-method-item';
            },

            showInpostWidget: function() {
                if (this.geowidget) {
                    this.geowidget.showWidget();
                }
            },

            getSelectedPoint: function(carrierCode) {
                if (!this.geowidget) return null;

                if (carrierCode) {
                    try {
                        const storedSpecific = localStorage.getItem('inpostinternational_locker_id_' + carrierCode);
                        if (storedSpecific) {
                            return JSON.parse(storedSpecific);
                        }

                        if (window.checkoutConfig?.inpostGeowidget?.['savedPoint_' + carrierCode]) {
                            return JSON.parse(window.checkoutConfig.inpostGeowidget['savedPoint_' + carrierCode]);
                        }
                    } catch (e) {
                        console.warn('Failed to load carrier-specific InPost point:', e);
                    }
                }
            },

            validateShippingInformation: function () {
                const originalResult = this._super();
                if (!originalResult) return false;

                const method = quote.shippingMethod();
                const inpostGeowidgetMethods = window.checkoutConfig.inpostGeowidget?.geowidgetShippingMethods || [];

                if (method && inpostGeowidgetMethods.includes(method.carrier_code)) {
                    let pointSelected = $('[name="inpostinternational_locker_id"]').val();
                    const selectedPoint = this.getSelectedPoint(method.carrier_code);

                    if (!pointSelected && selectedPoint) {
                        setTimeout(() => {
                            this.geowidget.updateInpostInternationalInputField(selectedPoint);
                            $('button.continue').trigger('click');
                        }, 100);
                        return false;
                    }

                    if (!pointSelected) {
                        this.errorValidationMessage('Please select pickup point');
                        return false;
                    }

                    // Check if the point is from localStorage but not from the server
                    const isFromLocalStorage = localStorage.getItem('inpostinternational_locker_id_' + method.carrier_code);
                    const isFromServer = window.checkoutConfig?.inpostGeowidget?.['savedPoint_' + method.carrier_code];

                    if (isFromLocalStorage && !isFromServer && selectedPoint && this.geowidget) {
                        // Save the point to the backend to ensure it's included in the quote and order
                        this.geowidget.savePoint(selectedPoint);
                    }
                }
                return true;
            }
        });
    };
});
