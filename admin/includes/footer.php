      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Add any custom JavaScript for the admin panel here
    
    // Error handling for DataTables initialization
    document.addEventListener('DOMContentLoaded', function() {
      // Add error handling for DataTables
      if (typeof $.fn.dataTable !== 'undefined') {
        // DataTables is loaded properly
        console.log('DataTables loaded successfully');
      } else {
        console.error('DataTables library not loaded properly');
      }
      
      // Add error handling for Chart.js
      if (typeof Chart !== 'undefined') {
        // Chart.js is loaded properly
        console.log('Chart.js loaded successfully');
      } else {
        console.error('Chart.js library not loaded properly');
      }
    });
  </script>
</body>
</html>