(function() {
    const dataEl = document.getElementById('dashboard-data');
    if (!dataEl) return;
    
    const chartData = JSON.parse(dataEl.textContent);
    
    new Chart(document.getElementById('chartVentas'),{
        type:'line',
        data:{
            labels: chartData.meses_js,
            datasets:[{label:'Ventas (S/)',data: chartData.ventas_js,borderColor:'#c8102e',backgroundColor:'rgba(200,16,46,.1)',fill:true,tension:.4,pointRadius:5,pointBackgroundColor:'#c8102e'}]
        },
        options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{ticks:{color:'rgba(255,255,255,.4)'},grid:{color:'rgba(255,255,255,.05)'}},y:{ticks:{color:'rgba(255,255,255,.4)'},grid:{color:'rgba(255,255,255,.05)'}}}}
    });

    new Chart(document.getElementById('chartPie'),{
        type:'doughnut',
        data:{labels: chartData.pie_labels,datasets:[{data: chartData.pie_data,backgroundColor: chartData.pie_colors,borderWidth:0}]},
        options:{responsive:true,plugins:{legend:{position:'bottom',labels:{color:'rgba(255,255,255,.6)',padding:12,font:{size:12}}}}}
    });
})();