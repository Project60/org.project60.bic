#!/bin/bash
set -eu -o pipefail

SCRIPT_DIR=$(realpath "$(dirname "$0")")
EXT_DIR=$(dirname "$SCRIPT_DIR")

cd "$EXT_DIR"
if [ ! -e tools/phpunit/vendor/bin ]; then
  "$SCRIPT_DIR/docker-prepare.sh"
fi

# CIVICRM_SMARTY_AUTOLOAD_PATH is not set in the container's civicrm.settings.php so we have to do it here.
# Otherwise this results in this error:
# Fatal error: Cannot declare class Smarty, because the name is already in use in /var/www/html/sites/all/modules/civicrm/packages/smarty5/Smarty.php on line 4
smarty=$(printf '%s\n' /var/www/html/sites/all/modules/civicrm/packages/smarty* | sort -r | head -n1)
if [ -e "$smarty/Smarty.php" ]; then
  export CIVICRM_SMARTY_AUTOLOAD_PATH="$smarty/Smarty.php"
elif [ -e "$smarty/vendor/autoload.php" ]; then
  export CIVICRM_SMARTY_AUTOLOAD_PATH="$smarty/vendor/autoload.php"
fi

export XDEBUG_MODE=coverage
# TODO: Remove when not needed, anymore.
# In Docker container with CiviCRM 5.5? all deprecations are reported as direct
# deprecations so "disabling" check of deprecation count is necessary for the
# tests to pass (if baselineFile does not contain all deprecations).
export SYMFONY_DEPRECATIONS_HELPER="max[total]=99999&baselineFile=./tests/ignored-deprecations.json"

composer phpunit -- --cache-result-file=/tmp/.phpunit.result.cache "$@"
