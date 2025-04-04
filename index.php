<?php
// session_start();
// if (!isset($_SESSION['user'])) {
//     header("Location: https://rms.mangaldeepgrp.com/login.php");
//     exit();
// }
?>
<?php

date_default_timezone_set('Asia/Kolkata'); 

$current_hour = date('H');
 
    if ($current_hour >= 18 || $current_hour < 6) {
    $light_status = "Light On";
    $light_color = "green";
} else {
    $light_status = "Light Off";
    $light_color = "red";
}

// Static Battery graph data
$graph_data = [
    "BV" => [13.1, 13.1, 13.1, 13, 13.1,13.1, 13.2, 13.7, 13.9, 14.6, 14.7, 13.4, 13.3, 13.1, 13.2, 13.2 , 13.1, 13.1,13.1, 13, 13.1, 13.1, 13.2 , 13.7, 13.9, 14.7 ],  // Battery Voltage
    "BI" => [0.75, 1.53, 1.51, 0, 0.06, 0.28, 5.1, 5.68, 0.03, 5.68, 0.03, 0.03,1.47,1.49,1.51,0.75,0.75,0.76,1.54,1.53,0,0.06,0.36,4.73,5.43,0.03],  // Battery Current
    "BP" => [9.8,20,19.7,19.7,0,0.7,3.6,69.8,78.9,0.4,0.4,19.6,19.8,19.7,9.9,9.9,9.9,20.1,19.8,0,0.7,4.7,64.8,75.4,0.4]                   // Battery Power
];

// Static Solar graph data
$solar_data = [
    "SV" => [0.9,0.9,0.8,6,14,13.4,17.4,17.4,19.5,19.9,2.5,0.9,1,0.7,0.9,1,6,14,16.7,17.2,16.9,19.8],  // Solar Voltage
    "SI" => [0,0,0,0,0.05,0.28,4.21,4.62,0.02,0.02,0,0,0,0,0,0,0,0.5,0.29,3.91,4.64,0.02],  // Solar Current
    "SP" => [0,0,0,0,0.7,3.7,73.2,80.3,0.3,0.3,0,0,0,0,0,0,0,0.7,4.8,67.2,78.4,0.3]   // Solar Power
];


// Static load graph data
$load_data = [
    "LV" =>  [8.2,8.3,8.3,8.3,0,0,0,0,0,0,0,8.3,8.3,8.3,8.2,8.2,8.2,8.3,8.3,0,0,0,0,0,0,0],  // Load Voltage
    "LC" =>  [1.13,2.26,2.26,2.26,0,0,0,0,0,0,0,2.25,2.19,2.19,1.12,1.12,1.12,2.3,2.22,0,0,0,0,0,0,0],  // Load Current
    "LP" =>  [9.2,18.7,18.7,18.7,0,0,0,0,0,0,0,18.6,18.1,18.1,9.1,9.1,9.1,19,18.4,0,0,0,0,0,0,0]  // Load Power
];


// Static load graph data
$fault_data = [
    "F" => [0,0,0,0,0,0,0,0,0,0,],  // Fault
];

