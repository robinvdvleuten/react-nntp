define phpenv::extension(
  $extension,
  $channel,
) {

  include phpenv

  exec { "discover pear channel ${channel}":
    path    => "/usr/bin:/usr/sbin:/bin:${phpenv::phpenvDir}/shims",
    command => "pear channel-discover ${channel}",
    onlyif  => [ "test ! -z ${channel}", "pear channel-info ${channel} | egrep -q '^Unknown'" ],
  }

  exec { "install extension ${channel}/${extension}":
    path    => "/usr/bin:/usr/sbin:/bin:${phpenv::phpenvDir}/shims",
    command => "pecl install ${channel}/${extension}",
    onlyif  => "pecl info ${channel}/${extension} | egrep -q '^No information found for'",
    require => Exec["discover pear channel ${channel}"],
  }

  exec { "rehash phpenv so extension is available":
    path    => "/usr/bin:/usr/sbin:/bin:${phpenv::phpenvDir}/bin",
    command => "phpenv rehash",
    require => Exec["install extension ${channel}/${extension}"],
  }
}
