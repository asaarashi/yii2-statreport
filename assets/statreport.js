(function($) {
    var buildUrl = function(params) {
        var self = this;
        var options = self.data('statreport');

        var url = options.url;
        if (options.url.indexOf("?") != -1){
            url += '&' + params;
        } else {
            url += '?' + params;
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
                chartSeries: [],
                chartOptions: {},
                dataTablesOptions: {},
                onError: null,
                onSuccess: null,
                onFailure: null,
                onBeforeRequest: null,
                enablePagination: false,
                fixedHeader: true,
                pageSize: 10
            };
            options = $.extend(defaults, options);
            self.data('statreport', options);

            // Bind switch buttons
            self.find('div.statreport-switcher-buttons > button').click(function() {
                self.statReport('view', $(this).val());
            });
            self.statReport('view', 'chart');

            //if(options.autoloading) {
            //    self.statReport('construct', params);
            //}
        },

        construct: function(params) {
            var self = this;

            if(typeof params != 'undefined') {
                self.statReport('params', params);
            } else {
                params = self.statReport('params');
            }
            var options = self.data('statreport');

            var dataTablesOptions = options.dataTablesOptions;
            dataTablesOptions.ajax = {
                'url': buildUrl.call(self, params),
                'dataSrc': 'table'
            };

            var dataTable = options.table.on('preXhr.dt', function(e, settings, data) {
                self.data('statreport-loading', true);
                // Show AJAX spinner
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
                        var rawData = options.chartSeries ? options.chartSeries.concat(json.chart) : [];
                        var highchartsOptions = options.chartSeries ? options.chartOptions : {};

                        var data = rawData;
                        var setData = function(data) {
                            highchartsOptions.data = {
                                rows: data
                            };
                            if (options.chart) options.chart.highcharts(highchartsOptions);
                        };
                        if(options.enablePagination) {
                            var countItems = data.length - 1;
                            var totalPages = countItems % options.pageSize == 0 ?
                            countItems / options.pageSize : parseInt(countItems / options.pageSize) + 1;
                            var currentPage = 1;
                            var pagerContainer = self.find(".statreport-pagination");
                            pagerContainer.data('statreport-raw-data', rawData);

                            pagerContainer.pagy({
                                currentPage: currentPage,
                                totalPages: totalPages,
                                page: function(p) {
                                    currentPage = p;
                                    var rawData = pagerContainer.data('statreport-raw-data');
                                    if(typeof rawData[0] == 'undefined') {
                                        return true;
                                    }
                                    var from = (p-1)*options.pageSize+1;
                                    var to = from + options.pageSize;
                                    data = [rawData[0]].concat(rawData.slice(from, to));
                                    setData(data);

                                    return true;
                                }
                            });
                            pagerContainer.pagy("page", currentPage);
                            if(totalPages == 1) {
                                pagerContainer.hide();
                            }
                        } else {
                            setData(data);
                        }

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

            self.find('div.statreport-switcher-buttons > button')
                .removeClass('btn-primary')
                .addClass('btn-white');
            self.find('div.statreport-switcher-buttons > button[value="' + type  + '"]')
                .removeClass('btn-white')
                .addClass('btn-primary');

            if (self.find('div.statreport-view').length > 1) {
                self.find('div.statreport-view[data-view-role!="' + type + '"]').hide();
                self.find('div.statreport-view[data-view-role="' + type + '"]').show();
            }

            if(type == 'chart') {
                $(window).trigger('resize');
                if(self.data('statreport').enablePagination) {
                    self.find(".statreport-pagination").show();
                }
            } else {
                if(self.data('statreport').enablePagination) {
                    self.find(".statreport-pagination").hide();
                }
            }
        },

        params: function(params) {
            var self = this;
            if(typeof params != 'undefined') {
                if(typeof params == 'object') {
                    params = $.param(params);
                }
                self.data('params', params);
            } else {
                return self.data('params');
            }
        },

        load: function(params) {
            var self = this;
            if(self.data('statreport-loading')) {
                return ;
            }
            if(typeof params != 'undefined') {
                self.statReport('params', params);
            } else {
                params = self.statReport('params');
            }
            if( ! self.data('constructed')) {
                self.statReport('construct', params);
            } else {
                self.data('data-tables').api().ajax.url(buildUrl.call(self, params)).load();
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
