<?php
/**
 * Used to skip all built-in (internal) functions.
 */
define('XHPROF_FLAGS_NO_BUILTINS', null);
/**
 * Used to add CPU profiling information to the output.
 */
define('XHPROF_FLAGS_CPU', null);
/**
 * Used to add memory profiling information to the output.
 */
define('XHPROF_FLAGS_MEMORY', null);

/**
 * Start xhprof profiler.
 *
 * @param int $flags default 0
 * <pre>
 * Optional flags to add additional information to the profiling.
 * See the XHprof constants for further information about these flags,
 * e.g., XHPROF_FLAGS_MEMORY to enable memory profiling.
 * </pre>
 * @param array $options default array()
 * <pre>
 * An array of optional options, namely,
 * the 'ignored_functions' option to pass in functions to be ignored during profiling.
 * </pre>
 * @return null
 */
function xhprof_enable($flags = 0, $options = array())
{
    return null;
}

/**
 * Stops the profiler, and returns xhprof data from the run.
 *
 * @return array
 * <pre>
 * An array of xhprof data, from the run.
 * </pre>
 */
function xhprof_disable()
{
    return array();
}