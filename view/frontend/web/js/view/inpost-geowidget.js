define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote'
], function (Component, ko, $, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Smartcore_InPostInternational/inpost-geowidget',
            selectedPoint: ko.observable(null),
            isVisible: ko.observable(false)
        },

        initialize: function () {
            this._super();
            this.bindEscapeKey();
            this.loadSavedPoint();
            this.loadGeowidgetResources();
            this.createModalContainer();
            return this;
        },

        createModalContainer: function() {
            if (!$('#inpost-modal-wrapper').length) {
                $('body').append(
                    '<div id="inpost-modal-wrapper" data-bind="visible: isVisible" style="display: none;">' +
                    '<div class="modal-content">' +
                    '<a href="#" class="action-close" data-bind="click: closeWidget"></a>' +
                    '<div id="inpost-map-container"></div>' +
                    '</div>' +
                    '</div>'
                );
                ko.applyBindings(this, document.getElementById('inpost-modal-wrapper'));
            }
        },

        showWidget: function () {
            $('body').addClass('_has-inpostinternational-modal');
            this.isVisible(true);
            setTimeout(() => this.initGeowidget(), 200);
        },

        closeWidget: function () {
            $('body').removeClass('_has-inpostinternational-modal');
            this.isVisible(false);
            $('#inpost-map-container').empty();
        },

        initGeowidget: function () {
            const container = document.getElementById('inpost-map-container');
            if (!container) return;

            container.innerHTML = '';
            const config = window.checkoutConfig.inpostGeowidget;
            const widget = document.createElement('inpost-geowidget');

            widget.setAttribute('token', config.token);
            widget.setAttribute('config', 'parcelCollect');
            widget.setAttribute('onpoint', 'onInternationalPointSelect');

            const shippingAddress = quote.shippingAddress();
            const countryId = shippingAddress.countryId;
            const countries = config.geowidgetCountries.split(',');
            if (countries.includes(countryId)) {
                countries.splice(countries.indexOf(countryId), 1);
                countries.unshift(countryId);
            }
            widget.setAttribute('country', countries.join(','));

            if (config.isSandbox) {
                widget.setAttribute('sandbox', 'true');
            }

            widget.addEventListener('onInternationalPointSelect', (event) => {
                this.handlePointSelection(event.detail);
            });

            container.appendChild(widget);
        },

        handlePointSelection: function(point) {
            this.selectedPoint(point);
            this.updateInpostInternationalInputField(point.name);

            const inpostMethod = $('input[type="radio"]').filter(function() {
                const methodCode = $(this).val();
                const carrierCode = methodCode.split('_')[0];
                return window.checkoutConfig.inpostGeowidget.shippingMethods.includes(carrierCode);
            }).first();

            if (inpostMethod.length) {
                inpostMethod.prop('checked', true).trigger('click');
            }

            this.savePoint(point);
            this.closeWidget();
        },

        loadGeowidgetResources: function() {
            const config = window.checkoutConfig.inpostGeowidget;
            const baseUrl = config.isSandbox
                ? 'https://sandbox-global-geowidget-sdk.easypack24.net'
                : 'https://geowidget.inpost-group.com';

            this.loadResource('link', {
                rel: 'stylesheet',
                href: `${baseUrl}/inpost-geowidget.css`
            });

            this.loadResource('script', {
                src: `${baseUrl}/inpost-geowidget.js`,
                defer: true
            });
        },

        loadResource: function(type, attributes) {
            const selector = type === 'link'
                ? `link[href="${attributes.href}"]`
                : `script[src="${attributes.src}"]`;

            if (!document.querySelector(selector)) {
                const element = document.createElement(type);
                Object.keys(attributes).forEach(key => element[key] = attributes[key]);
                document.head.appendChild(element);
            }
        },

        bindEscapeKey: function() {
            $(document).on('keyup', (e) => {
                if (e.key === 'Escape' && this.isVisible()) {
                    this.closeWidget();
                }
            });
        },

        loadSavedPoint: function() {
            const method = quote.shippingMethod();
            const carrierCode = method ? method.carrier_code : null;

            let serverSavedPoint = null;
            let specificServerSavedPoint = null;

            try {
                if (carrierCode && window.checkoutConfig?.inpostGeowidget?.['savedPoint_' + carrierCode]) {
                    specificServerSavedPoint = JSON.parse(window.checkoutConfig.inpostGeowidget['savedPoint_' + carrierCode]);
                }
            } catch (e) {
                console.warn('Failed to load InPost International point from configuration:', e);
            }

            let localStoragePoint = null;
            let specificLocalStoragePoint = null;

            try {
                if (carrierCode) {
                    const storedSpecific = localStorage.getItem('inpostinternational_locker_id_' + carrierCode);
                    if (storedSpecific) {
                        specificLocalStoragePoint = JSON.parse(storedSpecific);
                    }
                }
            } catch (e) {
                console.warn('Failed to load InPost International point from localStorage:', e);
            }

            const pointToUse = specificServerSavedPoint || specificLocalStoragePoint || serverSavedPoint || localStoragePoint;

            if (pointToUse) {
                this.selectedPoint(pointToUse);
                this.updateInpostInternationalInputField(pointToUse);

                if ((specificLocalStoragePoint || localStoragePoint) && !specificServerSavedPoint && !serverSavedPoint) {
                    this.savePoint(pointToUse);
                }
            }
        },

        updateInpostInternationalInputField: function(pointToUse) {
            const observer = new MutationObserver((mutations) => {
                const field = $('[name="inpostinternational_locker_id"]');
                if (field.length) {
                    field.val(pointToUse.name);
                    observer.disconnect();
                }
            });

            observer.observe(document.body, { childList: true, subtree: true });
        },

        savePoint: function(point) {
            let pointData = JSON.stringify(point);
            const method = quote.shippingMethod();
            const carrierCode = method ? method.carrier_code : null;

            // Always save to the general storage for backward compatibility
            localStorage.setItem('inpostinternational_locker_id', pointData);

            if (carrierCode) {
                localStorage.setItem('inpostinternational_locker_id_' + carrierCode, pointData);
            }

            return $.ajax({
                url: window.checkoutConfig.inpostGeowidget.savePointUrl,
                type: 'POST',
                data: {
                    point_data: pointData,
                    point_id: point.name,
                    carrier_code: carrierCode
                },
                dataType: 'json'
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Failed to sync point with server:', textStatus, errorThrown);
            });
        }
    });
});
