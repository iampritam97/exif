<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="View and analyze Exif data of images. Upload an image or provide an image URL to explore image metadata.">
    <title>Exif Data Viewer - View and Analyze Image Metadata</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }

        .jumbotron {
            background-color: #f8f8f8;
            color: #000;
            text-align: center;
            padding: 20px;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .exif-data {
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 5px;
            margin-top: 20px;
        }

        .exif-section {
            margin-bottom: 15px;
        }

        .exif-section h4 {
            color: #3498db;
        }

        .btn-primary {
            background-color: #000;
            color: #fff;
            border-color: #000;
            padding: 10px 20px;
            font-size: 12px;
            border-radius: 5px;
            text-transform: uppercase;
        }
        
        .btn-primary:hover {
            background-color: #27ae60;
            border-color: #219d58;
            color: #fff;
        }

    </style>
</head>
<body>

<div class="jumbotron">
<h1>Exif Data Viewer</h1>
<h3>Explore and Analyze Image Metadata</h3>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3 form-container">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $imageFile = isset($_FILES['image']) ? $_FILES['image'] : null;
                $imageUrl = isset($_POST['image_url']) ? $_POST['image_url'] : null;

                // Check if an image file is uploaded
                if (!empty($imageFile['tmp_name'])) {
                    // Verify file type using getimagesize
                    $imageInfo = getimagesize($imageFile['tmp_name']);
                    if ($imageInfo === false) {
                        echo '<p class="text-danger">Invalid image file.</p>';
                        $fileType = false;
                    } else {
                        $fileType = $imageInfo[2];
                        $imageData = file_get_contents($imageFile['tmp_name']);
                    }
                } elseif (!empty($imageUrl)) {
                    // Check if the image source is a valid URL
                    if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        echo '<p class="text-danger">Invalid URL format.</p>';
                        $fileType = false;
                    } else {
                        $imageData = file_get_contents($imageUrl);
                        // Check if the file is an image
                        $fileType = exif_imagetype("data://image/jpeg;base64," . base64_encode($imageData));
                    }
                } else {
                    echo '<p class="text-danger">Please provide either an image file or URL.</p>';
                    $fileType = false;
                }

                if ($fileType !== false) {
                    $exifData = exif_read_data("data://image/jpeg;base64," . base64_encode($imageData));
                
                    if ($exifData !== false) {
                        // Array of keys you want to display
                        $CameraToDisplay = array(
                            'Make',
                            'Model',
                            'FirmwareVersion',
                            'FocalLength',
                            'ApertureFNumber',
                            'ExposureTime',
                            'ISO',
                            // Add more keys as needed
                        );

                        $ImageToDisplay = array(
                            'OwnerName',
                            'ImageDescription',
                            'MimeType',
                            'FileSize',
                            'ExifImageWidth',
                            'ExifImageLength',
                            'DateTimeOriginal',
                            //'GPSLongitude',
                            //'GPSLatitude',
                            // Add more keys as needed
                        );

                        $OtherToDisplay = array(
                            'Software',
                            'FocalLength',
                            'UserComment',
                            'ExposureTime',
                            'XResolution',
                            'YResolution',
                            'ISO',
                            // Add more keys as needed
                        );
                
                        echo '<div class="exif-data">';
                        echo '<center><h4>Camera Settings</h4></center>';
                        echo '<table class="table table-bordered">';
                        echo '<thead><tr><th>Property</th><th>Value</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($CameraToDisplay as $key) {
                            if (array_key_exists($key, $exifData)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($key) . '</td>';
                                // Check if the value is an array before using htmlspecialchars
                                $value = $exifData[$key];
                                if (is_array($value)) {
                                    echo '<td><pre>' . htmlspecialchars(print_r($value, true)) . '</pre></td>';
                                } else {
                                    echo '<td>' . htmlspecialchars($value) . '</td>';
                                }
                
                                echo '</tr>';
                            }
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';

                        echo '<div class="exif-data">';
                        echo '<center><h4>Image Settings</h4></center>';
                        echo '<table class="table table-bordered">';
                        echo '<thead><tr><th>Property</th><th>Value</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($ImageToDisplay as $key) {
                            if (array_key_exists($key, $exifData)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($key) . '</td>';
                                // Check if the value is an array before using htmlspecialchars
                                $value = $exifData[$key];
                                if (is_array($value)) {
                                    echo '<td><pre>' . htmlspecialchars(print_r($value, true)) . '</pre></td>';
                                    }
                                    else {
                                        echo '<td>' . htmlspecialchars($value) . '</td>';
                                } 
                                echo '</tr>';
                            }
                    }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';

                        echo '<div class="exif-data">';
                        echo '<center><h4>Other Metadata</h4></center>';
                        echo '<table class="table table-bordered">';
                        echo '<thead><tr><th>Property</th><th>Value</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($OtherToDisplay as $key) {
                            if (array_key_exists($key, $exifData)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($key) . '</td>';
                                // Check if the value is an array before using htmlspecialchars
                                $value = $exifData[$key];
                                if (is_array($value)) {
                                    echo '<td><pre>' . htmlspecialchars(print_r($value, true)) . '</pre></td>';
                                } else {
                                    echo '<td>' . htmlspecialchars($value) . '</td>';
                                }
                
                                echo '</tr>';
                            }
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    } else {
                        echo '<p class="text-danger">Error reading Exif data.</p>';
                    }
                } else {
                    echo '<p class="text-danger">Provided input does not contain a valid image.</p>';
                }
            }
                
            ?>

            <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
                <div class="form-group">
                    <label for="image">Upload an image:</label>
                    <input type="file" name="image" id="image" class="form-control">
                    <p>Upload an image (max size: 10 MB)</p>
                </div>
                <div class="form-group">
                    <label for="image_url">OR Enter URL of the image:</label>
                    <input type="url" name="image_url" id="image_url" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">View Exif Data</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>
