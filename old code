<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: https://rms.mangaldeepgrp.com/login.php");
    exit();
}
?>
<?php

date_default_timezone_set('Asia/Kolkata'); // Set to Indian time zone

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
    <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
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

<div class="container-fluid footer bg-light">
        <p> Copyright &copy; 2025 . – | Developed by <a href="https://imborndigital.com/">Imborn Digital</a></p>
    </div>

</body>
<script>    
    
    // Mapping SC numbers to Padas (locations)
    function getPadaByNumber(num) {
        if (num >= 2001 && num <= 2028) return "Vanicha Pada";
        if (num >= 2029 && num <= 2040) return "Devicha Pada";
        if (num >= 2041 && num <= 2048) return "Jamul Pada";
        if (num >= 2049 && num <= 2068) return "Pankhanda";
        if (num >= 2069 && num <= 2074) return "Takarda";
        if (num >= 2075 && num <= 2080) return "Kaseli Pada";
        if (num >= 2081 && num <= 2088) return "Panchwad";
        if (num >= 2089 && num <= 2098) return "Pada (Devicha & Pachwad)";
        if (num >= 2099 && num <= 2103) return "Wamanli Pada";
        return "";
    }

    // Google Maps Embed URLs for each location
    let padaMaps = {
        "Vanicha Pada": "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3104.660316337373!2d72.9497975!3d19.247909399999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7bbd68f1657ab%3A0x3da29081bafa689b!2sVanicha%20pada!5e1!3m2!1sen!2sin!4v1741762937058!5m2!1sen!2sin", 

        "Devicha Pada": "https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1552.3754958837678!2d72.9548546!3d19.2431164!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7bbc7fa39c72d%3A0xfd22ed02dad2b31!2sGavit%20farm!5e1!3m2!1sen!2sin!4v1741763089945!5m2!1sen!2sin",

        "Jamul Pada": "https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3766.7995234095324!2d72.95504!3d19.247567000000004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMTnCsDE0JzUxLjIiTiA3MsKwNTcnMTguMSJF!5e0!3m2!1sen!2sin!4v1741765235002!5m2!1sen!2sin",

        "Pankhanda": "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1883.1209672731297!2d72.9465752!3d19.271842399999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7bbab310520e9%3A0x41214b12907f142b!2sPanKhanda%20TMC%20School!5e0!3m2!1sen!2sin!4v1741765394723!5m2!1sen!2sin",

        "Takarda": "https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6208.721908206025!2d72.953072!3d19.2637252!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7bb78e68b435d%3A0x3dcc6a88561d926f!2sTAKARDA%20GAON!5e1!3m2!1sen!2sin!4v1741775212926!5m2!1sen!2sin",

        "Kaseli Pada": "https://www.google.com/maps/embed?pb=!1m13!1m8!1m3!1d3104.0094293500633!2d72.9329297!3d19.2822809!3m2!1i1024!2i768!4f13.1!3m2!1m1!2s!5e1!3m2!1sen!2sin!4v1741775308197!5m2!1sen!2sin",

        "Panchwad": "https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d776.1355905666817!2d72.957647!3d19.2541426!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7bbb7f806a217%3A0x76763451ce968299!2sSBM%20Toilet!5e1!3m2!1sen!2sin!4v1741776033565!5m2!1sen!2sin",

        "Pada (Devicha & Pachwad)": "",

        "Wamanli Pada": ""

    };

 // Function to update map and city input based on selected number
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
