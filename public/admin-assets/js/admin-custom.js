/**
 * Custom Admin Dashboard JavaScript
 * Enhances the SB Admin 2 template with custom functionality
 * and applies consistent color theming for charts and interactions
 */

// Define custom color palette matching the site branding
const customColors = {
    primary: '#F28123',
    primaryDark: '#d16a0d',
    primaryLight: '#f4941a',
    dark: '#051922',
    darkSecondary: '#162133',
    success: '#1cc88a',
    warning: '#f6c23e',
    danger: '#e74a3b',
    info: '#36b9cc',
    light: '#f5f5f5',
    gray: {
        100: '#f8f9fc',
        200: '#eaecf4',
        300: '#dddfeb',
        400: '#d1d3e2',
        500: '#b7b9cc',
        600: '#858796'
    }
};

// Chart.js default color overrides
if (typeof Chart !== 'undefined') {
    Chart.defaults.color = customColors.gray[600];
    Chart.defaults.borderColor = customColors.gray[200];
    Chart.defaults.backgroundColor = customColors.gray[100];
}

// Enhanced DataTables configuration
$(document).ready(function() {
    // Apply custom styling to DataTables if present
    if ($.fn.DataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            pageLength: 10,
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function() {
                // Apply custom styling after each draw
                $('.dataTables_paginate .paginate_button.current').css({
                    'background': customColors.primary,
                    'border-color': customColors.primary,
                    'color': 'white'
                });
            }
        });
    }

    // Enhanced tooltip functionality
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body',
        trigger: 'hover'
    });    // Enhanced sidebar toggle for fixed positioning
    $('#sidebarToggle, #sidebarToggleTop').on('click', function() {
        $('body').toggleClass('sidebar-toggled');
        $('.sidebar').toggleClass('toggled');
        
        // Store sidebar state in localStorage
        if ($('.sidebar').hasClass('toggled')) {
            localStorage.setItem('sidebarToggled', 'true');
        } else {
            localStorage.setItem('sidebarToggled', 'false');
        }
        
        // Close any open collapsible menu items when sidebar is hidden
        if ($('.sidebar').hasClass('toggled')) {
            $('.sidebar .collapse').collapse('hide');
        }
    });

    // Restore sidebar state from localStorage
    if (localStorage.getItem('sidebarToggled') === 'true') {
        $('body').addClass('sidebar-toggled');
        $('.sidebar').addClass('toggled');
    }

    // Mobile sidebar behavior - close sidebar when clicking outside
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.sidebar, #sidebarToggle, #sidebarToggleTop').length) {
                if ($('.sidebar').hasClass('toggled')) {
                    $('body').removeClass('sidebar-toggled');
                    $('.sidebar').removeClass('toggled');
                    localStorage.setItem('sidebarToggled', 'false');
                }
            }
        }
    });

    // Responsive sidebar handling
    $(window).resize(function() {
        // Auto-hide sidebar on small screens
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        }
        
        // Auto-show sidebar on large screens if not manually toggled
        if ($(window).width() > 768 && localStorage.getItem('sidebarToggled') !== 'true') {
            $('body').removeClass('sidebar-toggled');
            $('.sidebar').removeClass('toggled');
        }
    });

    // Enhanced card hover effects
    $('.card').hover(
        function() {
            $(this).addClass('shadow-lg').removeClass('shadow');
        },
        function() {
            $(this).removeClass('shadow-lg').addClass('shadow');
        }
    );

    // Auto-hide alerts after 5 seconds
    $('.alert').each(function() {
        if (!$(this).hasClass('alert-permanent')) {
            setTimeout(() => {
                $(this).fadeOut('slow');
            }, 5000);
        }
    });

    // Enhanced form validation styling
    $('form').on('submit', function() {
        $(this).find('.btn[type="submit"]').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin"></i> Processing...'
        );
    });

    // Custom number formatting for dashboard metrics
    $('.metric-number').each(function() {
        const number = parseInt($(this).text().replace(/,/g, ''));
        if (!isNaN(number)) {
            $(this).text(number.toLocaleString());
        }
    });

    // Enhanced dropdown menus
    $('.dropdown-toggle').dropdown();

    // Custom confirmation dialogs
    $('[data-confirm]').on('click', function(e) {
        const message = $(this).data('confirm');
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });

    // Progress bar animations
    $('.progress-bar').each(function() {
        const width = $(this).attr('aria-valuenow') + '%';
        $(this).animate({ width: width }, 1000);
    });
});

// Custom Chart.js functions
function createAreaChart(ctx, labels, data, label = 'Data') {
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                lineTension: 0.3,
                backgroundColor: hexToRgba(customColors.primary, 0.1),
                borderColor: customColors.primary,
                pointRadius: 3,
                pointBackgroundColor: customColors.primary,
                pointBorderColor: customColors.primary,
                pointHoverRadius: 3,
                pointHoverBackgroundColor: customColors.primaryDark,
                pointHoverBorderColor: customColors.primaryDark,
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: data,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                x: {
                    time: {
                        unit: 'date'
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                },
                y: {
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value, index, values) {
                            return '$' + number_format(value);
                        }
                    },
                    gridLines: {
                        color: customColors.gray[200],
                        zeroLineColor: customColors.gray[200],
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: "rgba(255,255,255,.8)",
                    bodyColor: customColors.gray[600],
                    titleMarginBottom: 10,
                    titleColor: customColors.dark,
                    titleFontSize: 14,
                    borderColor: customColors.gray[200],
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            const datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': $' + number_format(tooltipItem.parsed.y);
                        }
                    }
                }
            }
        }
    });
}

function createBarChart(ctx, labels, data, label = 'Data') {
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                backgroundColor: customColors.primary,
                hoverBackgroundColor: customColors.primaryDark,
                borderColor: customColors.primary,
                data: data
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                x: {
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 6
                    },
                    maxBarThickness: 25
                },
                y: {
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value, index, values) {
                            return number_format(value);
                        }
                    },
                    gridLines: {
                        color: customColors.gray[200],
                        zeroLineColor: customColors.gray[200],
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    titleMarginBottom: 10,
                    titleColor: customColors.dark,
                    titleFontSize: 14,
                    backgroundColor: "rgba(255,255,255,.8)",
                    bodyColor: customColors.gray[600],
                    borderColor: customColors.gray[200],
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            const datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': ' + number_format(tooltipItem.parsed.y);
                        }
                    }
                }
            }
        }
    });
}

function createDoughnutChart(ctx, labels, data) {
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    customColors.primary,
                    customColors.success,
                    customColors.info,
                    customColors.warning,
                    customColors.danger
                ],
                hoverBorderColor: "rgba(234, 236, 244, 1)"
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    backgroundColor: "rgba(255,255,255,.8)",
                    bodyColor: customColors.gray[600],
                    borderColor: customColors.gray[200],
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10
                },
                legend: {
                    display: false
                }
            },
            cutout: '80%'
        }
    });
}

// Utility functions
function number_format(number, decimals = 0, dec_point = '.', thousands_sep = ',') {
    number = (number + '').replace(',', '').replace(' ', '');
    const n = !isFinite(+number) ? 0 : +number;
    const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
    const sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
    const dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
    let s = '';
    
    const toFixedFix = function (n, prec) {
        const k = Math.pow(10, prec);
        return '' + Math.round(n * k) / k;
    };
    
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function hexToRgba(hex, alpha = 1) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? 
        `rgba(${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}, ${alpha})` :
        null;
}

// Export for global use
window.customColors = customColors;
window.createAreaChart = createAreaChart;
window.createBarChart = createBarChart;
window.createDoughnutChart = createDoughnutChart;
