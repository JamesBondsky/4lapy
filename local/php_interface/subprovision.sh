#!/usr/bin/env bash

printRed () {
    printf "\e[0;31m$1\e[0m\n"
}

printGreen () {
    printf "\e[0;32m$1\e[0m\n"
}

printBlue () {
    printf "\e[0;34m$1\e[0m\n"
}

printYellow () {
    printf "\e[1;33m$1\e[0m\n"
}

# Basic variables
DOCUMENT_ROOT="/home/vagrant/htdocs"
SUBPROV_ROOT="/home/vagrant/.subprovision"
LOCAL_ENV_FILE="${DOCUMENT_ROOT}/bitrix/php_interface/local.env.php"
BITRIX_FOLDER_CHECK="${DOCUMENT_ROOT}/bitrix/index.php"
UNVER_FOLDER_ARCHIVE="${SUBPROV_ROOT}/unversioned-files.tar.gz"
SITE_URI="http://4lapy.vag"
MIGRATION_RUNNER="${DOCUMENT_ROOT}/migrate.php"
CONSOLE_RUNNER="${DOCUMENT_ROOT}/console.php"

# Create unversioned files
if [[ -f "${BITRIX_FOLDER_CHECK}" ]] ; then
    printGreen "Unversioned files seems to be OKay."
    printYellow "To refresh unversioned files, please, remove following files and directories, but be careful!"
    printYellow "\t\t${BITRIX_FOLDER_CHECK}"
    printYellow "\t\t${DOCUMENT_ROOT}/upload/"

else
    printRed "Unversioned files missing. "
    printBlue "Unpacking. Please, wait for a few minutes..."
    tar --overwrite --same-permissions --directory "${DOCUMENT_ROOT}" --gunzip --extract --file "${UNVER_FOLDER_ARCHIVE}"
fi

# Refresh local.env.php
printBlue "Refresh local env file."
sed -re "s/^#/\/\//g" "${DOCUMENT_ROOT}/local/php_interface/.env" \
    | sed -re "s/^[[:alnum:]_]+=.+$/putenv\('\0'\);/ig" \
    | sed -re "1s/^.*$/<?php \n\n\0/" \
    | sed -re "\$s/^.*$/\0\n/" > "${LOCAL_ENV_FILE}"

# Run composer install for the first time
if (shopt -s nullglob dotglob; f=(/home/vagrant/htdocs/local/php_interface/vendor/*); ((${#f[@]}))) ; then
    printGreen "Composer folder is OKay."
else
    printRed "Need composer packages first install"
    printBlue "Installing composer packages for the first time..."
    cd "${DOCUMENT_ROOT}"
    sudo -u vagrant composer install --optimize-autoloader --quiet --no-interaction
    cd - > /dev/null
fi

# Grant execution of migrations
if [[ -x "${MIGRATION_RUNNER}" ]] ; then
    printGreen "Migrations runner script execution permissions are OKay"
else
    printRed "Migrations runner is not executable."
    if [[ -f "${MIGRATION_RUNNER}" ]]; then
        printBlue "Mark ${MIGRATION_RUNNER} as executable "
        chmod a+x "${MIGRATION_RUNNER}"
    fi
fi

# Grant execution of migrations
if [[ -x "${CONSOLE_RUNNER}" ]] ; then
    printGreen "Console runner script execution permissions are OKay"
else
    printRed "Console runner is not executable."
    if [[ -f "${CONSOLE_RUNNER}" ]]; then
        printBlue "Mark ${CONSOLE_RUNNER} as executable "
        chmod a+x "${CONSOLE_RUNNER}"
    fi
fi

#TODO Возможно, понадобится добавить начальную сборку статики или инициализацию субмодулей сюда

printGreen "Subprovision is done."
printf "\e[0;32mVisit \e[0m\e[1;34;4;34m${SITE_URI}\e[0m \e[0;32mand welcome aboard!\e[0m\n"
