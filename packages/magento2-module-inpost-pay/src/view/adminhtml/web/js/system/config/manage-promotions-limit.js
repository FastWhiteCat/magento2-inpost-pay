define([
    'jquery',
    'mage/template'
], function ($) {
    'use strict';

    return function (config) {
        const containerId = config.inputId || '';
        const maxRowLimit = config.maxRowLimit || 10;
        const enabledAddButtonText = config.enabledAddButtonText || 'Add';
        const disabledAddButtonText = config.disabledAddButtonText || 'Limit Reached';
        const rowsContainer = $('#' + containerId);
        const addButton = rowsContainer.find('.action-add');

        /**
         * Enable or disable the Add button dynamically
         */
        function checkRowsLimit() {
            const currentRowCount = rowsContainer.find('tbody').children('tr').length;
            if (currentRowCount >= maxRowLimit) {
                addButton.prop('disabled', true).addClass('disabled').text(disabledAddButtonText);
            } else {
                addButton.prop('disabled', false).removeClass('disabled').text(enabledAddButtonText);
            }

            rowsContainer.find('button.action-delete').on('click', function () {
                setTimeout(checkRowsLimit, 200);
            });
        }

        checkRowsLimit();

        rowsContainer.on('click', '.action-add', function () {
            setTimeout(checkRowsLimit, 200);
        });
    };
});
