define([
    'uiComponent',
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'underscore',
    'mage/validation'
], function (Component, $, customerData, urlBuilder, _) {
    'use strict';

    var LONG_POLLING_TIME = 10000;
    var timeoutId, xhrForBasketConfirmation, xhrForOrderConfirmation, globalOrderResetFlag = false;
    var PRODUCT_TYPES = {
        CONFIGURABLE: 'configurable',
        SIMPLE: 'simple',
        GROUPED: 'grouped',
        VIRTUAL: 'virtual',
        DOWNLOADABLE: 'downloadable',
        BUNDLE: 'bundle'
    };

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
            window.checkIsBinding = this.checkIsBinding;

            window.getConfig = function (defaultConfig = config) {
                return defaultConfig;
            }

            this.bindEvents();
            this.checkIsBinding();
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

            return $productForm.validation('isValid');
        },

        checkIsBinding: function() {
            $.ajax({
                url: urlBuilder.build('inpostizi/BasketConfirmation/Get'
                    + '/form_key/'
                    + $.mage.cookies.get('form_key')
                ),
                method: 'GET',
            })
                .done(function (data) {
                    if (data.status && data.status === 'SUCCESS') {
                        if (data.basket_id) {
                            localStorage.setItem('basketId', data.basket_id);
                        }

                        var $iziButtons = $("inpost-izi-button");
                        if (!$iziButtons.length) return;

                        var event = new CustomEvent("izi-binding-complete", {detail: data});

                        $iziButtons.each(function () {
                            this.dispatchEvent(event)
                        });
                    }
                });
        },

        iziGetPayData: function (prefix, phoneNumber, bindingPlace) {
            var url = urlBuilder.build('inpostizi/PayData/Get' + '/form_key/' + $.mage.cookies.get('form_key'));
            var browserData = window.iziGetBrowserData({base64: true});
            var browserId = getCookie('BrowserId');
            var prefixValue = !browserId && prefix ? "" : prefix ? "+" + prefix : "";
            var phoneNumberValue = !browserId && phoneNumber ? "" : phoneNumber ? phoneNumber : "";
            var data = {
                prefix: prefixValue,
                number: phoneNumberValue,
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
                        if (!Object.keys(data).length) {
                            reject({ message: new Error($.mage.__('Something went wrong, refresh the page and try again')) });
                        } else if (Object.keys(data).length === 1 && data.basket_id) {
                            window.checkIsBinding();
                            localStorage.setItem('basketId', data.basket_id);
                            resolve([]);
                        } else if (data.action){
                            reject({ message: data.errorMessage });
                        } else {
                            localStorage.setItem('basketId', data.basket_id);
                            globalOrderResetFlag = false;
                            resolve({
                                qr_code: data.qr_code,
                                deep_link: data.deep_link,
                                deep_link_hms: data.deep_link_hms,
                            })
                        }
                    })
                    .fail(function () {
                        reject(new Error($.mage.__('Network problem')));
                    });
            });

            function getCookie(name) {
                var cookieArr = document.cookie.split(";");

                for (let i = 0; i < cookieArr.length; i++) {
                    let cookiePair = cookieArr[i].split("=");

                    if (name === cookiePair[0].trim()) {
                        return decodeURIComponent(cookiePair[1]);
                    }
                }

                return null;
            }
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
                    .fail(function () {
                        reject(new Error($.mage.__('Network problem')));
                    });
            });
        },

        iziGetIsBound: function () {
            return new Promise((resolve, reject) => {
                checkIsBound()
                    .then((data) => {
                        resolve(data)
                    })
                    .catch(function(error) {
                        reject(error)
                    });
            });

            function checkIsBound() {
                abortRequest(xhrForBasketConfirmation)

                return new Promise((resolve, reject) => {
                    xhrForBasketConfirmation = $.ajax({
                        url: urlBuilder.build('inpostizi/BasketConfirmation/Get'
                            + '/form_key/'
                            + $.mage.cookies.get('form_key')
                        ),
                        method: 'GET',
                    })
                        .done(function (data) {
                            if (data.status) {
                                if (timeoutId) {
                                    clearTimeout(timeoutId);
                                    abortRequest(xhrForBasketConfirmation)
                                }

                                switch (data.status) {
                                    case 'REJECT':
                                        reject({ message: new Error($.mage.__('Connection has been interrupted, please try again.')) });
                                        break;
                                    case 'PENDING':
                                        setTimerAndRunCallback(checkIsBound, resolve, reject);
                                        break;
                                    case 'SUCCESS':
                                        localStorage.setItem('browser_id', data.browser.browser_id);
                                        delete data.basket_id;
                                        resolve(data)
                                        break;
                                    default:
                                        break;
                                }
                            } else if (data.errorMessage && !data.action){
                                reject({ message: data.errorMessage });
                            } else {
                                if (data.action && data.action === 'reject') {
                                    reject({ message: data.errorMessage });
                                } else {
                                    setTimerAndRunCallback(checkIsBound, resolve, reject);
                                }
                            }
                        })
                        .fail(function () {
                            reject(new Error($.mage.__('Network problem')));
                        });
                });
            }
        },

        iziGetOrderComplete: function () {
            var basketId = localStorage.getItem('basketId');

            if (!basketId) {
                return Promise.resolve(new Error($.mage.__('Problem loading the cart, please refresh the page and try again')))
            }

            return new Promise((resolve, reject) => {
                checkOrderStatus()
                    .then((data) => {
                        if (data) {
                            resolve(data)
                        }
                    })
                    .catch(function(error) {
                        reject(error)
                    });
            });
            function checkOrderStatus() {
                abortRequest(xhrForOrderConfirmation)

                return new Promise((resolve, reject) => {
                    if (globalOrderResetFlag) resolve();

                    xhrForOrderConfirmation = $.ajax({
                        url: urlBuilder.build('inpostizi/OrderComplete/Get'
                            + '/?basketId='
                            + basketId
                        ),
                        method: 'GET',
                    })
                        .done(function (data) {
                            if (timeoutId) {
                                clearTimeout(timeoutId);
                                abortRequest(xhrForBasketConfirmation)
                            }

                            if (!data.action) {
                                setTimerAndRunCallback(checkOrderStatus, resolve, reject);
                            } else {
                                resolve(data);
                            }
                        })
                        .fail(function (error) {
                            reject(new Error($.mage.__('Network problem')));
                        });
                });
            }
        },

        iziBindingDelete: function () {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: urlBuilder.build('inpostizi/BasketBinding/Delete' + '/form_key/' + $.mage.cookies.get('form_key')),
                    method: 'GET',
                })
                    .done(function () {
                        globalOrderResetFlag = true;
                        resolve()
                    })
                    .fail(function () {
                        globalOrderResetFlag = true;
                        reject(new Error($.mage.__('Network problem')));
                    })
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
            customerData.invalidate(['cart']);
            customerData.reload(['cart'], true);

            customerData.get('cart').subscribe(function (cartData) {
                checkCartWidget(cartData);
                updateCounter(cartData.summary_count);
            });

            document.addEventListener('iziModalEventOpen', function () {
                $('.block-minicart').dropdownDialog('close');
            })

            window.addEventListener("inpost-update-count", function (e){
                updateCounter(e.detail);
            });

            function checkCartWidget(cartData = "") {
                var wrapperClass = getConfig().wrapperClass || "inpay-widget-wrapper";
                var popupBindingPlace = getConfig().popupBindingPlace || "BASKET_POPUP";
                var $inpayWrapperOnBasket = $("." + wrapperClass + "." + popupBindingPlace);
                var counter = cartData ? cartData.summary_count : getConfig().count || 0;

                if ($inpayWrapperOnBasket.length) {
                    if (counter === 0) {
                        $inpayWrapperOnBasket.hide()
                    } else {
                        $inpayWrapperOnBasket.show()
                    }
                }
            }

            function updateCounter(count) {
                var $iziButtons = $("inpost-izi-button");
                if (!$iziButtons.length) return;

                var event = new CustomEvent("inpost-update-count", {detail: count});

                $iziButtons.each(function () {
                    this.dispatchEvent(event)
                });
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

        setTimerAndRunCallback: function (callback, resolve, reject) {
            timeoutId = setTimeout(function () {
                callback()
                    .then((data) => {
                        resolve(data)
                    })
                    .catch(function(error) {
                        reject(error);
                    });
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
