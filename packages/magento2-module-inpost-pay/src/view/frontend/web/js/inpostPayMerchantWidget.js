define([
    'uiComponent',
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'underscore'
], function (Component, $, customerData, urlBuilder, _) {
    'use strict';

    var LONG_POLLING_TIME = 10000;
    var timeoutId, xhrForBasketConfirmation, xhrForOrderConfirmation;
    var PRODUCT_TYPES = {
        CONFIGURABLE: 'configurable',
        SIMPLE: 'simple',
        GROUPED: 'grouped',
        VIRTUAL: 'virtual',
        DOWNLOADABLE: 'downloadable',
        BUNDLE: 'bundle'
    }

    return Component.extend({
        initialize: function (config) {
            this._super();

            if (this.isWidgetInitialized()) return;

            window.iziCanBeBound = this.iziCanBeBound;
            window.iziGetPayData = this.iziGetPayData;
            window.iziGetBrowserData = this.iziGetBrowserData;
            window.iziMobileLink = this.iziMobileLink;
            window.iziGetIsBound = this.iziGetIsBound;
            window.iziGetOrderComplete = this.iziGetOrderComplete;
            window.iziBindingDelete = this.iziBindingDelete;
            window.iziAddToCart = this.iziAddToCart;
            window.getBrowserDescription = this.getBrowserDescription;
            window.abortRequest = this.abortRequest;
            window.checkIfProductIsAdded = this.checkIfProductIsAdded;
            window.setTimerAndRunCallback = this.setTimerAndRunCallback;

            window.getConfig = function (defaultConfig = config) {
                return defaultConfig;
            }

            this.bindEvents();
        },

        isWidgetInitialized: function () {
            return window.iziGetPayData && window.iziGetPayData && window.iziGetBrowserData && window.iziMobileLink;
        },

        iziCanBeBound: function (productId) {
            if (!productId) {
                return true;
            }

            var $productInput = $('[name="product"][value="' + productId + '"]');
            var $productForm = $productInput.parent('#product_addtocart_form');

            if (!$productForm.length) {
                return true;
            }

            var $groupedProductElements = $productForm.find('[name*="super_group"]')
            if ($groupedProductElements.length) {
                return !!$groupedProductElements.filter(function () {
                    return this.value > 0;
                }).length
            }

            var $configurableProductOptions = $productForm.find('[name*="super_attribute"]')
            if ($configurableProductOptions.length) {
                return !$configurableProductOptions.filter(function () {
                    return this.value === "";
                }).length
            }

            var $bundleProducts = $productForm.find('[name*="bundle_option"]');
            return !$bundleProducts.length;
        },

        iziGetPayData: function (prefix, phoneNumber, bindingPlace) {
            var url = urlBuilder.build('inpostizi/PayData/Get' + '/form_key/' + $.mage.cookies.get('form_key'));
            var browserData = window.iziGetBrowserData({base64: true});
            var data = {
                prefix: prefix && prefix.toString() || "",
                number: phoneNumber || "",
                browser: browserData,
                binding_place: bindingPlace
            };

            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    data: JSON.stringify(data)
                })
                    .done(function (data) {
                        resolve(Object.keys(data).length === 1 && data.basketId ? [] : data)
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error($.mage.__('Network problem: ') + textStatus));
                    });
            });
        },

        iziGetBrowserData: function (params) {
            var browserData = {
                user_agent: window.navigator.userAgent,
                description: getBrowserDescription(),
                platform: navigator.userAgentData?.platform || navigator.platform,
                architecture: window.navigator.appVersion
            };

            return params && params.base64 ? btoa(JSON.stringify(browserData)) : browserData;
        },

        iziMobileLink: function () {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: urlBuilder.build('inpostizi/MobileLink/Get' + '/form_key/' + $.mage.cookies.get('form_key')),
                    method: 'GET',
                })
                    .done(function (data) {
                        resolve(data)
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error($.mage.__('Network problem: ') + textStatus));
                    });
            });
        },

        iziGetIsBound: function () {
            return new Promise((resolve, reject) => {
                checkIsBound(resolve, reject);
            });

            function checkIsBound(resolve, reject) {
                abortRequest(xhrForBasketConfirmation)
                xhrForBasketConfirmation = $.ajax({
                    url: urlBuilder.build('inpostizi/BasketConfirmation/Get'
                        + '/form_key/'
                        + $.mage.cookies.get('form_key')
                    ),
                    method: 'GET',
                })
                    .done(function (data) {
                        if (data.phone_number) {
                            resolve(data);
                        } else if (data.status) {
                            if (timeoutId) {
                                clearTimeout(timeoutId);
                                abortRequest(xhrForBasketConfirmation)
                            }

                            switch (data.status) {
                                case 'REJECT':
                                    reject(new Error($.mage.__('Connection has been interrupted, please try again.')));
                                    break;
                                case 'PENDING':
                                    setTimerAndRunCallback(checkIsBound);
                                    break;
                                default:
                                    break;
                            }
                        } else if (data.error_code) {
                            reject(new Error(data.error_code));
                        } else {
                            setTimerAndRunCallback(checkIsBound)
                        }
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error($.mage.__('Network problem: ') + textStatus));
                    });
            }
        },

        iziGetOrderComplete: function () {
            //TODO check statuses from BE, change url when endpoint will be changed to controller
            return new Promise((resolve, reject) => {
                checkOrderStatus(resolve, reject);
            });

            function checkOrderStatus(resolve, reject) {
                abortRequest(xhrForOrderConfirmation)
                xhrForOrderConfirmation = $.ajax({
                    url: urlBuilder.build('rest/V1/izi/checkOrderStatus/'),
                    method: 'GET',
                })
                    .done(function (data) {
                        if (data.action && data.action === 'refresh') {
                            setTimerAndRunCallback(checkOrderStatus);
                        } else if (data.action && data.action === 'redirect') {
                            resolve(data)
                        }

                        reject(new Error($.mage.__('Unhandled status')));
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error($.mage.__('Network problem: ') + textStatus));
                    });
            }
        },

        iziBindingDelete: function () {
            //TODO change url when endpoint will be changed to controller
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: urlBuilder.build('rest/V1/izi/basket/binding'),
                    method: 'GET',
                })
                    .done(function () {
                        resolve()
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error($.mage.__('Network problem: ') + textStatus));
                    });
            });
        },

        iziAddToCart: function (id) {
            if (!id || !window.iziCanBeBound(id)) return;

            var $productInput = $('[name="product"][value="' + id + '"]');
            var $productForm = $productInput.parent('#product_addtocart_form');
            var isProductAdded = checkIfProductIsAdded(id, customerData.get("cart")(), $productForm)

            if (isProductAdded) return;

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
                            resolve()
                        },
                        error: function () {
                            reject()
                        }
                    });
                });
            }
        },

        bindEvents: function () {
            checkCartWidget();
            customerData.get('cart').subscribe(function (cartData) {
                checkCartWidget(cartData);

                var $iziButtons = $("inpost-izi-button");
                if (!$iziButtons.length) return;

                var event = new CustomEvent("inpost-update-count", {detail: cartData.summary_count});

                $iziButtons.each(function () {
                    this.dispatchEvent(event)
                });
            });

            document.addEventListener('iziModalEventClose', function () {
                abortRequest(xhrForBasketConfirmation)
            })

            function checkCartWidget(cartData = "") {
                var wrapperClass = getConfig().wrapperClass || "inpost-widget-wrapper";
                var popupBindingPlace = getConfig().popupBindingPlace || "BASKET_POPUP";
                var $inpayWrapperOnBasket = $("." + wrapperClass + "." + popupBindingPlace);
                var counter = cartData ? cartData.summary_count : getConfig().count;

                if ($inpayWrapperOnBasket.length) {
                    if (counter === 0) {
                        $inpayWrapperOnBasket.hide()
                    } else {
                        $inpayWrapperOnBasket.show()
                    }
                }
            }
        },

        getBrowserDescription: function () {
            const test = regexp => {
                return regexp.test(window.navigator.userAgent);
            };

            if (test(/opr\//i) || !!window.opr) {
                return 'Opera';
            } else if (test(/edg/i)) {
                return 'Edge';
            } else if (test(/chrome|chromium|crios/i)) {
                return 'Chrome';
            } else if (test(/firefox|fxios/i)) {
                return 'Firefox';
            } else if (test(/safari/i)) {
                return 'Safari';
            } else if (test(/trident/i)) {
                return 'IE';
            } else if (test(/ucbrowser/i)) {
                return 'UC Browser';
            } else if (test(/samsungbrowser/i)) {
                return 'Samsung Browser';
            } else {
                return 'Unknown browser';
            }
        },

        abortRequest: function (request) {
            if (request && request.readyState !== 1) {
                clearTimeout(timeoutId);
                request.abort();
                request = null;
            }
        },

        setTimerAndRunCallback: function (callback) {
            timeoutId = setTimeout(function () {
                callback();
            }, LONG_POLLING_TIME);
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

            //TODO add more specific validation of grouped product
            if (simpleProductsInGrouped.length) {
                var addedSimpleProducts = 0;
                _.each(simpleProductsInGrouped, function (item) {
                    if (cartData.items.some(function (cartItem) {
                        return cartItem.product_id === $(item).attr('name').match(/\[(.*?)\]/)[1];
                    })) addedSimpleProducts++
                })
                return addedSimpleProducts === simpleProductsInGrouped.length;
            }
        }
    });
});
