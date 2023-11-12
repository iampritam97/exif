<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exif Data Viewer</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f8f8;
        }

        .container {
            margin-top: 50px;
        }

        .jumbotron {
            background-color: #3498db;
            color: #fff;
            text-align: center;
            padding: 20px;
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
    </style>
</head>
<body>

<div class="container">
    <div class="jumbotron">
        <h1>Exif Data Viewer</h1>
    </div>

    <div class="row">
        <div class="col-md-6 col-md-offset-3 form-container">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $imageFile = isset($_FILES['image']) ? $_FILES['image'] : null;
                $imageUrl = isset($_POST['image_url']) ? $_POST['image_url'] : null;

                // Check if an image file is uploaded
                if (!empty($imageFile['tmp_name'])) {
                    $fileType = exif_imagetype($imageFile['tmp_name']);
                    $imageData = file_get_contents($imageFile['tmp_name']);
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
                        echo '<div class="exif-data">';
                        foreach ($exifData as $key => $value) {
                            echo '<div class="exif-section">';
                            echo '<h4>' . htmlspecialchars($key) . '</h4>';
                            echo '<pre>' . htmlspecialchars(print_r($value, true)) . '</pre>';
                            echo '</div>';
                        }
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
                <div class="form-group">
                    <label for="image">Upload an image:</label>
                    <input type="file" name="image" id="image" class="form-control">
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
