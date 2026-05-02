import Chart from "chart.js";
import { Tooltip } from "bootstrap";

require("jquery-ui/ui/widgets/sortable");
import "@selectize/selectize";
import "./theme";

window.$ = window.jQuery = require("jquery");

document.querySelectorAll('[data-toggle*="tooltip"]').forEach((el) => new Tooltip(el));

function applyChartTheme() {
    const dark = document.documentElement.getAttribute("data-bs-theme") === "dark";
    Chart.defaults.global.defaultFontColor = dark ? "#dee2e6" : "#666";
    Chart.defaults.scale.gridLines.color = dark ? "rgba(255,255,255,0.1)" : "rgba(0,0,0,0.1)";
    Chart.defaults.scale.gridLines.zeroLineColor = dark ? "rgba(255,255,255,0.25)" : "rgba(0,0,0,0.25)";
}

applyChartTheme();

(function () {
    let activeChart = null;

    window.chart_init = function chart_init(ctx, chart) {
        activeChart = new Chart(ctx, chart);
        return activeChart;
    };

    document.addEventListener("themechange", () => {
        applyChartTheme();
        if (activeChart) {
            activeChart.update();
        }
    });

    function getIconForVendor(vendor) {
        if (window.pkgtrends.vendors !== undefined) {
            return window.pkgtrends.vendors[vendor];
        }

        return "";
    }

    var input = jQuery("#packages");

    input.selectize({
        plugins: ["remove_button", "drag_drop"],
        create: false,
        maxOptions: 16,
        valueField: "id",
        labelField: "name",
        searchField: "name",

        render: {
            item: function (item, escape) {
                var icon = getIconForVendor(item.vendor);

                return '<div class="selected-item">' + '<i class="' + icon + '"></i><span class="name">' + escape(item.name) + "</span></div>";
            },

            option: function (item, escape) {
                var icon = getIconForVendor(item.vendor);

                return (
                    '<div class="item">' +
                    '<span class="title"><span class="name"><i class="' +
                    icon +
                    '"></i>' +
                    escape(item.name) +
                    "</span></span>" +
                    (item.description !== undefined && item.description !== null ? '<span class="description">' + escape(item.description) + "</span>" : "") +
                    "</div>"
                );
            },
        },

        load: function (query, callback) {
            if (!query.length) return callback();

            jQuery.ajax({
                url: "/search/?query=" + encodeURIComponent(query),
                type: "GET",
                error: function () {
                    callback();
                },
                success: function (res) {
                    callback(res);
                },
            });
        },

        onChange: function (value) {
            history.replaceState({}, null, window.location.origin + "/" + value.split(",").join("-vs-"));

            input[0].selectize.close();
            input[0].selectize.disable();

            window.location.reload();
        },
    });

    var options = jQuery("#package-options"),
        items = jQuery("#package-items");

    if (options.length && items.length) {
        jQuery(options.data("value")).each(function (_, option) {
            input[0].selectize.addOption(option);
        });

        input[0].selectize.addItems(items.data("value"), true);
    }

    var labels = jQuery("#chart-labels"),
        datasets = jQuery("#chart-datasets");

    if (labels.length && items.length) {
        chart_init(jQuery("#chart"), {
            type: "line",
            legend: {
                position: "bottom",
            },
            data: {
                labels: labels.data("value"),
                datasets: datasets.data("value"),
            },
        });
    }
})();
