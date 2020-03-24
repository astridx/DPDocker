#!/bin/bash

if [ -z $1 ]; then
  echo "No pr set, please run the script with a pr number from https://github.com/joomla/joomla-cms/pulls!"
  exit 1
fi

# Clone the repo if it doesn't exist for cache
if [ ! -d /var/www/html/pr/cache ]; then
  echo "Building cache"
  git clone https://github.com/joomla/joomla-cms.git /var/www/html/pr/cache
fi

# Setup root dir
root=/var/www/html/pr/$1

# Empty the install directory if rebuild is set
if [ ! -z $2 ]; then
  echo "Cleaning install folder"
  sudo rm -rf $root
fi

# Create the root directory when it doesn't exist
if [ ! -d $root ]; then
  echo "Copy files from /var/www/html/pr/cache"
  mkdir -p $root
  cp -a /var/www/html/pr/cache/. $root
fi

cd $root

# Switch to the pr
echo "Switching to pr code"
git reset --hard &>/dev/null
git branch -D pr-$1 &>/dev/null
git fetch origin pull/$1/head:pr-$1 &>/dev/null
git checkout pr-$1 &>/dev/null

# Run composer
if [ ! -z $2 ]; then
  rm -rf $root/libraries/vendor
fi
echo "Installing PHP dependencies"
composer install --quiet

# Run npm
if [ -f $root/package.json ]; then
  if [ ! -z $2 ]; then
    echo "Cleaning the assets"
    npm c &>/dev/null
  fi
  if [ ! -d $root/media/vendor ]; then
    echo "Installing the assets"
    npm i &>/dev/null
  fi
fi

# Install Joomla when no configuration file is available
if [ ! -f $root/configuration.php ]; then
  echo "Installing Joomla"
  cp $(dirname $0)/configuration.php $root/configuration.php
  sed -i "s/{PR}/$1/g" $root/configuration.php

  mysql -u root -proot -h mysql-pr -e "drop database if exists joomla_pr_$1";
  mysql -u root -proot -h mysql-pr -e "create database joomla_pr_$1";
  sed "s/#_/j/g" $root/installation/sql/mysql/joomla.sql | mysql -u root -proot -h mysql-pr -D joomla_pr_$1
  mysql -u root -proot -h mysql-pr -D joomla_pr_$1 -e "INSERT INTO j_users (id, name, username, email, password, block) VALUES
(42, 'admin', 'admin', 'admin@example.com', '\$2y\$10\$O.A8bZcuC6yFfgjzycqzku7LuG6zvBiozJcjXD4FP3bhJdvyKdtoG', 0);";
  mysql -u root -proot -h mysql-pr -D joomla_pr_$1 -e "INSERT INTO j_user_usergroup_map (user_id, group_id) VALUES ('42', '8');";
fi

echo -e "\e[32mPR $1 is checked out and ready to test on http://localhost:8090/pr/$1!"
echo -e "\e[32mYou can log in with admin/admin!"
echo -e "\e[32mPHPMyAdmin is available on http://localhost:8091!"
echo -e "\e[32mMailcatcher is available on http://localhost:8092!"