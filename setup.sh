#!/bin/bash

if [ "$EUID" -ne 0 ]; then
  sudo "$0"
  exit
fi

if [ $# -ne 1 ]; then
  echo "[ABORT] You need to specify a secure config directory (\$#==1)"
  exit
fi

config_dir=$(realpath "$1")

if [ ! -d $config_dir ]; then
  echo "[ABORT] The config directory doesnt exist"
  exit
fi

# server config
cp .dist.serverconfig.php "$config_dir/config.php"
chmod 600 "$config_dir/config.php"

# elevator script
gcc -o "$config_dir/elevator" elevator.c
chown root:root "$config_dir/elevator"
chmod 4755 "$config_dir/elevator"

# deploy.php script
cp deploy.php "$config_dir/deploy.php"
chmod 700  "$config_dir/deploy.php"

# example repo config
cp .dist.repoconfig.php "$config_dir"

echo -e '#! /bin/bash\necho "Hallo, du bist doof!"' > /etc/update-motd.d/webhook-deploy
chmod a+x /etc/update-motd.d/webhook-deploy
echo "[DONE] Success: your config dir is $config_dir"