$graph_json = json_encode($graph_data);
$solar_json = json_encode($solar_data);
$load_json  = json_encode($load_data);
$fault_json = json_encode($fault_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Solar RMS Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
     <!-- Leaflet CSS -->
     <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
</head>
<body>

<nav class="navbar navbar-light bg-light header">
  <div class="container">
    <a class="navbar-brand" href="https://rms.mangaldeepgrp.com/"><img src="images/logo.png" alt=""></a>
    <form class="d-flex">
    <a class="logout" href="logout.php">Logout</a>
    </form>
  </div>
</nav>

<!-- Main Container -->
<div class="container">

    <!-- Date Range & Apply Button -->
    <div id="filter-section" style="display: flex; gap: 10px; align-items: center; margin-bottom: 20px; margin-top: 40px;">
        <input type="date" id="start-date" value="<?php echo date('Y-m-d'); ?>"> - 
        <input type="date" id="end-date" value="<?php echo date('Y-m-d'); ?>">
        <button id="apply-btn">APPLY</button>
    </div>

<!-- Loading Animation -->
    <div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
    background: rgba(255, 255, 255, 0.8); z-index: 9999; justify-content: center; align-items: center;">
        <div class="loader"></div>
    </div>


    <!-- Device Selector -->
    <div class="grid">
        <div class="card">
            <div>Device No :</div>
            <select id="deviceSelect">
            <?php
            for ($i = 2001; $i <= 2103; $i++) {
                echo "<option value='$i'>SC$i</option>"; // Important: value='$i' (number only), display 'SC2001'
            }
            ?>
            </select>
        </div>
        <!-- Load Section -->
        <div class="card">
            <div>Load :</div>
            <div class="highlight" style="color: <?php echo $light_color; ?>;">
                <?php echo $light_status; ?>
                <?php if ($light_status == "Light On"): ?>
                    <img src="images/light-on.png" alt="Light On" style="width:30px; height: 30px; margin-left: 5px;">
                <?php else: ?>
                    <img src="images/light-off.png" alt="Light Off" style="width: 20px; height: 20px; margin-left: 5px;">
                <?php endif; ?>
            </div>
        </div>

        <!-- System Status -->
        <div class="card">
            <div>System Status :</div>
            <div class="highlight">NO FAULT</div>
        </div>
    </div>

    <!-- Battery & Solar Readings -->
    <div class="grid" style="margin-top: 20px;">
        <div class="card">
            <div>Battery Voltage :</div>
            <div class="highlight">14.6</div>
        </div>
        <div class="card">
            <div>Battery Current :</div>
            <div class="highlight">0.03</div>
        </div>
        <div class="card">
            <div>Solar Voltage :</div>
            <div class="highlight">19.5</div>
        </div>
        <div class="card">
            <div>Solar Current :</div>
            <div class="highlight">0.02</div>
        </div>
    </div>

    <div class="container">
    <h3 id="locationTitle" class="text-center mt-3">Vanicha Pada Locations</h3>
    <div id="map"></div>
    
    <!-- Battery Graph -->
    <div class="card mt-3">
        <div id="batteryGraph" style="height: 400px;"></div>
    </div>

    <!-- Solar Graph -->
    <div class="card mt-3">
        <div id="solarGraph" style="height: 400px;"></div>
    </div>

     <!-- Load Graph -->
    <div class="card mt-3">
        <div id="loadGraph" style="height: 400px;"></div>
    </div>

      <!-- Fault Graph -->
    <div class="card mt-3">
        <div id="faultGraph" style="height: 400px;"></div>
     </div>

     <div class="card">
        <h3>Map View</h3>
        <input type="text" id="citySelect" placeholder="Location" readonly style="margin-top: 10px; width: 100%; padding: 5px;" />
        <div id="mapContainer" style="margin-top: 20px;"></div>
    </div>
   
</div>
</div>

<div class="container-fluid footer bg-light">
        <p> Copyright &copy; 2025 . – | Developed by <a href="https://imborndigital.com/">Imborn Digital</a></p>
    </div>

</body>
<script>    
            const poleLocations = {
        2001: { lat: 19.0760, lng: 73.8777 },
        2002: { lat: 19.077262366415177, lng:  73.87723329141855 },
        2050: { lat: 19.321437, lng: 73.066538 },
        2051: { lat: 19.322002670372516, lng: 73.06681072604303 }
    };

    var map = L.map('map').setView([19.3217, 73.0667], 17);

    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        attribution: '© Google Maps Satellite'
    }).addTo(map);

    let markers = [];

    function updateMap(selectedId) {
        // Remove all existing markers
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];

        Object.keys(poleLocations).forEach(id => {
            const location = poleLocations[id];

            let marker = L.marker([location.lat, location.lng]).addTo(map)
                .bindPopup(`
                    <b>Pole Location: SC${id}</b><br>
                    <a href="https://www.google.com/maps?q=${location.lat},${location.lng}" target="_blank">Open in Google Maps</a>
                `);

            markers.push(marker);
        });

        // Center the map on the selected location
        if (poleLocations[selectedId]) {
            map.setView([poleLocations[selectedId].lat, poleLocations[selectedId].lng], 17);
        }
    }

    document.getElementById('deviceSelect').addEventListener('change', function() {
        updateMap(this.value);
    });

    updateMap("2001"); // Default selection

document.getElementById("deviceSelect").addEventListener("change", function () {
    var selectedDevice = this.value; // Get selected device number
    document.getElementById("locationTitle").innerText = " - SC" + selectedDevice + " Location";
});

function updateMapAndLocation(selectedNumber) {
    let padaName = getPadaByNumber(selectedNumber);
    let mapContainer = document.getElementById("mapContainer");
    let cityInput = document.getElementById("citySelect");

    // Set the pada name in citySelect input
    if (cityInput) {
        cityInput.value = padaName;
    }

    // Assuming you will define padaMaps somewhere in your real code
    if (typeof padaMaps !== "undefined" && padaMaps[padaName]) {
        mapContainer.innerHTML = `<iframe src="${padaMaps[padaName]}" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>`;
    } else {
        mapContainer.innerHTML = "No map available for this Device.";
    }
}

