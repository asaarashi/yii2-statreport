(function($) {
    var buildUrl = function() {
        var self = this;
        var options = self.data('statreport');

        var params = {};
        $.each(options.params, function(key, param) {
            if(param instanceof $) {
                if(param.is("input") && param.attr("type") == "checkbox") {
                    params[key] = param.filter(":checked").val();
                } else {
                    params[key] = param.val();
                }
            } else {
                params[key] = param;
            }
        });

        var query = $.param(params);
        var url = '';
        if (options.url.indexOf("?") != -1){
            url = options.url + '&' + query;
        } else {
            url = options.url + '?' + query;
        }
        return url;
    };

    var ajaxMask = function(options) {
        var settings = $.extend({
            stop: false
        }, options);

        if (!settings.stop) {
            var loadingDiv = $('<div class="ajax-mask"><div class="loading"></div></div>')
                .css({
                    'position': 'absolute',
                    'top': 0,
                    'left':0,
                    'width':'100%',
                    'height':'100%'
                });

            $(this).css({ 'position':'relative' }).append(loadingDiv);
        } else {
            $(this).find('.ajax-mask').remove();
        }
    };

    var methods = {
        init: function(options) {
            $.fn.dataTable.ext.errMode = 'none';

            var self = this;
            var defaults = {
                table: null,
                chart: null,
                url: '',
                params: {},
                chartSeries: [],
                chartOptions: {},
                dataTablesOptions: {},
                onError: null,
                onSuccess: null,
                onFailure: null,
                onBeforeRequest: null,
                autoloading: true
            };
            options = $.extend(defaults, options);
            self.data('statreport', options);

            self.find('div.statreport-switcher-buttons > button').click(function() {
                self.statReport('view', $(this).val());
            });
            self.statReport('view', 'chart');

            if(options.autoloading) {
                self.statReport('construct');
            }
        },

        construct: function() {
            var self = this;

            var options = self.data('statreport');

            var dataTablesOptions = options.dataTablesOptions;
            dataTablesOptions.ajax = {
                'url': buildUrl.call(self),
                'dataSrc': 'table'
            };

            var dataTable = options.table.on('preXhr.dt', function(e, settings, data) {
                self.data('statreport-loading', true);
                ajaxMask.call(self);
            });
            if(options.onBeforeRequest != null) {
                dataTable.on('preXhr.dt', options.onBeforeRequest);
            }
            dataTable.dataTable(dataTablesOptions);

            self.data('data-tables', dataTable);

            if(options.onError !== null) {
                dataTable.on('error.dt', options.onError);
            }
            dataTable.on('xhr.dt', function(e, settings, json) {
                self.data('statreport-loading', false);

                if(json != null) {
                    if(json.status == 0) {
                        var data = options.chartSeries.concat(json.chart);
                        var highchartsOptions = options.chartOptions;
                        highchartsOptions.data = {
                            rows: data
                        };
                        options.chart.highcharts(highchartsOptions);

                        if(typeof json.caption != 'undefined') {
                            self.find('div.statreport-caption').html(json.caption);
                        }
                    } else {
                        if(options.onFailure != null) {
                            options.onFailure(e, settings, json);
                        }
                    }
                }

                ajaxMask.call(self, { stop: true });
            });
            if(options.onSuccess !== null) {
                dataTable.on('xhr.dt', options.onSuccess);
            }

            self.data('constructed', true);
            return self;
        },

        view: function(type) {
            var self = this;

            self.data('view', type);

            self.find('div.statreport-switcher-buttons > button').removeClass('btn-primary');
            self.find('div.statreport-switcher-buttons > button[value="' + type  + '"]').addClass('btn-primary');

            self.find('div.statreport-view[data-view-role!="' + type + '"]').hide();
            self.find('div.statreport-view[data-view-role="' + type + '"]').show();

            if(type == 'chart') {
                $(window).trigger('resize');
            }
        },

        load: function() {
            var self = this;
            if(self.data('statreport-loading')) {
                return ;
            }
            if( ! self.data('constructed')) {
                self.statReport('construct');
            } else {
                self.data('data-tables').api().ajax.url(buildUrl.call(this)).load();
            }
        }
    };

    $.fn.statReport = function() {
        var method = arguments[0];

        var isDefaultMethod = false;
        if(typeof methods[method] != 'undefined') {
            method = methods[method];
        } else if(typeof method == "object" || ! method) {
            isDefaultMethod = true;
            method = methods.init;
        } else {
            $.error("Method " +  method + " does not exist on statReport");
            return this;
        }

        return method.apply(this, Array.prototype.slice.call(arguments, isDefaultMethod ? 0 : 1, arguments.length));
    };

})(jQuery);
