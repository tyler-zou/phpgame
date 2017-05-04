<!DOCTYPE html>
<html lang="zh">
    <head>
        <title>SzFramework Demo</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script type="text/javascript">
            var SZ = {
                "WEB_HOST": "{$WEB_HOST}",
                "CDN_HOST": "{$CDN_HOST}",
                "IMG_URL": "http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/img/",
                "JS_URL": "http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/js/",
                "REQUEST_URL": "http://{$WEB_HOST}/{$WWW_RELATIVE_PATH}/index.php",
                "CANVAS_URL": "{$CANVAS_URL}",
                "APP": null
            };
            less = {
                relativeUrls: true,
                rootpath: "http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/img/"
            };
        </script>
        <link href="http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/js/lib/bootstrap/css/bootstrap-3.0.0.min.css" rel="stylesheet" media="screen" />
        <link href="http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/js/lib/bootstrap/font-awesome/css/font-awesome-3.2.1.min.css" rel="stylesheet" />
        <link href="http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/css/main.less" rel="stylesheet/less" type="text/css" />
        <script src="http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/js/lib/jquery/jquery-1.10.2.min.js?v={$JS_VER}" type="text/javascript"></script>
        <script src="http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/js/lib/less/less-1.4.1.min.js?v={$JS_VER}" type="text/javascript"></script>
        <script src="http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/js/lib/bootstrap/bootstrap-3.0.0.min.js?v={$JS_VER}" type="text/javascript"></script>
        <script
            data-main="http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/js/boot"
            src="http://{$CDN_HOST}/{$WWW_RELATIVE_PATH}/js/lib/require/require-2.1.8.min.js?v={$JS_VER}">
        </script>
    </head>
    <body>
        <!-- TOP FIXED NAV BAR -->
        <div class="navbar navbar-fixed-top navbar-inverse">
            <div class="container">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">Title</a>
                <div class="nav-collapse collapse navbar-responsive-collapse">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="#">Home</a></li>
                        <li><a href="#">Link</a></li>
                        <li><a href="#">Link</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="#">Action</a></li>
                                <li><a href="#">Another action</a></li>
                                <li><a href="#">Something else here</a></li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Dropdown header</li>
                                <li><a href="#">Separated link</a></li>
                                <li><a href="#">One more separated link</a></li>
                            </ul>
                        </li>
                    </ul>
                    <form class="navbar-form pull-left" action="">
                        <input type="text" class="form-control col-lg-8" placeholder="Search">
                    </form>
                    <ul class="nav navbar-nav pull-right">
                        <li><a href="#">Link</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="#">Action</a></li>
                                <li><a href="#">Another action</a></li>
                                <li><a href="#">Something else here</a></li>
                                <li class="divider"></li>
                                <li><a href="#">Separated link</a></li>
                            </ul>
                        </li>
                    </ul>
                </div><!-- /.nav-collapse -->
            </div><!-- /.container -->
        </div>
        <!-- PAGE CONTAINER -->
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <!-- PAGE LEFT SUB NAV BAR -->
                    <div class="page-header">
                        <ul class="nav nav-pills nav-stacked">
                            <li class="active"><a href="#"><i class="icon-home"></i> Home</a></li>
                            <li><a href="#"><i class="icon-book"></i> Library</a></li>
                            <li><a href="#"><i class="icon-pencil"></i> Applications</a></li>
                            <li><a href="#"><i class="icon-cogs"></i> Settings</a></li>
                            <li class="nav-divider"></li>
                            <li><a href="#"><i class="icon-search"></i> Help</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9">
                    <!-- PAGE HEADER -->
                    <div class="page-header">
                        <h1>SzFramework Demo <small>application</small></h1>
                    </div>
                    <!-- PAGE CONTENT -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-12">
                                    <!-- Button trigger modal -->
                                    <a data-toggle="modal" href="#jsInfoDialog" class="btn btn-primary btn-large">Display JS Lib Info</a>
                                    <!-- Modal -->
                                    <div class="modal fade" id="jsInfoDialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                    <h4 class="modal-title">Modal Title</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <h5>Modal Body</h5>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="#" class="btn close" data-dismiss="modal">Close</a>
                                                    <a href="#" class="btn btn-primary">Save changes</a>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->
                                </div>
                            </div>
                            <hr/>
                            <div class="row">
                                <div id="RactiveContainer" class="col-lg-12">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div id="sublime" class="thumbnail"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>