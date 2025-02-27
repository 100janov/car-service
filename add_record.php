<?php
session_start();
require_once('../config.php');

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Code for adding a new record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_record'])) {
    $make = sanitizeInput($_POST['make']);
    $model = sanitizeInput($_POST['model']);

    // Determine the selected plate type
    $plateType = $_POST['plate_type'];

    // Initialize registration_plate
    $registration_plate = '';

    // Check the plate type and get the correct registration plate field
    if ($plateType === 'macedonian') {
        $registration_plate_prefix_mk = strtoupper($_POST['registration_plate_prefix_mk']);
        $registration_plate_number_mk = strtoupper($_POST['registration_plate_number_mk']);
        $registration_plate_suffix_mk = strtoupper($_POST['registration_plate_suffix_mk']);
        // Concatenate the registration plate parts
        $registration_plate = $registration_plate_prefix_mk . '-' . $registration_plate_number_mk . '-' . $registration_plate_suffix_mk;

    } elseif ($plateType === 'foreign') {
        $registration_plate_fr = strtoupper($_POST['registration_plate_fr']);
        // Concatenate the registration plate parts
        $registration_plate = $registration_plate_fr;
    }

    $year = intval($_POST['year']);
    $engine = sanitizeInput($_POST['engine']);
    $owner_name = sanitizeInput($_POST['owner_name']);
    $mobile_phone = sanitizeInput($_POST['mobile_phone']);

    // Assume the record is not paid when initially added
    $isPaid = 0;

    // Initialize variables for work done
    $workDone = '';

    // Check if work_done is set in the post data
    if (isset($_POST['work_done'])) {
        // Combine the array of work_done
        $workDoneArray = $_POST['work_done'];

        // Loop through the array and concatenate the values
        foreach ($workDoneArray as $work) {
            $workDone .= $work . ', '; // Assuming you want to separate multiple works with a comma
        }

        // Remove the trailing comma and space from workDone
        $workDone = rtrim($workDone, ', ');
    }

    // Insert data into the cars table
    $stmt = $conn->prepare("INSERT INTO cars (make, model, registration_plate, year, engine, owner_name, mobile_phone, work_done, amount_due, is_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssissssdi", $make, $model, $registration_plate, $year, $engine, $owner_name, $mobile_phone, $workDone, $amountDue, $isPaid);

    // Check if the data is inserted into cars table successfully
    if ($stmt->execute()) {
        $carId = $stmt->insert_id; // Get the ID of the newly inserted record

        // Insert data into work_details table for each work_done and amount
        if (isset($_POST['work_done']) && isset($_POST['amount'])) {
            $workDoneArray = $_POST['work_done'];
            $amountArray = $_POST['amount'];

            // Loop through the arrays and insert into work_details table
            foreach ($workDoneArray as $index => $work) {
                $amountDue = floatval($amountArray[$index]) ?: 0; // Get the amount or 0 if not set

                $stmtWork = $conn->prepare("INSERT INTO work_details (car_id, work_done, amount_due) VALUES (?, ?, ?)");
                $stmtWork->bind_param("iss", $carId, $work, $amountDue);
                $stmtWork->execute();
                $stmtWork->close();
            }
        }

        $success_message = "Успешно внесовте ново возило.";

    } else {
        $error_message = "Error in executing statement: " . $stmt->error;
        $error_message .= "<br>Debugging info: " . $stmt->errno . " - " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0: charset=utf-8">

    <title>Внеси Ново Возило</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        $(document).ready(function () {
            // Define a mapping of makes to models
            var modelsByMake = {
    'Audi': ['A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7', 'Q8', 'e-Tron', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'RS3', 'RS4', 'RS5', 'RS6', 'RS7', '80', 'TT', 'R8'],
    'BMW': ['1 Серија', '2 Серија', '3 Серија', '4 Серија', '5 Серија', '6 Серија', '7 Серија', '8 Серија', 'X1', 'X2', 'X3', 'X4', 'X5', 'X6', 'X7', 'XM', 'Z4', 'i3', 'i8', 'M2', 'M3', 'M4', 'M5', 'M8'],
    'Mercedes-Benz': ['A-Class', 'B-Class', 'C-Class', 'E-Class', 'S-Class', 'CLA', 'CLS', 'GLA', 'GLC', 'GLB', 'GLE', 'GLS', 'G-Class', 'SLS AMG', 'GT', 'EQC'],
    'Toyota': ['Camry', 'Corolla', 'Corolla Cross', 'Avalon', 'Prius', 'Yaris', 'Rav4', 'Highlander', '4Runner', 'Land Cruiser', 'C-HR', 'Tacoma', 'Tundra', 'Supra', 'Coaster', 'Fortuner'],
    'Ford': ['Fiesta', 'Focus', 'Mustang', 'Mondeo', 'Kuga', 'Puma', 'Escape', 'Edge', 'Explorer', 'Expedition', 'F-150', 'F-250', 'F-350', 'Ranger', 'Bronco', 'Mach-E', 'Transit', "Maverick"],
    'Honda': ['Accord', 'Civic', 'Civic Tourer', 'Jazz', 'Fit', 'HR-V', 'CR-V', 'Passport', 'Pilot', 'Ridgeline', 'Odyssey', 'Insight', 'Clarity', 'City', 'Prelude', 'Amaze', 'Elevate', 'Crosstour'],
    'Citroen': ['C1', 'C2', 'C3', 'C3 Aircross', 'C3 Picasso', 'C4', 'C4 Cactus', 'C4 Aircross', 'C4 Picasso', 'C5', 'C5 Aircross', 'C5 X', 'C6', 'Berlingo', 'Spacetourer', 'DS3', 'DS4', 'DS5'],
    'Chevrolet': ['Spark', 'Sonic', 'Malibu', 'Camaro', 'Corvette', 'Trax', 'Equinox', 'Blazer', 'Trailblazer', 'Traverse', 'Tahoe', 'Suburban', 'Colorado', 'Silverado', 'Captiva', 'Groove', 'Tracker', 'Montana'],
    'Nissan': ['Micra', 'Versa', 'Juke', 'Sentra', 'Qashqai', 'X-Trail', 'Altima', 'Maxima', '370Z', 'GT-R', 'Kicks', 'Rogue', 'Murano', 'Pathfinder', 'Armada', 'Frontier', 'Titan', 'Leaf', 'Note', 'Tiida', 'Skyline'],
    'Hyundai': ['i10', 'i20', 'i20 N', 'i30', 'i30 N', 'i30 Fastback', 'i30 Wagon', 'i30 N Fastback', 'i40', 'Accent', 'Elantra', 'Sonata', 'Veloster', 'Kona', 'Tucson', 'Bayon', 'Santa Fe', 'Palisade', 'Nexo', 'Venue', 'Ioniq', 'Alcazar', 'Creta', 'Aura'],
    'Volvo': ['S60', 'S80', 'S90', 'V40', 'V50', 'V60', 'V90', 'XC40', 'XC60', 'XC90', 'XC40 Recharge', 'XC60 Recharge', 'XC90 Recharge', '66', 'C30', 'C40', 'C70', '140', '480', '850'],
    'Mazda': ['2', '3', '3 Fastback', '6', 'CX', 'CX-3', 'CX-30', 'CX-4', 'CX-5', 'CX-8', 'CX-9', 'MX-30', 'MX-5 Miata', 'Miata', 'Tribute', '121', 'Nagare', 'Ibuki'],
    'Subaru': ['Impreza', 'Legacy', 'WRX', 'BRZ', 'Crosstrek', 'Forester', 'Outback', 'Ascent'],
    'Porsche': ['911', '718 Cayman', 'Cayman', 'Boxster', 'Panamera', 'Macan', 'Cayenne', 'Taycan', 'Cross Turismo', 'Carrera'],
    'Daewoo': ['Nexia', 'Cielo', 'Espero', 'Lanos', 'Leganza', 'Matiz', 'Nubira', 'Tacuma', 'Lublin', 'Musso', 'Arcadia', 'Chairman', 'Korando', 'Magnus', 'Tico', 'Tosca', 'Spark'],
    'Jaguar': ['XE', 'XF', 'XJ', 'XK', 'F-PACE', 'E-PACE', 'I-PACE', 'X-TYPE', 'F-TYPE', 'XJ220'],
    'Land Rover': ['Range Rover', 'Range Rover Sport', 'Range Rover Velar', 'Discovery', 'Discovery Sport', 'Defender'],
    'Kia': ['Rio', 'Forte', 'Ceed', 'ProCeed', 'Xceed', 'Optima', 'Stinger', 'K900', 'Soul', 'Niro', 'Sportage', 'Seltos', 'Telluride', 'Cadenza', 'Sorento', 'Carens', 'Picanto', 'Mohave', 'Stonic', 'Sonet', 'Cerato', 'EV6'],
    'Ferrari': ['F8 Tributo', '488 GTB', '812 Superfast', 'Roma', 'Portofino', 'SF90 Stradale'],
    'Lancia': ['Ypsilon', 'Delta', 'Thesis', 'Musa', 'Phedra', 'Voyager', 'Prisma', 'Dedra', 'Lybra'],
    'Maserati': ['Ghibli', 'Quattroporte', 'Levante', 'GranTurismo', 'Grecale', 'Alfieri', '420', 'Karif', 'MC20'],
    'Bentley': ['Bentayga', 'Continental GT', 'Flying Spur'],
    'Aston Martin': ['Vantage', 'DB11', 'DBS Superleggera', 'Rapide AMR'],
    'Lexus': ['IS', 'ES', 'LS', 'LC', 'UX', 'NX', 'RX', 'GS', 'GX', 'CT', 'RZ', 'LX', 'RC', 'LC Convertible', 'Lexus UX 300e'],
    'Jeep': ['Cherokee', 'Compass', 'Grand Cherokee', 'Renegade', 'Wrangler', 'Gladiator', 'Wagoneer', 'Avenger', 'Meridian', 'Commander', 'Liberty', 'Hurricane', 'Patriot'],
    'Chrysler': ['200', '300', 'Aspen', 'Voyager', 'Pacifica', 'Crossfire', 'Daytona', 'Galant'],
    'Dodge': ['Charger', 'Challenger', 'Durango', 'Journey'],
    'Fiat': ['500', '500X', '500L', 'Bravo', 'Tipo', 'Punto', 'Panda', 'Linea', 'Doblo'],
    'Alfa Romeo': ['Giulia', 'Giulietta', 'Stelvio', 'Mito', '147', '156', '159', '166', 'Tonale', 'Brera', 'GT', 'GTV'],
    'Mitsubishi': ['Mirage', 'Outlander', 'Eclipse', 'Eclipse Cross', 'Space Star', 'Colt', 'Colt Plus', 'Lancer', 'Grand Lancer', 'ASX', 'Pajero', 'XForce', 'L200'],
    'Suzuki': ['Swift', 'Vitara', 'Jimny', 'Ignis', 'Baleno', 'Celerio', 'Alto', 'Ertiga', 'Grand Vitara', 'Ciaz', 'SX4', 'Across', 'S-Cross'],
    'Seat': ['Ibiza', 'Leon', 'Ateca', 'Tarraco', 'Arona', 'Alhambra', 'Mii Electric', 'Leon Cupra', 'Altea', 'Arosa', 'Cordoba', 'Exeo', 'Toledo', 'Ronda', 'Marbella', '128', 'Fura', 'Cupra Leon', 'Bocanegra'],
    'Mini': ['Cooper', 'Clubman', 'Countryman', 'Hatch', 'Convertible', 'John Cooper Works', 'SE', 'Classic', 'Morris'],
    'Infiniti': ['Q50', 'Q60', 'QX50', 'QX60', 'QX80', 'Q30', 'QX30'],
    'Genesis': ['G70', 'G80', 'G90'],
    'Cadillac': ['CT4', 'CT5', 'CT6', 'XT4', 'XT5', 'XT6', 'Escalade'],
    'Maybach': ['S-Class', 'GLS'],
    'Abarth': ['595', '695'],
    'Lada': ['Granta', 'Vesta', 'XRAY', 'Niva', 'Samara'],
    'Haval': ['H1', 'H2', 'H6', 'H9'],
    'Great Wall': ['Haval', 'Wey', 'Ora'],
    'SsangYong': ['Tivoli', 'Korando', 'Rexton', 'Actyon', 'Musso'],
    'Mahindra': ['XUV300', 'Thar', 'Scorpio', 'XUV500', 'Alturas G4'],
    'Daihatsu': ['Sirion', 'Ayla', 'Sigra', 'Terios', 'Gran Max', 'Mira', 'Tanto', 'Hijet', 'Boon', 'Cast'],
    'Isuzu': ['D-Max', 'MU-X'],
    'Cupra': ['Formentor', 'Leon', 'Ateca', 'Ateca VZ5', 'Born'],
    'Dacia': ['Sandero', 'Sandero Stepway', 'Logan', 'Logan Stepway', 'Duster', 'Spring', 'Jogger', 'Solenza'],
    'Land Rover': ['Range Rover Evoque', 'Range Rover Velar', 'Discovery Sport', 'Defender', 'Discovery', 'Range Rover'],
    'Rover': ['200', '25', '400', '45', '600', '75', 'Streetwise', 'CityRover'],
    'Lotus': ['Elise', 'Exige', 'Evora', 'Evija'],
    'McLaren': ['540C', '570S', '600LT', '620R', '720S', '765LT', 'GT', 'Sabre'],
    'Opel': ['Corsa', 'Agila', 'Astra', 'Ascona', 'Insignia', 'Meriva', 'Calibra', 'Adam', 'Omega', 'Frontera', 'Astra Sports Tourer', 'Grandland', 'Grandland X', 'Crossland', 'Combo', 'Combo Life', 'Movano', 'Vivaro', 'Vivaro Life', 'Zafira', 'Zafira-e Life', 'Mokka', 'Mokka-e', 'Vectra'],
    'Peugeot': ['106', '107', '108', '206', '207', '208', '306', '307', '308', '408', '208 GTI', '208 Electric', '2008', '2008 Electric', '308 SW', '308 GT', '308 GTi', '3008', '3008 Hybrid', '3008 GT', '508', '508 SW', '508 PSE', '508 GT', '5008', '5008 PSE', '5008 GT', 'Rifter', 'Traveller', 'Expert', 'Partner'],
    'Renault': ['Twingo', 'Clio', 'Clio Estate', 'Clio RS', 'Captur', 'Megane', 'Megane Hatchback', 'Megane Estate', 'Megane R.S.', 'Scenic', 'Talisman', 'Espace', 'Kadjar', 'Arkana', 'Austral', 'Koleos', 'Kangoo', 'Trafic', 'Master', 'Zoe', 'Kiger', 'Fluence', 'Laguna', '7', '12', '14'],
    'Saab': ['9-3', '9-5', '900', '9000', 'Monster', '93', '600', '99'],
    'Skoda': ['Citigo', 'Fabia', 'Scala', 'Octavia', 'Slavia', 'Rapid', 'Octavia Estate', 'Superb', 'Superb Estate', 'Kamiq', 'Karoq', 'Kodiaq', 'Kushaq', 'Enyaq', 'Enyaq Coupe', 'Yeti'],
    'Smart': ['Fortwo', 'Forfour', 'Fortwo Cabrio', 'City Coupe'],
    'SsangYong': ['Tivoli', 'Korando', 'Korando Sports', 'Rexton', 'Musso'],
    'Subaru': ['Impreza', 'Impreza Sport', 'XV', 'Forester', 'Outback', 'Levorg', 'BRZ', 'WRX STI'],
    'Toyota': ['Aygo', 'Yaris', 'Yaris Cross', 'Corolla Hatchback', 'Corolla Touring Sports', 'Camry', 'Prius', 'Prius Plug-in Hybrid', 'Mirai', 'GT86', 'RAV4', 'Highlander', 'C-HR', 'Land Cruiser', 'Land Cruiser Utility', 'Hilux', 'Proace', 'Proace Verso', 'Proace City', 'GR Supra'],
    'Volkswagen': ['Up!', 'Polo', 'Golf', 'Golf Estate', 'Golf GTI', 'Golf GTD', 'Golf GTE', 'Golf R', 'ID.3', 'ID.4', 'e-up!', 'e-Golf', 'Arteon', 'Arteon Shooting Brake', 'Passat', 'Passat Estate', 'Passat GTE', 'CC', 'T-Cross', 'T-Roc', 'T-Roc Cabriolet', 'Tiguan', 'Tiguan Allspace', 'Touran', 'Sharan', 'Touareg', 'Caddy', 'Caddy Life', 'Caddy Cargo', 'Caravelle', 'Transporter', 'Grand California', 'Jetta', 'Atlas', 'Beetle', 'Amarok', 'EOS'],
    'Yugo': ['Yugo 45', 'Yugo 55', 'Yugo Koral', 'Yugo Cabrio', 'Yugo Florida', 'Yugo Sana', 'Yugo Tempo', 'Yugo Cabriolet', 'Yugo Florida In', 'Yugo Koral In', 'Yugo Sana In'],
    'Zastava': ['Zastava 750', 'Zastava 850', 'Zastava 101', 'Zastava Skala', 'Zastava Yugo', 'Zastava Koral', 'Zastava Florida', 'Zastava 10', 'Zastava 101 FL', 'Zastava 102', 'Zastava 103', 'Zastava 104', 'Zastava 105', 'Zastava 1100', 'Zastava 1200', 'Zastava 1300', 'Zastava 1400', 'Zastava 1500', 'Zastava 101 Special', 'Zastava 750S', 'Zastava 850S', 'Zastava Skala 55', 'Zastava Skala 65', 'Zastava Skala 55', 'Zastava Skala 65', 'Zastava 101A', 'Zastava Yugo 45', 'Zastava Yugo 55', 'Zastava Yugo 65', 'Zastava Yugo Tempo', 'Zastava Yugo Florida', 'Zastava Yugo Koral', 'Zastava Yugo Skala', 'Zastava Yugo Skala Plus', 'Zastava Yugo Cabrio', 'Zastava Yugo Sana', 'Zastava Yugo Sana In', 'Zastava Yugo Florida In', 'Zastava Yugo Koral In', 'Zastava Yugo Cabriolet', 'Zastava Z10', 'Zastava Z101', 'Zastava Z102', 'Zastava Z103', 'Zastava Z104', 'Zastava Z105', 'Zastava Z1100', 'Zastava Z1200', 'Zastava Z1300', 'Zastava Z1400', 'Zastava Z1500', 'Zastava Z101 Special', 'Zastava Z750S', 'Zastava Z850S', 'Zastava 750C', 'Zastava 850C', 'Zastava Skala 55C', 'Zastava Skala 65C'],
    // Add more makes and models as needed
};

            // Function to update models dropdown based on selected make
            $('#make').change(function () {
                var selectedMake = $(this).val();
                var models = modelsByMake[selectedMake] || [];

                // Update models dropdown
                var modelsDropdown = $('#model');
                modelsDropdown.empty();

                // Add default option
                modelsDropdown.append($('<option>', { value: '', text: 'Избери Модел' }));

                // Add options for each model
                $.each(models, function (i, model) {
                    modelsDropdown.append($('<option>', { value: model, text: model }));
                });
            });
        });
    </script>
    <style>
        body {
            padding-top: 0px;
        }

        @media (max-width: 576px) {
            body {
                padding-top: 0;
            }
        }

        .container {
            max-width: 100%;
            padding-right: 40px;
            padding-left: 250px;
            margin-right: auto;
            margin-left: auto;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .btn-block {
            width: 100%;
        }

        .mt-md-0 {
            margin-top: 0 !important;
        }

        .mb-md-3 {
            margin-bottom: 3rem !important;
        }

        .mt-md-3 {
            margin-top: 3rem !important;
        }
        
        .inline-input {
    display: inline-block;
    margin-right: 8px; /* Adjust spacing between input fields */
}

    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Внеси Ново Возило</h2>

    <!-- Form to add new car workshop record -->
    <form action="" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="make">Производител:</label>
            <!-- List of car manufacturers -->
            <select class="form-control" id="make" name="make" required>
                <!-- List of car manufacturers in alphabetical order -->
                <option value="" disabled selected hidden>Избери Производител</option>
                <option value="Alfa Romeo">Alfa Romeo</option>
                <option value="Aston Martin">Aston Martin</option>
                <option value="Audi">Audi</option>
                <option value="Bentley">Bentley</option>
                <option value="BMW">BMW</option>
                <option value="Cadillac">Cadillac</option>
                <option value="Citroen">Citroen</option>
                <option value="Chevrolet">Chevrolet</option>
                <option value="Chrysler">Chrysler</option>
                <option value="Cupra">Cupra</option>
                <option value="Dacia">Dacia</option>
                <option value="Daewoo">Daewoo</option>
                <option value="Daihatsu">Daihatsu</option>
                <option value="Dodge">Dodge</option>
                <option value="Ferrari">Ferrari</option>
                <option value="Fiat">Fiat</option>
                <option value="Ford">Ford</option>
		        <option value="Genesis">Genesis</option>
                <option value="Great Wall">Great Wall</option>
	        	<option value="Haval">Haval</option>
                <option value="Honda">Honda</option>
                <option value="Hyundai">Hyundai</option>
                <option value="Infiniti">Infiniti</option>
                <option value="Isuzu">Isuzu</option>
                <option value="Jaguar">Jaguar</option>
                <option value="Jeep">Jeep</option>
                <option value="Kia">Kia</option>
		        <option value="Lada">Lada</option>
                <option value="Lamborghini">Lamborghini</option>
                <option value="Land Rover">Land Rover</option>
                <option value="Lancia">Lancia</option>
                <option value="Lexus">Lexus</option>
                <option value="Lincoln">Lincoln</option>
                <option value="Lotus">Lotus</option>
                <option value="Maserati">Maserati</option>
                <option value="Mazda">Mazda</option>
                <option value="Mercedes-Benz">Mercedes-Benz</option>
                <option value="Mitsubishi">Mitsubishi</option>
		        <option value="Mini">Mini</option>
                <option value="Nissan">Nissan</option>
                <option value="Opel">Opel</option>
                <option value="Peugeot">Peugeot</option>
                <option value="Porsche">Porsche</option>
                <option value="Ram">Ram</option>
                <option value="Renault">Renault</option>
                <option value="Rolls-Royce">Rolls-Royce</option>
                <option value="Rover">Rover</option>
                <option value="Saab">Saab</option>
                <option value="Seat">Seat</option>
                <option value="Skoda">Skoda</option>
                <option value="Smart">Smart</option>
                <option value="SsangYong">SsangYong</option>
                <option value="Subaru">Subaru</option>
                <option value="Suzuki">Suzuki</option>
                <option value="Toyota">Toyota</option>
                <option value="Volkswagen">Volkswagen</option>
                <option value="Volvo">Volvo</option>
                <option value="Yugo">Yugo</option>
                <option value="Zastava">Zastava</option>
            </select>
        </div>
        <div class="form-group">
            <label for="model">Модел:</label>
            <select class="form-control" id="model" name="model" required>
                <!-- Initial placeholder option -->
                <option style="display:none">Избери Модел</option>
            </select>
        </div>
        <div class="form-group">
        <label for="plate_type">Тип на Табличка:</label>
        <!-- Dropdown field for plate type -->
        <select class="form-control" id="plate_type" name="plate_type" required>
            <option value="macedonian">Македонска Табличка</option>
            <option value="foreign">Странска Табличка</option>
        </select>
        </div>
        <div class="form-group" id="macedonian_plate_fields">
            <label for="plate_prefix">Регистарска Табличка:</label>
            <br>
            <input type="text" class="form-control inline-input" id="registration_plate_prefix_mk" name="registration_plate_prefix_mk" style="text-transform: uppercase; width: 5.8%;" maxlength="2" onkeydown="return /[a-zA-Z]/i.test(event.key)" required>
            <label style="margin-right: 8px">-</label>
            <input type="text" class="form-control inline-input" id="registration_plate_number_mk" name="registration_plate_number_mk" style="text-transform: uppercase; width: 6.7%;" maxlength="4" onkeydown="return /[0-9]/i.test(event.key)" required>
            <label style="margin-right: 10px">-</label>
            <input type="text" class="form-control inline-input" id="registration_plate_suffix_mk" name="registration_plate_suffix_mk" style="text-transform: uppercase; width: 5.8%;" maxlength="2" onkeydown="return /[a-zA-Z]/i.test(event.key)" required>
        </div>
        
        <div class="form-group" id="foreign_plate_fields" style="display: none;">
            <label for="plate_prefix">Регистарска Табличка:</label>
            <br>
            <input type="text" class="form-control inline-input" id="registration_plate_fr" name="registration_plate_fr" style="text-transform: uppercase;" maxlength="15">
            
        </div>
        <div class="form-group">
            <label for="year">Година:</label>
            <input type="number" class="form-control" id="year" name="year" maxlength="4" required>
        </div>
        <div class="form-group">
            <label for="engine">Мотор:</label>
            <input type="text" class="form-control" id="engine" name="engine" required>
        </div>
        <div class="form-group">
            <label for="owner_name">Име на сопственик:</label>
            <input type="text" class="form-control" id="owner_name" name="owner_name" required>
        </div>
        <div class="form-group">
            <label for="mobile_phone">Телефон:</label>
            <input type="number" class="form-control" id="mobile_phone" name="mobile_phone" maxlength="9" required>
        </div>

        <!-- Remove the plus button and additional fields -->

        <!-- Remove the "Total Amount Due" field -->

        <!-- Submit Button -->
        <button type="submit" name="submit_record" class="btn btn-secondary btn-lg active" >Потврди</button>
    </form>

    <!-- Display success or error messages -->
    <?php
    if (isset($success_message)) {
    echo "<div id='successModal' class='modal' tabindex='-1' role='dialog'>
            <div class='modal-dialog' role='document'>
              <div class='modal-content'>
                
                <div class='modal-body'>
                  <p>$success_message</p>
                </div>
              <div class='modal-footer justify-content-center'>
                <a id='redirectLink' class='btn btn-primary' href='add_record.php'>OK</a>
            </div>
              </div>
            </div>
          </div>";

    // JavaScript to show the modal and redirect when the "OK" button is clicked
    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                $('#successModal').modal('show');

                document.getElementById('redirectLink').addEventListener('click', function(event) {
                    event.preventDefault();
                    window.location.href = this.getAttribute('href');
                });
            });
          </script>";
} elseif (isset($error_message)) {
    // Display any error message with the default alert styles
    echo "<div class='alert alert-danger mt-3' role='alert'>$error_message</div>";
}
    ?>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        // Function to toggle visibility of input fields based on selected option
        $('#plate_type').change(function () {
            var selectedOption = $(this).val();
            if (selectedOption === 'macedonian') {
                $('#macedonian_plate_fields').show();
                $('#foreign_plate_fields').hide();
            } else if (selectedOption === 'foreign') {
                $('#macedonian_plate_fields').hide();
                $('#foreign_plate_fields').show();
            }
        });
    });
