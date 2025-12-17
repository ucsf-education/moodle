<?php
// Some normal code
echo "Loading page to test fatal error in Sentry\n";

// Trigger a fatal error
undefined_function_call();

