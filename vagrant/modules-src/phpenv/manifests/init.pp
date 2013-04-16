class phpenv {
  include git

  $phpenvDir = "/home/vagrant/.phpenv"
  $phpenvTmp = "/tmp/phpenv"
  $phpenvRepo = "git://github.com/CHH/phpenv.git"

  exec { "clone ${phpenvRepo}":
    path    => "/usr/bin:/usr/sbin:/bin",
    command => "git clone ${phpenvRepo} ${phpenvTmp}",
    creates => "${phpenvTmp}/.git",
    require => Class['git'],
  }

  exec { "install phpenv through bash script":
    path        => "/usr/bin:/usr/sbin:/bin",
    command     => "${phpenvTmp}/bin/phpenv-install.sh",
    environment => "PHPENV_ROOT=${phpenvDir}",
    creates     => $phpenvDir,
    user        => "vagrant",
    require     => Exec["clone ${phpenvRepo}"]
  }

  file { "/etc/profile.d/phpenv.sh":
    path    => "/etc/profile.d/phpenv.sh",
    require => Exec["install phpenv through bash script"],
    content => template("phpenv/phpenv.sh.erb"),
  }
}
