var async = require('async');
var path = require('path');

module.exports = function(grunt) {
    'use strict';

    grunt.option('stack', true); // print stack trace when warning & error encountered

    var pkg = grunt.file.readJSON('package.json');

    var env = grunt.option('env') || 'dev'; // e.g --env=staging
    var repo = grunt.option('repo') || 'all'; // e.g --repo=framework

    /**
     * Read developers list from option.
     */
    var developers = grunt.option('dev') || ''; // e.g -- dev=jonathan,mike,john
    if (developers) {
        developers = developers.split(',');
        if (developers[0] === '') {
            developers = [];
        }
    }

    /**
     * Prepare the param to store the base path of the current grunt working directory.
     */
    var basePath = null;
    grunt.util.spawn({
        "cmd": "pwd"
    }, function(err, result, code) {
        basePath = result.stdout;
    });

    /**
     * Default grunt task.
     */
    grunt.task.registerTask('default', 'Release using git', function() {
        var done = this.async();

        /**
         * Check error and terminate the app if err encountered,
         * otherwise instruct that the task has been finished properly.
         *
         * @param {Error} err null means no error
         * @param {Object} result
         * @param {number} code
         */
        function gruntSpawnCheck(err, result, code) {
            /**
             * grunt.util.spawn output:
             * {
             *     "stdout": "",
             *     "stderr": "",
             *     "code": 0,
             *     "toString": [Function]
             * }
             * sometimes "err" may not be empty string and the request was finished properly, e.g:
             * git pull:
             * {
             *     "stdout": "Already up-to-date.",
             *     "stderr": "From ...\n * branch            develop    -> FETCH_HEAD",
             *     "code": 0,
             *     "toString": [Function]
             * }
             */
            if (code !== 0 && err) {
                grunt.fail.warn(err);
            } else {
                if (result.stdout) {
                    grunt.log.ok(result.stdout);
                }
                if (result.stderr) {
                    grunt.log.ok(result.stderr);
                }
            }
        }

        /**
         * Read config from package.js config file.
         *
         * @param {Array} keys e.g ["env", "dev", "framework"]
         */
        function utilReadConfig(keys) {
            var conf = pkg;
            if (keys && typeof keys === 'object') {
                for (var index in keys) {
                    var key = keys[index];
                    if (!conf.hasOwnProperty(key)) {
                        grunt.fail.warn('config key: ' + key + ' not found!');
                    }
                    conf = conf[key];
                }
            }
            return conf;
        }

        /**
         * Copy files from source dir to destination dir.
         *
         * @param {string} src copy from
         * @param {string} dest copy to
         * @param {Array} [fileFilter] [".git", ".svn"]
         * @param {Array} [dirFilter] [".txt", ".md"]
         */
        function utilCopyDir(src, dest, fileFilter, dirFilter) {
            grunt.log.writeln('Copying dir from: ' + src + ', to: ' + dest);

            grunt.file.recurse(src, function(abspath, rootdir, subdir, filename) {
                if (dirFilter) {
                    for (var dfKey in dirFilter) {
                        if (abspath.indexOf(dirFilter[dfKey]) !== -1) {
                            return;
                        }
                    }
                }
                if (fileFilter) {
                    for (var ffKey in fileFilter) {
                        if (filename.indexOf(fileFilter[ffKey]) !== -1) {
                            return;
                        }
                    }
                }

                var targetPath = subdir ? path.join(dest, subdir, filename) : path.join(dest, filename);
                //grunt.log.writeln('Copying file to: ' + targetPath);
                grunt.file.copy(abspath, targetPath);
            });
        }

        /**
         * Clone target git repository codes into clone path.
         *
         * @param {string} repoName
         * @param {Function} callback
         */
        function gitCloneRepository(repoName, callback) {
            grunt.log.writeln('***********************************************************');
            grunt.log.writeln('Processing repository: ' + repoName);
            grunt.log.writeln('***********************************************************');
            grunt.log.writeln('Start to clone repository: ' + repoName);
            var repoUrl = utilReadConfig(["env", env, repoName, "repo"]);
            var clonePath = utilReadConfig(["env", env, repoName, "clonePath"]) + '/' + repoName;

            if (!grunt.file.exists(clonePath)) {
                grunt.log.writeln('Repository does not exist, clone it: ' + clonePath);
                // clone repo if dir does not exists
                grunt.util.spawn({
                    "cmd": "git",
                    "args": ["clone", repoUrl, clonePath]
                }, function(err, result, code) {
                    gruntSpawnCheck(err, result, code);
                    callback(null, repoName);
                });
            } else {
                grunt.log.writeln('Repository already exists, skip cloning');
                // update the local repo
                callback(null, repoName);
            }
        }

        /**
         * Switch grunt CWD to repository clone path.
         *
         * @param {string} repoName
         * @param {Function} callback
         */
        function gitSwitchCwdToRepository(repoName, callback) {
            grunt.log.writeln('Start to switch cwd to repository: ' + repoName);
            var clonePath = utilReadConfig(["env", env, repoName, "clonePath"]) + '/' + repoName;

            grunt.file.setBase(clonePath); // switch to the cloned git dir

            var branch = utilReadConfig(["env", env, repoName, "branch"]);
            callback(null, repoName, branch);
        }

        /**
         * Update the repository of specified branch in config file.
         *
         * @param {string} repoName
         * @param {string} branchName
         * @param {Function} callback
         */
        function gitUpdateRepositoryBranch(repoName, branchName, callback) {
            grunt.log.writeln('Start to update branch "' + branchName + '" of repository: ' + repoName);

            grunt.util.spawn({
                "cmd": "git",
                "args": ["pull", "origin", branchName]
            }, function(err, result, code) {
                gruntSpawnCheck(err, result, code);
                callback(null, repoName);
            });
        }

        /**
         * Get all the tag names of the repository.
         *
         * @param {string} repoName
         * @param {Function} callback
         */
        function gitGetTags(repoName, callback) {
            grunt.log.writeln('Start to get repository tags: ' + repoName);

            grunt.util.spawn({
                "cmd": "git",
                "args": ["tag"]
            }, function(err, result, code) {
                gruntSpawnCheck(err, result, code);
                var tags = result.stdout.split("\n");
                if (tags[0] === '') {
                    tags = []; // means there is no tags in this repository
                }
                callback(null, repoName, tags);
            });
        }

        /**
         * Copy framework "build/publish" codes to app root dir.
         *
         * @param {string} repoName
         * @param {Array} tags
         * @param {Function} callback
         */
        function gitDeployAppRepositoryPublishDir(repoName, tags, callback) {
            grunt.log.writeln('Start to deploy app publish codes: ' + repoName);

            var isApp = utilReadConfig(["env", env, repoName, "isApp"]);

            if (isApp) {
                // ensure destination path exists
                var destPath = utilReadConfig(["env", env, repoName, "destPath"]) + '/' + repoName;
                if (!grunt.file.exists(destPath)) {
                    grunt.log.writeln('App deploy destination path does not exist, create it: ' + destPath);
                    grunt.file.mkdir(destPath);
                }
                // copy publish codes
                var frameworkClonePublishPath = utilReadConfig(["env", env, "framework", "clonePath"]) + '/framework/build/publish';
                utilCopyDir(frameworkClonePublishPath, destPath);
            }

            callback(null, repoName, tags);
        }

        /**
         * Start to deploy the repository codes.
         *
         * @param {string} repoName
         * @param {Array} tags
         * @param {Function} callback
         */
        function gitDeployRepository(repoName, tags, callback) {
            grunt.log.writeln('Start to deploy repository "' + repoName + '" with tags: ');
            grunt.log.writeln(JSON.stringify(tags));

            var branch = utilReadConfig(["env", env, repoName, "branch"]);
            tags.push(branch);

            var flow = [];

            for (var tagOrBranchKey in tags) (function(tagOrBranchName) {
                flow.push(function(flowCallback) {
                    flowCallback(null, repoName, tagOrBranchName);
                });
                flow.push(gitCheckoutRepositoryBranchOrTag);
                flow.push(gitDeployRepositoryBranchOrTag);
            })(tags[tagOrBranchKey]);

            async.waterfall(flow, function(err, result) {
                if (!err) {
                    callback(null);
                } else {
                    grunt.fail.warn(err);
                }
            });
        }

        /**
         * Checkout the specified branch or tag of the repository.
         *
         * @param {string} repoName
         * @param {string} branchOrTagName
         * @param {Function} callback
         */
        function gitCheckoutRepositoryBranchOrTag(repoName, branchOrTagName, callback) {
            grunt.log.writeln('Start to checkout branch/tag "'+ branchOrTagName +'" of repository: ' + repoName);

            grunt.util.spawn({
                "cmd": "git",
                "args": ["checkout", branchOrTagName]
            }, function(err, result, code) {
                gruntSpawnCheck(err, result, code);
                callback(null, repoName, branchOrTagName);
            });
        }

        /**
         * Copy the codes of specified branch or tag to the destination path.
         *
         * @param {string} repoName
         * @param {string} branchOrTagName
         * @param {Function} callback
         */
        function gitDeployRepositoryBranchOrTag(repoName, branchOrTagName, callback) {
            grunt.log.writeln('Start to copy branch/tag "'+ branchOrTagName +'" of repository "' + repoName + '" to deploy position');

            var destPath = '';
            var clonePath = utilReadConfig(["env", env, repoName, "clonePath"]) + '/' + repoName;

            var isApp = utilReadConfig(["env", env, repoName, "isApp"]);
            var isConfig = utilReadConfig(["env", env, repoName, "isConfig"]);

            if (isApp) {
                destPath = utilReadConfig(["env", env, repoName, "destPath"]) + '/' + repoName + '/apps/main/';
            } else if (isConfig) {
                destPath = utilReadConfig(["env", env, isConfig, "destPath"]) + '/' + isConfig + '/apps/main/'; // current, "isConfig" means parent app repoName
            } else {
                destPath = utilReadConfig(["env", env, repoName, "destPath"]) + '/' + repoName + '/';
            }

            if (branchOrTagName === 'develop' || branchOrTagName === 'master' // branch is "develop" or "master"
                || branchOrTagName.match(/v[0-9]/) === null) { // branch is not "v..." version tag
                destPath += 'latest'; // in those cases, give "latest" dir
            } else {
                destPath += branchOrTagName; // otherwise, give "v..." tag dir
            }

            // ensure destPath exists
            if (!grunt.file.exists(destPath)) {
                grunt.log.writeln('Deploy dest path does not exists, create it: ' + destPath);
                grunt.file.mkdir(destPath);
            } else {
                grunt.log.writeln('Deploy dest path already exist, deploy it: ' + destPath);
            }

            utilCopyDir(clonePath, destPath, [".txt", ".md"], [".git"]);

            // copy publish config to the app root dir
            if (isConfig) {
                var from = clonePath + '/publish.config.php';
                var to = utilReadConfig(["env", env, isConfig, "destPath"]) + '/' + isConfig + '/publish.config.php';
                grunt.log.writeln('***********************************************************');
                grunt.log.writeln('Copying publish config from: ' + from + ', to: ' + to);
                grunt.file.copy(from, to);
            }

            callback(null);
        }

        /**
         * Deploy app developer dirs after all deployments done.
         *
         * @param {string} repoName
         * @param {Function} callback
         */
        function gitDeployAppRepositoryDevDirs(repoName, callback) {
            grunt.log.writeln('***********************************************************');
            grunt.log.writeln('Start to copy dev dir codes of repository: ' + repoName);

            var destPath = utilReadConfig(["env", env, repoName, "destPath"]) + '/' + repoName + '/apps';

            if (developers.length !== 0) {
                for (var index in developers) {
                    utilCopyDir(destPath + '/main', destPath + '/' + developers[index]);
                }
            }

            callback(null);
        }

        /**
         * ***************************
         * MAIN PROCESS
         * ***************************
         */
        var queue = [];
        utilReadConfig(["env", env]); // ensure config correct

        // normal deploy process, for all repositories including app & non-app
        for (var repoName in pkg.env[env]) (function(repoName) {
            if (repo !== 'all' && repo !== repoName) {
                /**
                 * "repo !== 'all'" means target repo to be deployed is specified.
                 * "repo !== repoName" current loop is not the target, skip it.
                 */
                return;
            }
            queue.push(function(callback) {
                callback(null, repoName); // do nothing with the starter, just call the callback function
            });
            queue.push(gitCloneRepository);
            queue.push(gitSwitchCwdToRepository);
            queue.push(gitCheckoutRepositoryBranchOrTag);
            queue.push(gitUpdateRepositoryBranch);
            queue.push(gitGetTags);
            queue.push(gitDeployAppRepositoryPublishDir);
            queue.push(gitDeployRepository);
        })(repoName);

        // additional deploy process, for app repository, copy all developer dir
        if (developers.length !== 0) {
            for (var repoName in pkg.env[env]) (function(repoName) {
                if (repo !== 'all' && repo !== repoName) {
                    return;
                }
                var isApp = utilReadConfig(["env", env, repoName, "isApp"]);
                if (isApp) {
                    queue.push(function(callback) {
                        callback(null, repoName); // do nothing with the starter, just call the callback function
                    });
                    queue.push(gitDeployAppRepositoryDevDirs);
                }
            })(repoName);
        }

        // start to process
        async.waterfall(queue, function(err, result) {
            if (!err) {
                done();
            } else {
                grunt.fail.warn(err);
            }
        });

    });

};