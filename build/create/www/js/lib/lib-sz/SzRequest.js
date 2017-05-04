define(["SzLogger", "jquery"], function(SzLogger) {

    /**
     * EfRequest, declaration.
     */
    var SzRequest = function() {};

    /**
     * Send async POST request.
     *
     * @param {Object} params
     * @param {Function} onSuccess
     * @param {Function} onFailure
     * @param {String} url
     * @return {void}
     */
    SzRequest.prototype.sendReq = function(params, onSuccess, onFailure, url) {
        if (typeof params != 'object' || typeof onSuccess != 'function') {
            return;
        }
        $.ajax({
            type: 'POST',
            url: (typeof url === 'string' && url != null && url != '') ? url : SZ.REQUEST_URL,
            data: params,
            dataType: 'json',
            success: function(data){
                if (null == data) {
                    // no response, display error message
                    if (typeof onFailure == 'function') {
                        onFailure();
                    }
                    SzLogger.log('Request failed! null data returned!');
                } else {
                    // means this is a successful response, call response function
                    onSuccess(data);
                    SzLogger.log('Request succeed! response function called!');
                }
            },
            error: function(err){
                if (typeof onFailure == 'function') {
                    onFailure(err);
                }
                SzLogger.log('Request failed! Error message: ' + err.responseText);
            }
        });
    };

    /**
     * Send async POST request. Follow the message format of SzFramework.
     *
     * @param {Object} params
     * @param {Function} onSuccess
     * @param {Function} onFailure
     * @return {void}
     */
    SzRequest.prototype.sendSzReq = function(params, onSuccess, onFailure) {
        if (typeof params != 'object' || typeof onSuccess != 'function') {
            return;
        }
        $.ajax({
            type: 'POST',
            url: SZ.REQUEST_URL,
            data: {"*": [params]},
            dataType: 'json',
            success: function(data){
                if (null == data) {
                    // no response, display error message
                    if (typeof onFailure == 'function') {
                        onFailure();
                    }
                    SzLogger.log('Request failed! null data returned!');
                } else if (null != data && 'object' == typeof data && data.code != 0) {
                    // means this is an error reponse
                    // 'code' means response result status, 0 means succeed, otherwise it means error code
                    if (typeof onFailure == 'function') {
                        onFailure(data);
                    }
                    SzLogger.log('Request failed! Err code: ' + data.code + ', Err msg: ' + data.msg);
                } else {
                    // means this is a successful response, call response function
                    onSuccess(data.msg[0]);
                    SzLogger.log('Request succeed! response function called!');
                }
            },
            error: function(err){
                if (typeof onFailure == 'function') {
                    onFailure(err);
                }
                SzLogger.log('Request failed! Error message: ' + err.responseText);
            }
        });
    };

    /**
     * Get http resource.
     *
     * @param {String} url
     * @param {Function} onSuccess
     * @return {void}
     */
    SzRequest.prototype.getResource = function(url, onSuccess) {
        $.get(url, function(data) {
            onSuccess(data);
        });
    };

    return new SzRequest();

});