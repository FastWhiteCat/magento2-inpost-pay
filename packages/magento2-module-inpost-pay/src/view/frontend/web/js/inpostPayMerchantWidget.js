define([
    'uiComponent',
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'underscore'
], function (Component, $, customerData, urlBuilder, _) {
    'use strict';

    return Component.extend({
        initialize: function (config) {
            this._super();
            var isWidgetInitialized = window.iziGetPayData && window.iziGetPayData && window.iziGetBrowserData && window.iziMobileLink;

            if (isWidgetInitialized) return;

            window.iziCanBeBound = this.iziCanBeBound;
            window.iziGetPayData = this.iziGetPayData;
            window.iziGetBrowserData = this.iziGetBrowserData;
            window.iziMobileLink = this.iziMobileLink;
            window.iziGetIsBound = this.iziGetIsBound;
            window.iziGetOrderComplete = this.iziGetOrderComplete;
            window.iziBindingDelete = this.iziBindingDelete;
            window.iziAddToCart = this.iziAddToCart;
            window.getBrowserDescription = this.getBrowserDescription;
            window.getConfig = function (defaultConfig = config) {
                return defaultConfig;
            }

            this.bindEvents();
        },

        iziCanBeBound: function (productId) {
            if (!productId) {
                return false;
            }

            var $productForm = $('#product_addtocart_form');

            if (!$productForm.length) {
                return false;
            }
            var $groupedProductElements = $productForm.find('[name*="super_group"]')
            if ($groupedProductElements.length) {
                return !!$groupedProductElements.filter(function() {
                    return this.value > 0;
                }).length
            }

            var $configurableProductOptions = $productForm.find('[name*="super_attribute"]')
            if ($configurableProductOptions.length) {
                return !$configurableProductOptions.filter(function() {
                    return this.value === "";
                }).length
            }

            var $bundleProducts = $productForm.find('[name*="bundle_option"]');
            return !$bundleProducts.length;
        },

        iziGetPayData: function (prefix, phoneNumber, bindingPlace) {
            var isLoggedIn = _.has(customerData.get('customer')(), 'fullname');
            var cartId = customerData.get('cart')().cartId || getConfig().cartId || "";
            var url = !isLoggedIn
                ? urlBuilder.build('rest/V1/inpost-basket/' +
                    (getConfig().cartId || "")) +
                    '/get-pay-data'
                : urlBuilder.build('rest/V1/inpost-basket/mine/get-pay-data');

            var browserData = window.iziGetBrowserData({base64: true});
            var data = {
                prefix: prefix || "",
                number: phoneNumber || "",
                browser: browserData,
                binding_place: bindingPlace,
                cartId: cartId
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
                        // nie testowane - brak poprawnej zwrotki z BE
                        if (Object.keys(data).length === 1 && data.basketId) {
                            resolve([]);
                        }

                        resolve(data);
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error('Network problem: ' + textStatus));
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
                    url: urlBuilder.build('rest/V1/izi/basket/binding'),
                    method: 'GET',
                })
                    .done(function (data) {
                        resolve(data)
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error('Network problem: ' + textStatus));
                    });
            });
        },

        iziGetIsBound: function () {
            let timeoutId;

            return new Promise((resolve, reject) => {
                checkIsBound(resolve, reject);
            });

            function checkIsBound(resolve, reject) {
                $.ajax({
                    url: urlBuilder.build('rest/V1/izi/getIsBound/'),
                    method: 'GET',
                })
                    .done(function (data) {
                        if (data.phone_number) {
                            resolve(data);
                        } else if (data.action) {
                            if (timeoutId) {
                                clearTimeout(timeoutId);
                            }

                            switch (data.action) {
                                case 'close':
                                    reject(new Error("Połączenie zostało przerwane, spróbuj ponownie."));
                                    break;
                                case 'retry':
                                    timeoutId = setTimeout(function()  {
                                        checkIsBound(resolve, reject)
                                    },10000);
                                    break;
                                default:
                                    break;
                            }
                        } else if (data.error_code) {
                            reject(new Error(data.error_code));
                        } else {
                            timeoutId = setTimeout(function()  {
                                checkIsBound(resolve, reject)
                            },10000);
                        }
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error('Network problem: ' + textStatus));
                    });
            }
        },

        iziGetOrderComplete: function () {
            let timeoutId;

            return new Promise((resolve, reject) => {
                checkOrderStatus(resolve, reject);
            });

            function checkOrderStatus(resolve, reject) {
                $.ajax({
                    url: urlBuilder.build('rest/V1/izi/checkOrderStatus/'),
                    method: 'GET',
                })
                    .done(function (data) {
                        if (data.action && data.action === 'refresh') {
                            timeoutId = setTimeout(function()  {
                                checkOrderStatus(resolve, reject)
                            },10000);
                        } else if (data.action && data.action === 'redirect') {
                            resolve(data)
                        }

                        reject(new Error('Nieoczekiwany status'));

                        if (data.action) {
                            resolve(data)
                        } else {
                            timeoutId = setTimeout(function()  {
                                checkOrderStatus(resolve, reject)
                            },10000);
                        }
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error('Network problem: ' + textStatus));
                    });
            }
        },

        iziBindingDelete: function () {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: urlBuilder.build('rest/V1/izi/basket/binding'),
                    method: 'GET',
                })
                    .done(function (data) {
                        resolve()
                    })
                    .fail(function (xhr, textStatus) {
                        reject(new Error('Network problem: ' + textStatus));
                    });
            });
        },

        iziAddToCart: function (id) {
            if (!id || !window.iziCanBeBound(id)) {
                return new Error('Nie podano id produktu lub nie mozna dodac produktu do koszyka');
            }

            $("#product_addtocart_form").submit();

            return new Promise(function (resolve, reject) {
                var cartSubscriber = customerData.get('cart').subscribe(function (cartData) {
                    cartSubscriber.dispose();
                    resolve();
                });
            }).then().catch(function(error) {
                return new Error(error)
            });
        },

        bindEvents: function () {
            customerData.get('cart').subscribe(function (cartData) {
                var $iziButtons = $("inpost-izi-button");
                if (!$iziButtons.length) return;

                var event = new CustomEvent("inpost-update-count", { detail: cartData.summary_count });

                $iziButtons.each(function() {
                    this.dispatchEvent(event)
                });
            });
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
        }
    });
});
