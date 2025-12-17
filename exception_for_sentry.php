<?php
// Some normal code
echo "Loading page to test Sentry\n";

// Throw an exception for Sentry to log
throw new Exception('Test error: Sentry logging check');

