define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Smartcore_InPostInternational/js/inpost-geowidget-coordinator'
], function (Component, ko, $, quote, coordinator) {
    'use strict';

    function clone(value) {
        return value ? JSON.parse(JSON.stringify(value)) : null;
    }

    return Component.extend({
        defaults: {
            template: 'Smartcore_InPostInternational/inpost-geowidget',
            selectedPoint: ko.observable(null),
            isVisible: ko.observable(false),
            pointsVersion: ko.observable(0)
        },

        providerId: 'smartcore-international',

        initialize: function () {
            this._super();
            this.providerRegistered = false;
            this.currentClearRequestKey = null;
            this.lastClearedContextKey = null;
            this.registerProvider();
            this.syncCurrentMethodSelection();
            quote.shippingMethod.subscribe(this.syncCurrentMethodSelection.bind(this));
            quote.shippingAddress.subscribe(this.syncCurrentMethodSelection.bind(this));
            return this;
        },

        registerProvider: function () {
            if (this.providerRegistered) {
                return;
            }

            this.providerRegistered = true;

            coordinator.registerProvider({
                id: this.providerId,
                selectionEventName: 'onpointselect',
                getCssUrl: function (context) {
                    return context.isSandbox
                        ? 'https://sandbox-global-geowidget-sdk.easypack24.net/inpost-geowidget.css'
                        : 'https://geowidget.inpost-group.com/inpost-geowidget.css';
                },
                getScriptUrl: function (context) {
                    return context.isSandbox
                        ? 'https://sandbox-global-geowidget-sdk.easypack24.net/inpost-geowidget.js'
                        : 'https://geowidget.inpost-group.com/inpost-geowidget.js';
                },
                getWidgetAttributes: function (context) {
                    var attributes = {
                        token: context.token,
                        config: 'parcelCollect',
                        onpoint: 'onpointselect',
                        country: context.countryList
                    };

                    if (context.isSandbox) {
                        attributes.sandbox = 'true';
                    }

                    return attributes;
                },
                onSelected: this.handlePointSelection.bind(this)
            });
        },

        parseConfigList: function (value) {
            if (Array.isArray(value)) {
                return value;
            }

            if (typeof value !== 'string' || value.length === 0) {
                return [];
            }

            return value.split(',').map(function (item) {
                return item.trim();
            }).filter(Boolean);
        },

        getShippingMethodValue: function (method) {
            return method ? method.carrier_code + '_' + method.method_code : null;
        },

        getShippingCountry: function () {
            var shippingAddress = quote.shippingAddress();

            return shippingAddress && shippingAddress.countryId ? shippingAddress.countryId : null;
        },

        buildContext: function (method) {
            var widgetConfig = window.checkoutConfig.inpostGeowidget || {};
            var context;
            var countryId;
            var countries;

            if (!method) {
                return null;
            }

            countryId = this.getShippingCountry();
            countries = this.parseConfigList(widgetConfig.geowidgetCountries);

            if (countryId && countries.indexOf(countryId) !== -1) {
                countries.splice(countries.indexOf(countryId), 1);
                countries.unshift(countryId);
            }

            context = {
                providerId: this.providerId,
                carrierCode: method.carrier_code,
                methodCode: method.method_code,
                methodValue: this.getShippingMethodValue(method),
                countryId: countryId,
                countryList: countries.join(','),
                token: widgetConfig.token,
                isSandbox: !!widgetConfig.isSandbox
            };

            return context;
        },

        getContextKey: function (context) {
            return coordinator.getContextKey(this.providerId, context);
        },

        getStorageKey: function (context) {
            return 'inpost:' + this.providerId + ':' +
                context.carrierCode + ':' +
                context.methodCode + ':' +
                (context.countryId || 'unknown');
        },

        enrichPoint: function (context, point) {
            var payload = clone(point) || {};

            payload._inpostContext = {
                providerId: this.providerId,
                carrierCode: context.carrierCode,
                methodCode: context.methodCode,
                countryId: context.countryId
            };

            return payload;
        },

        extractPointCountry: function (point) {
            if (!point) {
                return null;
            }

            return point.country ||
                point.country_code ||
                (point.address && (point.address.country || point.address.country_code)) ||
                (point.address_details && (
                    point.address_details.country ||
                    point.address_details.country_code
                )) ||
                null;
        },

        isPointCompatible: function (context, point) {
            var pointContext = point && point._inpostContext;
            var pointCountry;

            if (!context || !point) {
                return false;
            }

            if (pointContext) {
                return pointContext.providerId === this.providerId &&
                    pointContext.carrierCode === context.carrierCode &&
                    pointContext.methodCode === context.methodCode &&
                    pointContext.countryId === context.countryId;
            }

            pointCountry = this.extractPointCountry(point);

            return !!pointCountry && pointCountry === context.countryId;
        },

        getServerPoint: function (context) {
            var savedPoint;

            try {
                savedPoint = window.checkoutConfig &&
                    window.checkoutConfig.inpostGeowidget &&
                    window.checkoutConfig.inpostGeowidget['savedPoint_' + context.carrierCode];

                if (!savedPoint) {
                    return null;
                }

                savedPoint = JSON.parse(savedPoint);
            } catch (error) {
                return null;
            }

            return this.isPointCompatible(context, savedPoint) ? savedPoint : null;
        },

        getLegacyStoragePoint: function (context) {
            var keys = [
                'inpostinternational_locker_id_' + context.carrierCode,
                'inpostinternational_locker_id'
            ];
            var storedPoint = null;

            keys.some(function (key) {
                var value = localStorage.getItem(key);

                if (!value) {
                    return false;
                }

                try {
                    storedPoint = JSON.parse(value);
                } catch (error) {
                    storedPoint = null;
                }

                return !!storedPoint;
            });

            return this.isPointCompatible(context, storedPoint) ? storedPoint : null;
        },

        getStoredPoint: function (context) {
            var storedPoint;
            var rawValue;

            if (!context) {
                return null;
            }

            rawValue = localStorage.getItem(this.getStorageKey(context));

            if (rawValue) {
                try {
                    storedPoint = JSON.parse(rawValue);
                } catch (error) {
                    storedPoint = null;
                }
            }

            if (this.isPointCompatible(context, storedPoint)) {
                return storedPoint;
            }

            return this.getServerPoint(context) || this.getLegacyStoragePoint(context);
        },

        bumpPointsVersion: function () {
            this.pointsVersion(this.pointsVersion() + 1);
        },

        getSelectedPoint: function (method) {
            this.pointsVersion();
            return this.getStoredPoint(this.buildContext(method));
        },

        findMethodRow: function (context) {
            return $('input[type="radio"][value="' + context.methodValue + '"]').closest('tr');
        },

        findHiddenField: function (context) {
            var row = this.findMethodRow(context);
            var field = row.find('input[name="inpostinternational_locker_id"]').first();

            if (field.length) {
                return field;
            }

            return $('input[name="inpostinternational_locker_id"]').first();
        },

        updateInpostinternationalInputField: function (point, context) {
            var field = this.findHiddenField(context);

            if (field.length) {
                field.val(point ? point.name : '');
            }
        },

        selectShippingMethod: function (context) {
            var input = $('input[type="radio"][value="' + context.methodValue + '"]').first();

            if (input.length && !input.is(':checked')) {
                input.prop('checked', true).trigger('click');
            }
        },

        savePoint: function (point, context) {
            var payload = point ? this.enrichPoint(context, point) : null;
            var pointData = payload ? JSON.stringify(payload) : '';

            if (payload) {
                this.lastClearedContextKey = null;
                localStorage.setItem(this.getStorageKey(context), pointData);
                localStorage.setItem('inpostinternational_locker_id', pointData);
                localStorage.setItem('inpostinternational_locker_id_' + context.carrierCode, pointData);
            }

            this.bumpPointsVersion();

            return $.ajax({
                url: window.checkoutConfig.inpostGeowidget.savePointUrl,
                type: 'POST',
                data: {
                    point_data: pointData,
                    point_id: payload ? payload.name : '',
                    carrier_code: context.carrierCode,
                    clear: payload ? 0 : 1
                },
                dataType: 'json'
            });
        },

        clearPoint: function (context) {
            var requestKey;

            if (!context) {
                return $.Deferred().resolve().promise();
            }

            localStorage.removeItem(this.getStorageKey(context));
            localStorage.removeItem('inpostinternational_locker_id');
            localStorage.removeItem('inpostinternational_locker_id_' + context.carrierCode);
            this.updateInpostinternationalInputField(null, context);
            this.bumpPointsVersion();

            requestKey = this.getContextKey(context);

            if (this.currentClearRequestKey === requestKey || this.lastClearedContextKey === requestKey) {
                return $.Deferred().resolve().promise();
            }

            this.currentClearRequestKey = requestKey;
            this.lastClearedContextKey = requestKey;

            return this.savePoint(null, context).always(function () {
                this.currentClearRequestKey = null;
            }.bind(this));
        },

        handlePointSelection: function (point, context) {
            var payload = this.enrichPoint(context, point);

            this.selectedPoint(payload);
            this.updateInpostinternationalInputField(payload, context);
            this.savePoint(payload, context);
        },

        syncCurrentMethodSelection: function () {
            var method = quote.shippingMethod();
            var config = window.checkoutConfig.inpostGeowidget || {};
            var geowidgetMethods = this.parseConfigList(config.geowidgetShippingMethods);
            var context;
            var selectedPoint;

            if (!method || geowidgetMethods.indexOf(method.carrier_code) === -1) {
                return;
            }

            context = this.buildContext(method);

            if (!context) {
                return;
            }

            coordinator.syncContext(this.providerId, context);
            selectedPoint = this.getStoredPoint(context);

            if (selectedPoint) {
                this.selectedPoint(selectedPoint);
                this.updateInpostinternationalInputField(selectedPoint, context);
                this.bumpPointsVersion();

                if (!window.checkoutConfig.inpostGeowidget['savedPoint_' + context.carrierCode]) {
                    this.savePoint(selectedPoint, context);
                }
            } else {
                this.selectedPoint(null);
                this.clearPoint(context);
            }
        },

        showWidget: function (method) {
            var context = this.buildContext(method || quote.shippingMethod());

            if (!context) {
                return;
            }

            this.selectShippingMethod(context);
            coordinator.syncContext(this.providerId, context);
            coordinator.open(this.providerId, context);
        }
    });
});
