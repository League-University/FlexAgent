#!/bin/bash
# This script installs the FlexAgent application by copying files to the appropriate directories.
# It is designed to be run from the root directory of the FlexAgent project, immediately after cloning the repository.

# Check if the script is run as root
if [ "$(id -u)" -ne 0 ]; then
  echo "This script must be run as root. Please use sudo."
  exit 1
fi

# Make sure PHP is installed
if ! command -v php &> /dev/null; then
  echo "PHP is not installed. Please install PHP 7.2 or higher."
  exit 1
fi

# Check PHP version
php_version=$(php -r 'echo PHP_VERSION;')
if [[ "$(printf '%s\n' "7.2" "$php_version" | sort -V | head -n1)" == "7.2" && "$php_version" != "7.2" ]]; then
  echo "PHP version is too low. Please install PHP 7.2 or higher."
  exit 1
fi

# Prompt user for installation method
read -p "Install using symlinks? (y/n): " use_symlinks
if [[ "$use_symlinks" =~ ^[Yy]$ ]]; then
  echo "Symlinking files to filesystem..."
  for dir in ./etc ./opt ./var; do
    if [ -d "$dir" ]; then
      ln -sf "${PWD}/$dir" "/${dir}" || echo "Failed to symlink $dir"
    else
      echo "Skipping $dir as it is not a valid directory."
    fi
  done
else
  echo "Copying files to filesystem..."
  for dir in ./etc ./opt ./var; do
    if [ -d "$dir" ]; then
      cp -r "${PWD}/$dir" "/${dir}" || echo "Failed to copy $dir"
    else
      echo "Skipping $dir as it is not a valid directory."
    fi
  done
fi
