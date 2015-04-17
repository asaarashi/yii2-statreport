(function($) {
    var buildUrl = function() {
        var self = this;
        var options = self.data('stat-report');

        var params = {};
        $.each(options.params, function(key, param) {
            params[key] = param instanceof $ ? param.val() : param;
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

    var methods = {
        init: function(options) {
            var self = this;
            var defaults = {
                table: null,
                chart: null,
                url: '',
                params: {},
                chartSeries: [],
                chartOptions: {},
                tableOptions: {}
            };

            options = $.extend(defaults, options);
            self.data('stat-report', options);

            var tableOptions = options.tableOptions;
            tableOptions.ajax = {
                'url': buildUrl.call(self),
                'dataSrc': 'table'
            };
            var dataTable = options.table.dataTable(tableOptions);
            self.data('data-tables', dataTable);

            dataTable.on('xhr.dt', function(e, settings, json) {
                var data = options.chartSeries.concat(json.chart);
                console.log(data);  // 调试用
                var highchartsOptions = options.chartOptions;
                highchartsOptions.data = {
                    rows: data
                };
                options.chart.highcharts(highchartsOptions);

                if(typeof json.caption != 'undefined') {
                    self.find('div.statreport-caption').html(json.caption);
                }
            });

            self.find('div.toggle-view-buttons > button').click(function() {
                self.statReport('view', $(this).val());
            });
            self.statReport('view', 'chart');

            return self;
        },

        view: function(type) {
            var self = this;

            self.data('view', type);

            self.find('div.toggle-view-buttons > button').removeClass('btn-primary');
            self.find('div.toggle-view-buttons > button[value="' + type  + '"]').addClass('btn-primary');

            self.find('div.stat-report-view[data-view-role!="' + type + '"]').hide();
            self.find('div.stat-report-view[data-view-role="' + type + '"]').show();
        },

        load: function() {
            var self = this;
            self.data('data-tables').api().ajax.url(buildUrl.call(this)).load();
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
