define([
    'uiComponent',
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/step-navigator',
    'mage/url',
    'underscore',
    'ko',
    'mage/validation',
], function (Component, $, customerData, customer, stepNavigator, urlBuilder, _, ko) {
    'use strict';

    /**
     * @typedef {string} WidgetBasketEventHandler
     * @enum {WidgetBasketEventHandler}
     */
    var WidgetBasketEventTypes = {
        BASKET_DELETED: 'basketDeleted',
        BASKET_PRODUCT_CHANGED: 'basketProductChanged',
        ORDER_CREATED: 'orderCreated'
    }

    var CHECKOUT_BINDING_PLACE = 'CHECKOUT_PAGE'

    return Component.extend({
        /**
         * Initialize widget
         *
         * @param {{
         *  merchantClientId: string,
         *  language: string,
         *  scriptUrl: string,
         *  bindingPlace: string,
         *  basketBindingApiKey: string|undefined
         * }} config
         */
        initialize: function (config) {
            this._super();
            var self = this;
            this.isVisible = ko.observable(false);
            this.checkoutConfiguration = window.checkoutConfig ? window.checkoutConfig.inPostConfig : null;
            this.configuration = config.bindingPlace && config.bindingPlace !== CHECKOUT_BINDING_PLACE
                ? config
                : (window.checkoutConfig ? window.checkoutConfig.inPostConfig : {});

            if (this.configuration) {
                stepNavigator.steps.subscribe(function (steps) {
                    var shippingStep = steps.find(function(step) { return step.code === 'shipping'});
                    var shippingStepVisibility = shippingStep ? shippingStep.isVisible() : window.location.hash.includes('shipping');
                    self.isVisible(!customer.isLoggedIn() && self.configuration.enabledOnCheckoutPage && shippingStepVisibility);
                })
            }

            if (!config || (config && !config.merchantClientId)) return;

            if (this.configuration.scriptUrl && this.configuration.bindingPlace !== CHECKOUT_BINDING_PLACE) {
                this.loadScript(this.configuration.scriptUrl, function() {
                    this.initializeWidget(this.configuration);
                }.bind(this));
            }
        },

        loadScript: function(url, callback, id = 'inpost-api') {
            if (document.getElementById(id)) {
                return;
            }

            var script = document.createElement( "script" )
            script.type = "text/javascript";
            script.src = url;
            script.id = 'inpost-api';
            script.onload = function() {
                callback();
            };

            document.getElementsByTagName( "head" )[0].appendChild( script );
        },

        initializeWidget: function(config) {
            /**
             * @type {object}
             * @property {string} merchantId
             * @property {retrieveApiKey} basketBindingApiKey
             * @property {string} language
             * @property {unboundWidgetClicked} unboundWidgetClicked
             * @property {handleBasketEvent} handleBasketEvent
             * @property {string} apiBaseUrl
             * @property {boolean} webView
             */
            var widgetOptions = $.extend({
                merchantClientId: config.merchantClientId,
                basketBindingApiKey: this.retrieveBasketBindingApiKey(config.basketBindingApiKey),
                unboundWidgetClicked: this.unboundWidgetClicked,
                handleBasketEvent: this.handleBasketEvent,
            }, {
                language: config.language ? config.language : undefined,
                apiBaseUrl: config.apiBaseUrl ? config.apiBaseUrl : undefined,
                webView: config.webView ? config.webView : undefined,
            });

            var widget = InPostPayWidget.init(widgetOptions);
        },

        getConfiguration: function() {
            return this.checkoutConfiguration;
        },

        initAfterRender: function() {
            var config = window.checkoutConfig ? window.checkoutConfig.inPostConfig : {}

            if (config.hasOwnProperty('enabledOnCheckoutPage')
                && !config.enabledOnCheckoutPage) {
                $('#inpost-izi-button-wrapper').remove()
                return;
            }

            if (config.scriptUrl) {
                this.loadScript(config.scriptUrl, function() {
                    this.initializeWidget(config);
                }.bind(this));
            }
        },

        /**
         * Handle user clicks on the unbound basket
         *
         * @callback unboundWidgetClicked
         * @param {string} productId
         * @return {promise<string>}
         */
        unboundWidgetClicked: function (productId) {
            if (!productId) {
                return Promise.reject('Product id not found');
            }

            var $productInput = $('[name="product"][value="' + productId + '"]');
            var $productForm = $productInput.parent('#product_addtocart_form');

            if (!$productForm.length) {
                return Promise.reject('Problem with product configuration');
            }

            if (!$productForm.validation('isValid')) {
                return Promise.reject('Product is invalid');
            }

            return ajaxSubmit($productForm).then().catch(function (err) {
                console.error(err);
            })

            function ajaxSubmit($form) {
                return new Promise(function (resolve, reject) {
                    $.ajax({
                        url: $form.attr('action'),
                        data: new FormData($form[0]),
                        type: 'post',
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function () {
                            $.ajax({
                                url: urlBuilder.build('inpostizi/BasketBindingApiKey/Get' + '/form_key/' + $.mage.cookies.get('form_key')),
                                method: 'GET',
                            })
                                .done(function (data) {
                                    resolve(data.basket_binding_api_key)
                                })
                                .fail(function () {
                                    reject();
                                });
                        },
                        error: function () {
                            reject()
                        },
                    });
                });
            }

        },

        /**
         * Handle retrieving basketBindingApiKey
         * Return true if widget should not refresh the page
         *
         * @callback retrieveApiKey
         * @param {undefined|string} apiKey
         * @return {undefined|string|promise<string>}
         */
        retrieveBasketBindingApiKey: function (apiKey = undefined) {
            var self = this;
            if (apiKey || apiKey === undefined) return apiKey;

            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: urlBuilder.build('inpostizi/BasketBindingApiKey/Get' + '/form_key/' + $.mage.cookies.get('form_key')),
                    method: 'GET',
                })
                    .done(function (data) {
                        self.basketBindingApiKey = data;
                        resolve(data)
                    })
                    .fail(function () {
                        reject(new Error($.mage.__('Network problem')));
                    });
            });
        },

        /**
         * Callback function to reflect basket updates.
         * If not provided or returning false - widget will refresh the entire page.
         * @return 'true' if widget should not refresh the page
         *
         * @callback retrieveApiKey
         * @param {WidgetBasketEventHandler} widgetBasketEvent
         * @return {boolean}
         */
        handleBasketEvent: function (widgetBasketEvent) {
            if (widgetBasketEvent !== WidgetBasketEventTypes.ORDER_CREATED) return false;

            var self = this;

            //TODO dodany basketBindingApiKey - na ten moment nie wiadomo czy jest dostępny i jak zadziała - do przetestowania
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: urlBuilder.build('inpostizi/OrderComplete/Get'
                            + '/form_key/'
                            + $.mage.cookies.get('form_key'))
                            + '/?basketBindingApiKey='
                            + self.basketBindingApiKey,
                    method: 'GET',
                })
                    .done(function (data) {
                        if (data.redirect) {
                            resolve(true);
                            window.location.replace(data.redirect);
                        } else {
                            reject();
                        }
                    })
                    .fail(function () {
                        reject(new Error($.mage.__('Network problem')));
                    });
            });
        }
    });
});
