class phpbuild {
  include git

  $phpbuildDir = "/home/vagrant/.phpbuild"
  $phpbuildTmp = "/tmp/phpbuild"
  $phpbuildRepo = "git://github.com/CHH/php-build.git"

  exec { "clone ${phpbuildRepo}":
    path    => "/usr/bin:/usr/sbin:/bin",
    command => "git clone ${phpbuildRepo} ${phpbuildTmp}",
    creates => "${phpbuildTmp}/.git",
    require => Class['git'],
  }

  exec { "install phpbuild through bash script":
    path        => "/usr/bin:/usr/sbin:/bin",
    command     => "${phpbuildTmp}/install.sh",
    environment => "PREFIX=${phpbuildDir}",
    creates     => $phpbuildDir,
    user        => "vagrant",
    require     => Exec["clone ${phpbuildRepo}"]
  }

  file { "${phpbuildDir}/share/php-build/default_configure_options":
    path    => "${phpbuildDir}/share/php-build/default_configure_options",
    owner   => "vagrant",
    require => Exec["install phpbuild through bash script"],
    content => template("phpbuild/default_configure_options.erb"),
  }

  file { "/etc/profile.d/phpbuild.sh":
    path    => "/etc/profile.d/phpbuild.sh",
    require => Exec["install phpbuild through bash script"],
    content => template("phpbuild/phpbuild.sh.erb"),
  }
}
