#!/bin/bash

# WARNING: DO NOT point this to a web server you do not own!
# WARNING: DO NOT do this in production!
# WARNING: ENABLE server-side caching!
#          This test was written to verify that the cache "lock" feature works properly.
# WARNING: If the cache "lock" feature fails, your web server will send a LOT of UDP requests
#          to the server specified in your config.php

# Send 100 requests in parallel
seq 1 100 | xargs -P0 -I{} curl -s http://127.0.0.1/main-server?stress_test={} > /dev/null
