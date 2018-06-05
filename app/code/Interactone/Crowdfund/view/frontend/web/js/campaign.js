define([
    "jquery"
], function ($) {
    "use strict";

    $.widget('interact.campaign', {
        options: {},

        _create: function () {
            this._bind();
            var self = this;
            self._handleEstimatedProfit();
            self._handleShowButtonCreate();
        },

        _bind: function () {
            var self = this;
        },

        _ajaxEstimatedProfitSubmit: function () {
            var self = this;
            $.ajax({
                url: self.options.estimated.urlProfit,
                type: self.options.estimated.method,
                dataType: 'json',
                data: $(self.options.form).serialize(),
                beforeSend: function () {
                    $('body').trigger('processStart');
                },
                success: function (res) {
                    var elmResult = self.options.estimated.element_result;
                    if (!res.error) {
                        $(elmResult).html('');
                        $(elmResult).html(res.profit);
                        $('#profit_value').val(res.profit);
                        $(elmResult).attr('class','');
                        $(elmResult).addClass('success bold');
                    } else {
                        $(elmResult).html('');
                        $('#profit_value').val(0);
                        $(elmResult).html(res.error_message);
                        $(elmResult).attr('class','');
                        $(elmResult).addClass('error bold');
                    }
                    $('body').trigger('processStop');
                }
            });
        },

        _handleEstimatedProfit: function () {
            var self = this;
            var inputElement = self.options.form + ' .required-profit';

            $(inputElement).on('change', function () {
                if (self._isValid(inputElement)) {
                    self._ajaxEstimatedProfitSubmit();
                }
            });
        },
        _handleShowButtonCreate: function () {
            var self = this;
            var inputElement = self.options.form + ' .input-text';

            $(inputElement).on('change', function () {
                if (self._isValid(inputElement)) {
                    $(self.options.btn_submit).prop('disabled', false);
                } else {
                    $(self.options.btn_submit).prop('disabled', true);
                }
            });
            $( "#prototypes_product_list" ).on("click","button", function(event) {
                if (self._isValid(inputElement)) {
                    $(self.options.btn_submit).prop('disabled', false);
                } else {
                    $(self.options.btn_submit).prop('disabled', true);
                }
            });
        },

        _isValid: function (inputElement) {
            var isValid = false;
            var self = this;

            var empty = $(inputElement).filter(function () {
                return this.value === "";
            });
            if (empty.length == 0) {
                isValid = true;
            }
            return isValid;
        },

    });

    return $.interact.campaign;
});