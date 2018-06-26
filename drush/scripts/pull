#!/usr/bin/env bash
base_dir=$(cd $(dirname "${BASH_SOURCE[0]}")/../../ && pwd)
drush=$base_dir/vendor/bin/drush

source=${1:-stg}

$drush sql:sync @$source @dev \
&& $drush core:rsync @$source:%files @dev:%files \
&& $drush --yes cache-rebuild
