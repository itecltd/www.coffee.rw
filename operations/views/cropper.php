<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Image Cropper | Notika - Notika Admin Template</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- favicon
		============================================ -->
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <!-- Google Fonts
		============================================ -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900" rel="stylesheet">
    <!-- Bootstrap CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <!-- font awesome CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/font-awesome.min.css">
    <!-- owl.carousel CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/owl.carousel.css">
    <link rel="stylesheet" href="../../css/owl.theme.css">
    <link rel="stylesheet" href="../../css/owl.transitions.css">
    <!-- meanmenu CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/meanmenu/meanmenu.min.css">
    <!-- animate CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/animate.css">
    <!-- normalize CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/normalize.css">
    <!-- mCustomScrollbar CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/scrollbar/jquery.mCustomScrollbar.min.css">
    <!-- Notika icon CSS
		============================================ -->
    <link rel="stylesheet" href="css/notika-custom-icon.css">
    <!-- wave CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/wave/waves.min.css">
    <!-- cropper CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/cropper/cropper.min.css">
    <!-- main CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/main.css">
    <!-- style CSS
		============================================ -->
    <link rel="stylesheet" href="../../style.css">
    <!-- responsive CSS
		============================================ -->
    <link rel="stylesheet" href="../../css/responsive.css">
    <!-- modernizr JS
		============================================ -->
    <script src="../../js/vendor/modernizr-2.8.3.min.js"></script>
</head>

<body>
	<!-- Breadcomb area End-->
    <!-- Image Cropper area Start-->
    <div class="images-cropper-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="image-cropper-wp">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="image-crop">
                                    <img src="../../img/cropper/1.jpg" alt="">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <div class="preview-img-pro-ad">
                                    <div class="maincrop-img">
                                        <div class="image-crp-int">
                                            <h4>Preview image</h4>
                                            <div class="img-preview img-preview-custom"></div>
                                        </div>
                                        <div class="image-crp-img">
                                            <h4>Comon method</h4>
                                            <p>You can upload new image to crop.</p>
                                            <div class="btn-group images-cropper-pro">
                                                <label title="Upload image file" for="inputImage" class="btn btn-primary img-cropper-cp">
														<input type="file" accept="image/*" name="file" id="inputImage" class="hide"> Upload new image
													</label>
                                                <label title="Donload image" id="download" class="btn btn-primary">Download</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cp-img-anal">
                                        <h4>Other method</h4>
                                        <p>
                                            You may set cropper options with <code>$(image}).cropper(options)</code>
                                        </p>
                                        <div class="btn-group images-action-pro">
                                            <button class="btn btn-white" id="zoomIn" type="button">Zoom In</button>
                                            <button class="btn btn-white" id="zoomOut" type="button">Zoom Out</button>
                                            <button class="btn btn-white" id="rotateLeft" type="button">Rotate Left</button>
                                            <button class="btn btn-white" id="rotateRight" type="button">Rotate Right</button>
                                            <button class="btn btn-warning img-cropper-cp-t" id="setDrag" type="button">New crop</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Image Cropper area End-->
    <!-- Start Footer area-->
    <div class="footer-copyright-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="footer-copy-right">
                        <p>Copyright © 2018 
. All rights reserved. Template by <a href="https://colorlib.com">Colorlib</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Footer area-->
    <!-- jquery
		============================================ -->
    <script src="../../js/vendor/jquery-1.12.4.min.js"></script>
    <script src="../../js/cropper/cropper.min.js"></script>
    <script src="../../js/cropper/cropper-actice.js"></script>

</body>

</html>