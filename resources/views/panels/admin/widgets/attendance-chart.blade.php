<div class="col-md-12">
    <div class="card h-100">
        <div class="card-header">
            <h3>Attendance Overview</h3>
        </div>
        <div class="card-body">
            <canvas id="attendanceChart" height="280"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  if(document.getElementById('attendanceChart')) {
      const ctx = document.getElementById('attendanceChart').getContext('2d');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
          datasets: [{
            label: 'الحضور الشهري (%)',
            data: [92, 95, 94, 96, 95, 98],
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
  }
</script>
@endpush
