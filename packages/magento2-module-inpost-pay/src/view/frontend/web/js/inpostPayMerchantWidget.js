define([
    'uiComponent',
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/step-navigator',
    'mage/url',
    'underscore',
    'ko',
    'mage/validation',
], function (Component, $, customerData, stepNavigator, urlBuilder, _, ko) {
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

    var PRODUCT_TYPES = {
        CONFIGURABLE: 'configurable',
        SIMPLE: 'simple',
        GROUPED: 'grouped',
        VIRTUAL: 'virtual',
        DOWNLOADABLE: 'downloadable',
        BUNDLE: 'bundle'
    };

    var CHECKOUT_BINDING_PLACE = 'CHECKOUT_PAGE'
    var PRODUCT_CARD_BINDING_PLACE = 'PRODUCT_CARD'
    var SUCCESS_PAGE_BINDING_PLACE = 'ORDER_CREATE'

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
            this.checkIfProductIsAdded = this.checkIfProductIsAdded.bind(this);
            this.sectionData = customerData.get("cart")

            if (!this.sectionData().hasOwnProperty('summary_count')) {
                customerData.reload(['cart'])
            }

            if (this.configuration) {
                stepNavigator.steps.subscribe(function (steps) {
                    var shippingStep = steps.find(function(step) { return step.code === 'shipping'});
                    var shippingStepVisibility = shippingStep ? shippingStep.isVisible() : window.location.hash.includes('shipping');
                    self.isVisible(self.configuration.enabledOnCheckoutPage && shippingStepVisibility);
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
             * @property {boolean} webView
             */
            var widgetOptions = $.extend({
                merchantClientId: config.merchantClientId,
                basketBindingApiKey: this.retrieveBasketBindingApiKey(config.basketBindingApiKey),
                unboundWidgetClicked: this.unboundWidgetClicked.bind(this),
                handleBasketEvent: this.handleBasketEvent.bind(this),
            }, {
                language: config.language ? config.language : undefined,
                webView: config.webView ? config.webView : undefined,
            });

            var widget = InPostPayWidget.init(widgetOptions);

            this.bindEvents();
        },

        getConfiguration: function() {
            return this.checkoutConfiguration;
        },

        bindEvents: function() {
            customerData.get('cart').subscribe(function (cartData) {
                checkCartWidget(cartData);
            });

            checkCartWidget(this.sectionData());

            function checkCartWidget(cartData = "") {
                var wrapperClass = "inpay-widget-wrapper";
                var $inpayWrapperOnBasket = $("." + wrapperClass);
                var counter = cartData ? cartData.summary_count : 0;

                if ($inpayWrapperOnBasket.length) {
                    $inpayWrapperOnBasket.each(function() {
                        if ($(this).hasClass(PRODUCT_CARD_BINDING_PLACE)
                            || $(this).hasClass(SUCCESS_PAGE_BINDING_PLACE)) {
                            return;
                        }

                        if (counter === 0) {
                            $(this).hide()
                        } else {
                            $(this).show()
                        }
                    })
                }
            }
        },

        checkIfProductIsAdded: function (id, cartData, $productForm) {
            if (!cartData.items) return false;

            if (cartData.items
                && cartData.items.some((item) => item.product_id === id && item.product_type === PRODUCT_TYPES.SIMPLE))
                return true;

            var $configurableProductOptions = $productForm.find('[data-attribute-code]')

            if ($configurableProductOptions.length) {
                var configurableProducts = cartData.items.filter(function (item) {
                    return item.product_id === id;
                })
                var productOptions = 0;

                return configurableProducts.some(function (item) {
                    _.each(item.options, function (option, index) {
                        if (option.option_id.toString() === $configurableProductOptions[index].dataset.attributeId
                            && option.option_value === $configurableProductOptions[index].dataset.optionSelected)
                            productOptions++;
                    })

                    var isAddedProduct = productOptions === $configurableProductOptions.length;
                    productOptions = 0;

                    return isAddedProduct
                })
            }

            var $groupedProductElements = $productForm.find('[name*="super_group"]')
            var simpleProductsInGrouped = $groupedProductElements.filter(function () {
                return this.value > 0;
            })

            if (simpleProductsInGrouped.length) {
                var addedSimpleProducts = 0;
                _.each(simpleProductsInGrouped, function (item) {
                    if (cartData.items.some(function (cartItem) {
                        return cartItem.product_id === $(item).attr('name').match(/\[(.*?)\]/)[1];
                    })) addedSimpleProducts++
                })
                return addedSimpleProducts === simpleProductsInGrouped.length;
            }
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

        getBasketBindingApiKey: function() {
            var self = this;

            return new Promise((resolve, reject) => {
                var formData = {
                    form_key: $.mage.cookies.get('form_key')
                };

                if (self.configuration.enabledAnalyticsParams) {
                    var gaClientId = window.localStorage.getItem('client_id');
                    var fbclid = window.localStorage.getItem('fbclid');
                    var gclid = window.localStorage.getItem('gclid');

                    if (gaClientId !== null) {
                        formData.ga_client_id = gaClientId;
                    }

                    if (fbclid !== null) {
                        formData.fbclid = fbclid;
                    }

                    if (gclid !== null) {
                        formData.gclid = gclid;
                    }
                }

                var url = urlBuilder.build('inpostizi/BasketBindingApiKey/Get');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                })
                    .done(function (data) {
                        if (data && data.success && data.basket_binding_api_key) {
                            self.basketBindingApiKey = data.basket_binding_api_key;
                            resolve(data.basket_binding_api_key)
                        } else if (data.error) {
                            reject(data.error)
                        } else {
                            reject(new Error('Something went wrong, refresh the page and try again'))
                        }
                    })
                    .fail(function () {
                        reject(new Error('Something went wrong, refresh the page and try again'));
                    });
            })
        },

        /**
         * Handle user clicks on the unbound basket
         *
         * @callback unboundWidgetClicked
         * @param {string} productId
         * @return {promise<string>}
         */
        unboundWidgetClicked: function (productId) {
            var self = this;

            if (this.sectionData().summary_count > 0 && !productId) {
                return new Promise(function (resolve, reject) {
                    self.getBasketBindingApiKey(resolve, reject)
                        .then((data) => {
                            resolve(data)
                        })
                        .catch(function(error) {
                            reject(error)
                        });
                });
            }

            if (!productId) {
                return Promise.reject(new Error('Product id not found'));
            }

            var $productInput = $('[name="product"][value="' + productId + '"]');
            var $productForm = $productInput.parent('#product_addtocart_form');

            if (!$productForm.length) {
                return Promise.reject(new Error('UNDELIVERABLE_PRODUCT'))

            }

            if (!$productForm.validation('isValid')) {
                return Promise.reject(new Error('UNDELIVERABLE_PRODUCT'))
            }

            var isProductAdded = this.checkIfProductIsAdded(productId, customerData.get("cart")(), $productForm)

            if (isProductAdded) {
                return new Promise(function (resolve, reject) {
                    self.getBasketBindingApiKey(resolve, reject)
                        .then((data) => {
                            resolve(data)
                        })
                        .catch(function(error) {
                            reject(error)
                        });
                });
            }

            return ajaxSubmit($productForm).then().catch(function (err) {
                console.error(err);
            })

            function ajaxSubmit($form) {
                return new Promise(function (resolve, reject) {
                    var formData = {
                        form_key: $.mage.cookies.get('form_key')
                    };

                    if (self.configuration.enabledAnalyticsParams) {
                        var gaClientId = window.localStorage.getItem('client_id');
                        var fbclid = window.localStorage.getItem('fbclid');
                        var gclid = window.localStorage.getItem('gclid');

                        if (gaClientId !== null) {
                            formData.ga_client_id = gaClientId;
                        }

                        if (fbclid !== null) {
                            formData.fbclid = fbclid;
                        }

                        if (gclid !== null) {
                            formData.gclid = gclid;
                        }
                    }

                    var url = urlBuilder.build('inpostizi/BasketBindingApiKey/Get');

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
                                url: url,
                                method: 'POST',
                                data: formData
                            })
                                .done(function (data) {
                                    if (data && data.success && data.basket_binding_api_key) {
                                        self.basketBindingApiKey = data.basket_binding_api_key;
                                        resolve(data.basket_binding_api_key)
                                    } else if (data.error) {
                                        reject(data.error)
                                    } else {
                                        reject(new Error('Something went wrong, refresh the page and try again'))
                                    }
                                })
                                .fail(function () {
                                    reject(new Error('Something went wrong, refresh the page and try again'));
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
            if (apiKey) return apiKey;

            var basketBindingApiKeyFromCookies = $.mage.cookies.get('basketBindingApiKey');

            return basketBindingApiKeyFromCookies ? basketBindingApiKeyFromCookies : undefined
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
            if (widgetBasketEvent !== WidgetBasketEventTypes.ORDER_CREATED) {
                customerData.invalidate(['cart', 'messages']);
                return false;
            }

            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: urlBuilder.build('inpostizi/OrderComplete/Get'
                            + '/form_key/'
                            + $.mage.cookies.get('form_key'))
                        + '/?basket_binding_api_key='
                        + $.mage.cookies.get('basketBindingApiKey'),
                    method: 'GET',
                })
                    .done(function (data) {
                        if (data && data.redirect) {
                            customerData.invalidate(['cart', 'messages']);
                            resolve(true);
                            window.location.replace(data.redirect);
                        } else {
                            reject(false);
                        }
                    })
                    .fail(function () {
                        reject(false);
                    });
            });
        }
    });
});
