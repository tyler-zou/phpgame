define(
    [
        "require", "SzRequest", "SzLogger", "Ractive", "text!view/ractive.html",
        "jade", "jquery.json", "underscore"
    ],
    function(
        require, SzRequest, SzLogger, Ractive, ractiveTemplate
    ) {

    require('jade');

    var APP = function() {};

    APP.prototype.init = function() {
        this.initJade();
        this.initRactive();
    };

    /**
     * Use SzRequest to retrieve jade template asynchronously,
     * and use jade lib to render html.
     */
    APP.prototype.initJade = function() {
        SzLogger.log('Jade init start!');
        SzRequest.getResource(SZ.JS_URL + 'view/jsInfo.jade', function(template) {
            var render = jade.compile(template);
            var params = {
                "JQUERY_VERSION": $().jquery,
                "UNDERSCORE_VERSION": _.VERSION,
                "JSON_STR": $.toJSON({"jquery": $().jquery, "underscore": _.VERSION})
            };
            $('#jsInfoDialog .modal-body').append(
                render(params)
            );
            SzLogger.log('Jade init done!');
        });
    };

    /**
     * Use require js text plugin to retrieve template,
     * and use Ractive to render html, and build auto update.
     */
    APP.prototype.initRactive = function() {
        SzLogger.log('Ractive init start!');

        var users = [];

        var TestTryAction = new Ractive({
            "el": "RactiveContainer",
            "template": ractiveTemplate,
            "data": {
                "users": users
            }
        });

        var showError = function(msg) {
            var alert = $('#ractiveAlert');
            alert.html('');
            alert.append(
                '<div class="alert alert-error">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    '<span>' + msg + '</span>' +
                '</div>'
            );
        };

        var showSuccess = function(msg) {
            var alert = $('#ractiveAlert');
            alert.html('');
            alert.append(
                '<div class="alert alert-success">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    '<span>' + msg + '</span>' +
                '</div>'
            );
        };

        var clearInput = function() {
            $('#ractiveFirstName').val('');
            $('#ractiveLastName').val('');
        };

        $("#ractiveSubmit").click(function() {
            // param validation
            var firstName = $('#ractiveFirstName').val();
            var lastName = $('#ractiveLastName').val();
            if (!firstName || !lastName) {
                showError('<strong>Error!</strong> User name cannot be empty!');
                return;
            }
            SzRequest.sendSzReq(
                ["test.try", [firstName, lastName]],
                function(data) {
                    clearInput();
                    var user = {
                        "firstName": data.firstName,
                        "lastName": data.lastName,
                        "identify": data.identify
                    };
                    /**
                     * Since we already update the "users", and the pointer already in
                     * the "TestTryAction", so it not necessary to call
                     * "TestTryAction.get("users").push(user);"
                     * separately.
                     */
                    users.push(user);
                    showSuccess('<strong>Succeed! </strong> User "' + data.firstName + '.' + data.lastName + '" registered! Uuid: "' + data.identify + '"');
                },
                function(err) {
                    showError(err);
                }
            );
        });

        $("#ractiveClear").click(function() {
            clearInput();
        });

        SzLogger.log('Ractive init done!');
    };

    return new APP();

});