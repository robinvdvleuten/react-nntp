define phpenv::php(
  $version = "",
) {

  include apt
  include phpbuild
  include phpenv

  if $version == "" {
    $phpVersion = $title
  } else {
    $phpVersion = $version
  }

  exec { "apt-update":
    command => "/usr/bin/apt-get update",
  }

  apt::builddep { "php5-cli":
    require => Exec["apt-update"],
  }

  $phpDeps = [ "libmcrypt-dev" ]
  package { $phpDeps:
    ensure => "installed",
    require => Apt::Builddep["php5-cli"],
  }

  $phpVersionDir = "${$phpenv::phpenvDir}/versions/${phpVersion}"

  exec { "compiles php-${phpVersion}":
    command => "${phpbuild::phpbuildDir}/bin/php-build -i development ${phpVersion} ${phpVersionDir}",
    creates => "${phpVersionDir}/bin/php",
    timeout => 0,
    logoutput => true,
    require => [ File["/etc/profile.d/phpbuild.sh"], File["/etc/profile.d/phpenv.sh"], Package["libmcrypt-dev"] ],
  }

  exec { "rehash phpenv":
    command => "${phpenv::phpenvDir}/bin/phpenv rehash",
    require => Exec["compiles php-${phpVersion}"],
  }

  exec { "set ${phpVersion} as global":
    command => "${phpenv::phpenvDir}/bin/phpenv global ${phpVersion}",
    require => Exec["rehash phpenv"],
  }
}
