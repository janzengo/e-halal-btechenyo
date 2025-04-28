$(function() {
    // Only initialize DataTables on tables with .dataTable or .datatable class
    $('table.dataTable, table.datatable').each(function() {
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable({
                "paging": false,
                "lengthChange": false,
                "searching": false,
                "ordering": false,
                "info": false,
                "autoWidth": false
            });
        }
    });

    // Handle report generation
    $('#generateReport').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Generating Report...').prop('disabled', true);
        window.open(window.BASE_URL + 'administrator/pages/includes/controllers/generate_report.php', '_blank');
        setTimeout(function() {
            $btn.html(originalText).prop('disabled', false);
        }, 1000);
    });

    // Color palettes for charts
    const positionColors = [
        'rgba(54, 162, 235, 0.7)',   // Blue
        'rgba(255, 99, 132, 0.7)',   // Pink
        'rgba(75, 192, 192, 0.7)',   // Teal
        'rgba(255, 159, 64, 0.7)',   // Orange
        'rgba(153, 102, 255, 0.7)'   // Purple
    ];

    const partylistColors = [
        'rgba(46, 204, 113, 0.7)',   // Green
        'rgba(52, 152, 219, 0.7)',   // Blue
        'rgba(155, 89, 182, 0.7)',   // Purple
        'rgba(231, 76, 60, 0.7)',    // Red
        'rgba(241, 196, 15, 0.7)'    // Yellow
    ];

    // Debug: Log all available data
    console.log('Window object:', window);
    console.log('Position Data:', window.positionData);
    console.log('Partylist Data:', window.partylistData);

    // Initialize Position Participation Chart
    const positionCtx = document.getElementById('positionParticipationChart');
    console.log('Position chart canvas:', positionCtx);
    
    if (positionCtx && window.positionData && window.positionData.labels && window.positionData.data) {
        console.log('Creating position chart with data:', window.positionData);
        new Chart(positionCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: window.positionData.labels,
                datasets: [{
                    label: 'Total Votes Cast',
                    data: window.positionData.data,
                    backgroundColor: window.positionData.labels.map((_, index) => 
                        positionColors[index % positionColors.length]
                    ),
                    borderColor: window.positionData.labels.map((_, index) => 
                        positionColors[index % positionColors.length].replace('0.7', '1')
                    ),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Votes'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Positions'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Position Participation Rate',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Total Votes: ${context.raw.toLocaleString()}`;
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.error('Position chart initialization failed:', {
            context: positionCtx,
            data: window.positionData
        });
    }

    // Initialize Partylist Chart
    const partylistCtx = document.getElementById('partylistChart');
    console.log('Partylist chart canvas:', partylistCtx);
    
    if (partylistCtx && window.partylistData && window.partylistData.labels && window.partylistData.data) {
        console.log('Creating partylist chart with data:', window.partylistData);
        new Chart(partylistCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: window.partylistData.labels,
                datasets: [{
                    label: 'Total Votes',
                    data: window.partylistData.data,
                    backgroundColor: window.partylistData.labels.map((_, index) => 
                        partylistColors[index % partylistColors.length]
                    ),
                    borderColor: window.partylistData.labels.map((_, index) => 
                        partylistColors[index % partylistColors.length].replace('0.7', '1')
                    ),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Votes'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Partylists'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Partylist Performance',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Total Votes: ${context.raw.toLocaleString()}`;
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.error('Partylist chart initialization failed:', {
            context: partylistCtx,
            data: window.partylistData
        });
    }
}); 