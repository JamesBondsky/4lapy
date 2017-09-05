# -*- mode: ruby -*-
# vi: set ft=ruby :

#TODO Починить, т.к. пока не работает 
# Demand hostupdater plugin to be installed
# +%x(vagrant plugin install vagrant-hostsupdater) unless Vagrant.has_plugin?('vagrant-hostsupdater')

# Sub provision script - apply to compiled box only
$subprov = <<SCRIPT

    # Basic variables
    DOCUMENT_ROOT="/home/vagrant/htdocs"
    SUBPROV_ROOT="/home/vagrant/.subprovision"

    # Create unversioned files
    BITRIX_FOLDER_CHECK="${DOCUMENT_ROOT}/bitrix/index.php"
    UNVER_FOLDER_ARCHIVE="${SUBPROV_ROOT}/unversioned-files.tar.gz"

    if [[ -f "${BITRIX_FOLDER_CHECK}" ]] ; then
        printf "Unversioned files seems to be OKay. \n"
        printf "To refresh unversioned files, please, remove following files and directories, but be careful!\n"
        printf "\t\t${BITRIX_FOLDER_CHECK}\n"
        printf "\t\t${DOCUMENT_ROOT}/upload/iblock\n"

    else
        printf "Unversioned files seems to be missing. \nUnpacking. Please, wait for a few minutes...\n"
        tar --overwrite --same-permissions --directory "${DOCUMENT_ROOT}" --gunzip --extract --file "${UNVER_FOLDER_ARCHIVE}"
    fi

    # Run composer install for the first time
    if (shopt -s nullglob dotglob; f=(/home/vagrant/htdocs/local/php_interface/vendor/*); ((${#f[@]}))) ; then
        printf "Composer folder is OKay.\n"
    else
        printf "Need composer first install\n"
        cd "${DOCUMENT_ROOT}"
        sudo --user=vagrant composer install
        cd -
    fi

    #TODO Возможно, понадобится добавить начальную сборку статики или инициализацию субмодулей сюда

    # Grant execution of migrations
    MIGRATION_RUNNER="${DOCUMENT_ROOT}/migrate.php"
    if [[ -x "${MIGRATION_RUNNER}" ]] ; then
        printf "Migrations runner is OKay"
    else
        if [[ -f "${MIGRATION_RUNNER}" ]]; then
            printf "Mark ${MIGRATION_RUNNER} as executable \n"
            chmod a+x "${MIGRATION_RUNNER}"
        fi
    fi

    printf "Subprovision done. \nVisit http://4lapy.vag and welcome aboard!\n"

SCRIPT

Vagrant.configure("2") do |config|

  # Base box and provisioning for first setup
  #config.vm.box = "ubuntu/trusty64"

  # Base box for regular usage
  config.vm.box = "adv/4lapy"
  config.vm.box_url = "http://vagrant-atlas.adv.ru/4lapy/4lapy.json"

  # Main provision
  #config.vm.provision "ansible" do |ansible|
  #  ansible.playbook = "../provisioning/playbook.yml"
  #  ansible.galaxy_role_file = "../provisioning/require-roles.yml"
  #  ansible.sudo = true
  #end

  # Sub provision - apply to compiled box only
  config.vm.provision "shell", inline: $subprov

  # Please, don't change vm.define. It could lead to problems with already running machines!
  config.vm.define "4lapy"
  config.vm.hostname = "4lapy.vag"
  config.hostsupdater.aliases = ["www.4lapy.vag"]
  config.hostsupdater.remove_on_suspend = false
  config.vm.network "private_network", ip: "192.168.7.17"

  # Http for vagrant-share
  config.vm.network "forwarded_port", guest: 80, host: 8880

  config.vm.provider "virtualbox" do |v|
      v.memory = 3072
      v.cpus = 2
  end

  config.vm.synced_folder ".", "/home/vagrant/htdocs", owner: "vagrant", group: "vagrant", create: true

end
