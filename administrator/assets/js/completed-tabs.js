// Enhanced tab switching and DataTable initialization for Election Completed page

document.addEventListener('DOMContentLoaded', function() {
    var tabButtons = document.querySelectorAll('.custom-tab-btn');
    var tabContents = document.querySelectorAll('.custom-tab-content');
    
    // Tab switching functionality
    tabButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons and content
            tabButtons.forEach(function(b) { b.classList.remove('active'); });
            tabContents.forEach(function(tc) { 
                tc.classList.remove('active');
                // Add fade-out animation
                tc.style.opacity = 0;
            });
            
            // Add active class to clicked button
            btn.classList.add('active');
            
            // Get and activate corresponding tab content
            var tabId = btn.getAttribute('data-tab');
            var tabPane = document.getElementById(tabId);
            if(tabPane) {
                tabPane.classList.add('active');
                // Trigger reflow to enable animation
                void tabPane.offsetWidth;
                // Fade in the content
                tabPane.style.opacity = 1;
                
                // Reinitialize any charts if needed
                if (typeof Chart !== 'undefined' && window.positionData && window.partylistData) {
                    // Give a small delay for the tab to become visible
                    setTimeout(function() {
                        try {
                            // Trigger window resize to redraw charts correctly
                            window.dispatchEvent(new Event('resize'));
                        } catch (e) {
                            console.error('Error refreshing charts:', e);
                        }
                    }, 100);
                }
            }
        });
    });
    
    // Initialize DataTables
    $('.result-table').each(function() {
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: false, // Keep ordering off since we sort in PHP
                responsive: true,
                language: {
                    emptyTable: 'No results found for this position.'
                },
                drawCallback: function() {
                    // Highlight top vote
                    $(this).find('.top-vote-row').css('background-color', '#e6ffe6');
                }
            });
        }
    });
});