// Handle selection change to update map and location
document.getElementById("deviceSelect").addEventListener("change", function () {
    let selectedNumber = parseInt(this.value);
    updateMapAndLocation(selectedNumber);
});

// Trigger default map on page load
window.addEventListener("load", function () {
    let selectElement = document.getElementById("deviceSelect");
    let selectedNumber = parseInt(selectElement.value);

    // If nothing is selected, set a default number (example: 2001)
    if (isNaN(selectedNumber)) {
        selectedNumber = 2001; // Default number
        selectElement.value = selectedNumber;
    }

    // Update map and city input on load
    updateMapAndLocation(selectedNumber);
});

    let graphData = <?= $graph_json ?>;
    let solarData = <?= $solar_json ?>;
    let loadData = <?= $load_json ?>;
    let faultData = <?= $fault_json ?>;
    
    // Initialize Highcharts
    let batteryChart = Highcharts.chart('batteryGraph', {
        chart: { type: 'line' },
        title: { text: 'Battery Graph for Device No: SC2001' },
        subtitle: { text: 'Source: Device Data' },
        xAxis: {
                    labels: {
                        enabled: false // This will hide the numbers/labels on X-axis
                    },
                    tickLength: 0, 
                    lineWidth: 0,   
                },    
        yAxis: { title: { text: 'Value' } },    
        series: [
            { name: 'Battery Voltage (BV)', data: graphData.BV, color: '#0099FF' },
            { name: 'Battery Current (BI)', data: graphData.BI, color: '#6600CC' },
            { name: 'Battery Power (BP)', data: graphData.BP, color: '#00FF00' }
        ]
    });

    let solarChart = Highcharts.chart('solarGraph', {
        chart: { type: 'line' },
        title: { text: 'Solar Graph for Device No: SC2001' },
        subtitle: { text: 'Source: Device Data' },
        xAxis: {
                    labels: {
                        enabled: false // This will hide the numbers/labels on X-axis
                    },
                    tickLength: 0, 
                    lineWidth: 0,   
                },
        yAxis: { title: { text: 'Value' } },
        series: [
            { name: 'Solar Voltage (SV)', data: solarData.SV, color: '#00BFFF' },
            { name: 'Solar Current (SI)', data: solarData.SI, color: '#800080' },
            { name: 'Solar Power (SP)', data: solarData.SP, color: '#32CD32' }
        ]
    });

    
    let loadChart = Highcharts.chart('loadGraph', {
        chart: { type: 'line' },
        title: { text: 'Load Graph for Device No: SC2001' },
        subtitle: { text: 'Source: Device Data' },
        xAxis: {
                    labels: {
                        enabled: false // This will hide the numbers/labels on X-axis
                    },
                    tickLength: 0, 
                    lineWidth: 0,   
                },
        yAxis: { title: { text: 'Value' } },
        series: [
            { name: 'Load Voltage (LV)', data: loadData.LV, color: '#00BFFF' },
            { name: 'Load Current (LC)', data: loadData.LC, color: '#800080' },
            { name: 'Load Power (LP)', data: loadData.LP, color: '#32CD32' }
        ]
    });


    let faultChart = Highcharts.chart('faultGraph', {
        chart: { type: 'line' },
        title: { text: 'Load Graph for Device No: SC2001' },
        subtitle: { text: 'Source: Device Data' },
        xAxis: {
                    labels: {
                        enabled: false // This will hide the numbers/labels on X-axis
                    },
                    tickLength: 0, 
                    lineWidth: 0,   
                },
        yAxis: { title: { text: 'Value' } },
        series: [
            { name: 'Load Voltage (F)', data: faultData.F, color: '#00BFFF' }
        ]
    });


    // Device Selection Change Listener
    document.getElementById("deviceSelect").addEventListener("change", function () {
        let selectedDevice = this.value;

        // Update Graph Titles Dynamically
        batteryChart.setTitle({ text: `Battery Graph for Device No: ${selectedDevice}` });
        solarChart.setTitle({ text: `Solar Graph for Device No: ${selectedDevice}` });
        loadChart.setTitle({ text: `load Graph for Device No: ${selectedDevice}` });
        faultChart.setTitle({ text: ` Graph for Device No: ${selectedDevice}` });
    });



document.getElementById('apply-btn').addEventListener('click', function() {
    
    document.getElementById('loading-overlay').style.display = 'flex';

    setTimeout(function() {
        location.reload(); 
    }, 1000);
});


</script>
</html>
