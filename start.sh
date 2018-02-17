#!/usr/bin/env bash
vagrant="C:/Users/Dwizzel/Desktop/dwizzel.dev/virtualbox/VMs/centos/7"
if ! [ -f "$vagrant/Vagrantfile" ]; then 
    echo "Not a valid vagrant path: $vagrant"
    exit 0
else
    vm=$(grep "config.vm.box" -m1 "$vagrant/Vagrantfile" | sed 's/.*"\([a-z].*\)"/\1/')
    echo "Starting $vm"
fi
cd ${vagrant}
echo "Vagrant up"
vagrant up
echo "Vagrant ssh"
vagrant ssh
echo "Vagrant halt"
vagrant halt
exit 0