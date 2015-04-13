(function($) {
    var buildUrl = function() {
        var options = this.data('stat-report');

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
            var defaults = {
                table: null,
                chart: null,
                url: '',
                params: {},
                chartSeries: [],
                chartOptions: {}
            };

            var options = $.extend(defaults, options);
            this.data('stat-report', options);

            var tableOptions = options.tableOptions;
            tableOptions.ajax = buildUrl.call(this);
            var dataTable = options.table.dataTable(tableOptions);
            this.data('data-tables', dataTable);

            dataTable.on('xhr.dt', function(e, settings, json) {
                var data = options.chartSeries.concat(json.data);
                console.log(data);
                var highchartsOptions = options.chartOptions;
                highchartsOptions.data = {
                    rows: data
                };
                options.chart.highcharts(highchartsOptions);
            });

            //this.statReport('load');

            return this;
        },

        load: function() {
            this.data('data-tables').api().ajax.url(buildUrl.call(this)).load();
        }
    };

    $.fn.statReport = function() {
        var method = arguments[0];

        if(typeof methods[method] != 'undefined') {
            method = methods[method];
        } else if(typeof method == "object" || ! method) {
            method = methods.init;
        } else {
            $.error("Method " +  method + " does not exist on statReport");
            return this;
        }

        return method.apply(this, Array.prototype.slice.call(arguments));
    };
})(jQuery);