</script>

<script>
    function validateForm() {
        var makeSelect = document.getElementById('make');
        var selectedMake = makeSelect.options[makeSelect.selectedIndex].value;
        if (selectedMake === '') {
            alert('Please select a car manufacturer.');
            return false; // Prevent form submission
        }
        return true; // Allow form submission
    }
</script>

<script>
    document.getElementById('plate_type').addEventListener('change', function() {
        var selectedPlateType = this.value;
        if (selectedPlateType === 'macedonian') {
            document.getElementById('macedonian_plate_fields').style.display = 'block';
            document.getElementById('foreign_plate_fields').style.display = 'none';

            // Set required attribute for Macedonian fields
            document.getElementById('registration_plate_prefix_mk').required = true;
            document.getElementById('registration_plate_number_mk').required = true;
            document.getElementById('registration_plate_suffix_mk').required = true;

            // Remove required attribute for Foreign fields
            document.getElementById('registration_plate_fr').removeAttribute('required');
        } else if (selectedPlateType === 'foreign') {
            document.getElementById('macedonian_plate_fields').style.display = 'none';
            document.getElementById('foreign_plate_fields').style.display = 'block';

            // Set required attribute for Foreign fields
            document.getElementById('registration_plate_fr').required = true;

            // Remove required attribute for Macedonian fields
            document.getElementById('registration_plate_prefix_mk').removeAttribute('required');
            document.getElementById('registration_plate_number_mk').removeAttribute('required');
            document.getElementById('registration_plate_suffix_mk').removeAttribute('required');
        }
    });
</script>

</body>
</html>
