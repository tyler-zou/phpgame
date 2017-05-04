define(["SzLogger"], function(SzLogger) {

    var SzSns = function() {};

    /**
     * Get user info from platform.
     *
     * @param {String} userId
     * @return {Object} userInfo
     */
    SzSns.prototype.getUserInfo = function(userId) {
        this.logAPICall('SzSns.getUserInfo');
        return {};
    };

    /**
     * Get user infos from platform.
     *
     * @param {Array} userIds
     * @return {Array} userInfos array of the result as SzSns.getUserInfo
     */
    SzSns.prototype.getUserInfos = function(userIds) {
        this.logAPICall('SzSns.getUserInfos');
        return [];
    };

    /**
     * Send feed request.
     *
     * @return {void}
     */
    SzSns.prototype.sendFeed = function() {
        this.logAPICall('SzSns.sendFeed');
    };

    /**
     * Send platform request.
     *
     * @return {void}
     */
    SzSns.prototype.sendRequest = function() {
        this.logAPICall('SzSns.sendRequest');
    };

    /**
     * Add platform friend.
     *
     * @return {void}
     */
    SzSns.prototype.addFriend = function() {
        this.logAPICall('SzSns.addFriend');
    };

    /**
     * Direct player to friend's home page.
     *
     * @return {void}
     */
    SzSns.prototype.gotoFriendHome = function() {
        this.logAPICall('SzSns.gotoFriendHome');
    };

    /**
     * Display payment choices on page.
     *
     * @return {void}
     */
    SzSns.prototype.displayPayment = function() {
        this.logAPICall('SzSns.displayPayment');
    };

    /**
     * Send payment order to platform & server.
     *
     * @return {void}
     */
    SzSns.prototype.sendPaymentOrder = function() {
        this.logAPICall('SzSns.sendPaymentOrder');
    };

    /**
     * Resize platform canvas page.
     *
     * @return {void}
     */
    SzSns.prototype.resizeCanvas = function() {
        this.logAPICall('SzSns.resizeCanvas');
    };

    /**
     * Log SzSns API call.
     *
     * @param {string} func function name
     * @return {void}
     */
    SzSns.prototype.logAPICall = function(func) {
        SzLogger.log(
            'Interface API ' + func + ' called! There is no implementation found!'
        );
    };

    return SzSns;

});