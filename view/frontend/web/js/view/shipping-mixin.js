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

            initialize: function () {
                this._super();
                registry.get('checkout.inpost-geowidget', function (component) {
                    this.geowidget = component;
                }.bind(this));

                quote.shippingMethod.subscribe(this.onShippingMethodChange.bind(this));

                return this;
            },

            getGeowidgetMethods: function () {
                var value = window.checkoutConfig.inpostGeowidget &&
                    window.checkoutConfig.inpostGeowidget.geowidgetShippingMethods;

                if (Array.isArray(value)) {
                    return value;
                }

                return typeof value === 'string' && value.length
                    ? value.split(',').map(function (item) { return item.trim(); }).filter(Boolean)
                    : [];
            },

            getShippingMethods: function () {
                var value = window.checkoutConfig.inpostGeowidget &&
                    window.checkoutConfig.inpostGeowidget.shippingMethods;

                if (Array.isArray(value)) {
                    return value;
                }

                return typeof value === 'string' && value.length
                    ? value.split(',').map(function (item) { return item.trim(); }).filter(Boolean)
                    : [];
            },

            onShippingMethodChange: function (method) {
                var selectedPoint;

                if (!method || !this.geowidget) {
                    return;
                }

                if (this.getGeowidgetMethods().indexOf(method.carrier_code) === -1) {
                    return;
                }

                selectedPoint = this.geowidget.getSelectedPoint(method);

                if (selectedPoint) {
                    this.geowidget.updateInpostinternationalInputField(
                        selectedPoint,
                        this.geowidget.buildContext(method)
                    );
                } else {
                    this.geowidget.syncCurrentMethodSelection();
                }
            },

            getTemplateForMethod: function (method) {
                if (this.getGeowidgetMethods().indexOf(method.carrier_code) !== -1) {
                    return 'Smartcore_InPostInternational/shipping-method-item';
                }

                if (this.getShippingMethods().indexOf(method.carrier_code) !== -1) {
                    return 'Smartcore_InPostInternational/shipping-method-item-no-geowidget';
                }

                return 'Magento_Checkout/shipping-address/shipping-method-item';
            },

            showInpostWidget: function (method) {
                if (this.geowidget) {
                    this.geowidget.showWidget(method);
                }
            },

            getSelectedPoint: function (method) {
                return this.geowidget ? this.geowidget.getSelectedPoint(method) : null;
            },

            validateShippingInformation: function () {
                var originalResult = this._super();
                var method = quote.shippingMethod();
                var selectedPoint;
                var context;
                var pointSelected;

                if (!originalResult) {
                    return false;
                }

                if (!method || this.getGeowidgetMethods().indexOf(method.carrier_code) === -1 || !this.geowidget) {
                    return true;
                }

                context = this.geowidget.buildContext(method);
                pointSelected = this.geowidget.findHiddenField(context).val();
                selectedPoint = this.geowidget.getSelectedPoint(method);

                if (!pointSelected && selectedPoint) {
                    this.geowidget.updateInpostinternationalInputField(selectedPoint, context);
                    pointSelected = this.geowidget.findHiddenField(context).val();
                }

                if (!pointSelected) {
                    this.errorValidationMessage('Please select pickup point');
                    return false;
                }

                if (selectedPoint && !window.checkoutConfig.inpostGeowidget['savedPoint_' + method.carrier_code]) {
                    this.geowidget.savePoint(selectedPoint, context);
                }

                return true;
            }
        });
    };
});
