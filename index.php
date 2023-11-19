<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="View and analyze Exif data of images. Upload an image or provide an image URL to explore image metadata.">
    <title>Exif Data Viewer - View and Analyze Image Metadata</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        body {
            font-family: "Inter", sans-serif;
            background-color: #fff;
            color: #343a40;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .jumbotron {
            background-color: #fff;
            color: #000;
            text-align: center;
            padding: 20px;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            border: 2px solid #000;
        }

        .exif-data {
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 10px;
            margin-top: 20px;
        }

        .exif-section {
            margin-bottom: 15px;
        }

        .exif-section h4 {
            color: #3498db;
        }

        .btn-primary {
            padding: 10px 20px;
            font-weight: 500;
            border: 2px solid #000;
            border-radius: 5px;
            background-color: #fff;
            color: #000;
        }

        .btn-primary:hover {
            background-color: #000;
            border-color: #000;
            color: #fff;
        }

        #dropArea {
            min-height: 150px;
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            background-color: #f8f8f8;
            border-radius: 10px;
        }

        #dropArea.highlight {
            border-color: #009688;
            background-color: #e9e9e9;
        }

        #previewArea {
            margin-top: 20px;
        }

        #previewArea img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        footer {
            background-color: #fff;
            color: #000;
            padding: 30px 0;
            text-align: center;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var dropArea = document.getElementById('dropArea');
            var fileInput = document.getElementById('image');

            dropArea.addEventListener('click', function () {
                fileInput.click();
            });

            dropArea.addEventListener('dragenter', function (event) {
                event.preventDefault();
                dropArea.classList.add('highlight');
            });

            dropArea.addEventListener('dragover', function (event) {
                event.preventDefault();
            });

            dropArea.addEventListener('dragleave', function () {
                dropArea.classList.remove('highlight');
            });

            dropArea.addEventListener('drop', function (event) {
                event.preventDefault();
                dropArea.classList.remove('highlight');

                var files = event.dataTransfer.files;
                handleFiles(files);
            });

            fileInput.addEventListener('change', function () {
                var files = fileInput.files;
                handleFiles(files);
            });

            function handleFiles(files) {
                if (files.length > 0) {
                    var file = files[0];

                    if (file.type.startsWith('image/') || file.type.startsWith('application')) {
                        var reader = new FileReader();

                        reader.onload = function (e) {
                            var img = document.createElement('img');
                            img.src = e.target.result;

                            var previewArea = document.getElementById('previewArea');
                            previewArea.innerHTML = '';
                            previewArea.appendChild(img);
                        };

                        reader.readAsDataURL(file);
                    } else {
                        alert('Please select an image file.');
                    }
                }
            }
        });
    </script>
</head>
<body>

<div class="jumbotron">
    <h1>Exif Data Viewer</h1>
    <p class="lead">Explore and Analyze Image Metadata</p>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-3 form-container">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $imageFile = isset($_FILES['image']) ? $_FILES['image'] : null;
                $imageUrl = isset($_POST['image_url']) ? $_POST['image_url'] : null;

                if (!empty($imageFile['tmp_name'])) {
                    $imageData = file_get_contents($imageFile['tmp_name']);
                } elseif (!empty($imageUrl)) {
                    if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        echo '<p class="text-danger">Invalid URL format.</p>';
                        $fileType = false;
                    } else {
                        $imageData = file_get_contents($imageUrl);
                    }
                } else {
                    echo '<p class="text-danger">Please provide either an image file or URL.</p>';
                    $fileType = false;
                }

                try {
                    $imagick = new \Imagick();
                    $imagick->readImageBlob($imageData);
                    $exifData = $imagick->getImageProperties();
                    
                    $ImagePropertyToDisplay = array(
                        'exif:Make',
                        'exif:Model',
                        'exif:DateTimeOriginal',
                        'exif:DateTimeDigitized',
                        'exif:Software',
                        'xmp:CreatorTool',
                        'exif:FocalLength',
                        'exif:ExposureTime',
                        'exif:ShutterSpeedValue',
                        'exif:XResolution',
                        'exif:YResolution',
                        'exif:thumbnail:XResolution',
                        'exif:thumbnail:YResolution',
                        'exif:DigitalZoomRatio',
                        'exif:ApertureValue',
                        'exif:GPSLatitude',
                        'exif:GPSLatitudeRef',
                        'exif:GPSLongitude',
                        'exif:GPSLongitudeRef',
                    );

                    echo '<div class="exif-data">';
                    echo '<h4 class="text-center mb-4">Image Properties</h4>';
                    echo '<table class="table table-bordered">';
                    echo '<thead class="thead-light"><tr><th>Property</th><th>Value</th></tr></thead>';
                    echo '<tbody>';
                    foreach ($ImagePropertyToDisplay as $key) {
                        if (array_key_exists($key, $exifData)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($key) . '</td>';
                            echo '<td>' . htmlspecialchars($exifData[$key]) . '</td>';
                            echo '</tr>';
                        }
                    }
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';

                    $imagick->clear();
                    $imagick->destroy();
                } catch (\Exception $e) {
                    echo '<p class="text-danger">Error reading Exif data: ' . $e->getMessage() . '</p>';
                }
            }
            ?>

            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
                <div class="form-group">
                    <label for="image">Upload an image:</label>
                    <div id="dropArea" class="form-control">
                        <p class="mb-0">Click or drag and drop an image here</p>
                    </div>
                    <input type="file" name="image" id="image" style="display: none;">
                    <small class="form-text text-muted">Upload an image (max size: 10 MB)</small>
                </div>
                <div class="form-group">
                    <label for="image_url">OR Enter URL of the image:</label>
                    <input type="url" name="image_url" id="image_url" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary btn-block">View Exif Data</button>
            </form>

            <div id="previewArea"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<footer><div class="container">Made with <a href="https://www.php.net/manual/en/book.imagick.php">Imagick</a></div></footer>
</body>
</html>
