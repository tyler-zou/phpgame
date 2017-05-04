define(["jquery.json"], function() {

    /**
     * SzUtil, declaration.
     */
    var SzUtil = function() {};

    /**
     * Get swf movie on page.
     *
     * @return {Object} swfMovie
     */
    SzUtil.prototype.getMovie = function(movieName) {
        if (navigator.appName.indexOf('Microsoft') != -1) {
            return window[movieName];
        } else {
            return document[movieName];
        }
    };

    /**
     * Format object to json string with '<pre>' tags wrapped.
     *
     * @param {Object} object
     * @return {String} msg
     */
    SzUtil.prototype.printObject = function(object) {
        var msg = '';
        if (typeof JSON != 'undefined') {
            msg = JSON.stringify(object, null, 4);
        } else {
            msg = $.toJSON(object);
        }

        return '<pre>' + msg + '</pre>';
    };

    /**
     * Get url param.
     *
     * @param {String} name the name of the param
     * @return {String} param
     */
    SzUtil.prototype.getUrlParam = function(name) {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r!=null) return unescape(r[2]); return null;
    };

    /**
     * Refresh page.
     *
     * @return {void}
     */
    SzUtil.prototype.refreshPage = function() {
        window.location.reload();
    };

    return new SzUtil();

});