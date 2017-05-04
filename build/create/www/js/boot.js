requirejs.config({
    baseUrl: SZ.JS_URL,
    paths: {
        // libs
        "bootstrap":     "lib/bootstrap/bootstrap-2.3.2.min",
        "jade":          "lib/jade/jade-0.27.7.min",
        "jquery.cookie": "lib/jquery/jquery.cookie-1.3.1.min",
        "jquery.json":   "lib/jquery/jquery.json-2.4.min",
        "jquery":        "lib/jquery/jquery-1.10.2.min",
        "jscharts":      "lib/jscharts/jscharts-2.08.min",
        "less":          "lib/less/less-1.4.1.min",
        "moment":        "lib/moment/moment-2.1.0.min",
        "Ractive":       "lib/Ractive/Ractive-0.3.3.min",
        "swfobject":     "lib/swfobject/swfobject-2.2.min",
        "swfrightclick": "lib/swfrightclick/rightclick-0.6.2",
        "underscore":    "lib/underscore/underscore-1.5.0.min",
        // plugins of lib RequireJs
        "text":          "lib/require/plugins/text-2.0.9.min",
        // lib-sz
        "SzBase64":      "lib/lib-sz/SzBase64",
        "SzLogger":      "lib/lib-sz/SzLogger",
        "SzRequest":     "lib/lib-sz/SzRequest",
        "SzTime":        "lib/lib-sz/SzTime",
        "SzUtil":        "lib/lib-sz/SzUtil",
        // lib-sz sns
        "SzSns":         "lib/lib-sz/SzSns",
        "SzSnsFacebook": "lib/lib-sz/sns/SzSnsFacebook",
        // app
        "app": "app" // write your application javascript codes here, and you can define more modules
    },
    shim: {
        "bootstrap": {
            deps: ["jquery"]
        },
        "jquery.cookie": {
            deps: ["jquery"]
        },
        "jquery.json": {
            deps: ["jquery"]
        },
        "jquery": {
            exports: '$'
        },
        "underscore": {
            exports: '_'
        }
    }
});

/**
 * Initialize the whole JavaScript env.
 */
require(["app"], function(app) {
    $(document).ready(function() {
        app.init();
        SZ.APP = app;
    });
});