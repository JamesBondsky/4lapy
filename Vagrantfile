# -*- mode: ruby -*-
# vi: set ft=ruby :

#TODO Починить, т.к. пока не работает 
# Demand hostupdater plugin to be installed
# +%x(vagrant plugin install vagrant-hostsupdater) unless Vagrant.has_plugin?('vagrant-hostsupdater')

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
  config.vm.provision "shell",
    path: "common/local/php_interface/subprovision.sh",
    privileged: false,
    keep_color: true


  # Please, don't change vm.define. It could lead to problems with already running machines!
  config.vm.define "4lapy"
  config.vm.hostname = "4lapy.vag"
  config.hostsupdater.aliases = ["www.4lapy.vag"]
  config.hostsupdater.remove_on_suspend = false
  config.vm.network "private_network", ip: "192.168.7.17"

  # Http for vagrant-share
  config.vm.network "forwarded_port", guest: 80, host: 8880

  config.vm.provider "virtualbox" do |v|
      v.memory = 6144
      v.cpus = 2
  end

  config.vm.synced_folder ".", "/home/vagrant/project", owner: "vagrant", group: "vagrant", create: true
  #config.vm.synced_folder ".", "/home/vagrant/project", create: true, type: "nfs"

end
